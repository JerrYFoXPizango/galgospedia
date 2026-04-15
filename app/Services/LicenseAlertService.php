<?php

declare(strict_types=1);

namespace Services;

use Config\{Database, Config};

/**
 * Gestiona el envío de alertas de licencias a presidentes y socios.
 *
 * - Consulta socios activos con licencia caducada o próxima (≤30 días).
 * - Agrupa por club y envía un digest al presidente.
 * - Envía aviso individual al socio si tiene email.
 * - Registra cada alerta en license_alerts para no repetirla.
 */
class LicenseAlertService
{
    private \PDO       $db;
    private MailService $mailer;

    public function __construct()
    {
        $this->db     = Database::pdo();
        $this->mailer = new MailService();
    }

    /**
     * Ejecuta el ciclo completo de alertas.
     *
     * @return array{processed:int, clubs:int, sent:int, errors:int}
     */
    public function run(bool $dryRun = false): array
    {
        $rows = $this->getPending();

        if (!$rows) {
            return ['processed' => 0, 'clubs' => 0, 'sent' => 0, 'errors' => 0];
        }

        // Agrupar por club
        $byClub = [];
        foreach ($rows as $row) {
            $byClub[$row['club_id']][] = $row;
        }

        $sent   = 0;
        $errors = 0;
        $logStmt = $this->db->prepare(
            'INSERT INTO license_alerts (club_member_id, type) VALUES (?, ?)'
        );

        foreach ($byClub as $members) {
            $first          = $members[0];
            $clubName       = $first['club_name'];
            $presidentEmail = $first['president_email'] ?? null;
            $presidentName  = $first['president_username'] ?? 'Presidente';

            $expired = array_values(array_filter($members, fn($m) => $m['alert_type'] === 'expired'));
            $soon    = array_values(array_filter($members, fn($m) => $m['alert_type'] === 'expiring_soon'));

            // Digest al presidente
            if ($presidentEmail && !$dryRun) {
                $subject = $this->buildSubject($clubName, count($expired), count($soon));
                $html    = $this->buildDigestHtml($clubName, $expired, $soon);
                if (!$this->mailer->send($presidentEmail, $presidentName, $subject, $html)) {
                    $errors++;
                }
            }

            // Aviso individual a socios con email
            foreach ($members as $m) {
                if ($m['member_email'] && !$dryRun) {
                    $subj = $m['alert_type'] === 'expired'
                        ? '[Galgospedia] Tu licencia ha caducado'
                        : '[Galgospedia] Tu licencia caduca pronto';
                    if (!$this->mailer->send(
                        $m['member_email'], $m['member_name'],
                        $subj, $this->buildMemberHtml($clubName, $m)
                    )) {
                        $errors++;
                    }
                }

                // Registrar alerta
                if (!$dryRun) {
                    $logStmt->execute([$m['member_id'], $m['alert_type']]);
                    $sent++;
                }
            }
        }

        return [
            'processed' => count($rows),
            'clubs'     => count($byClub),
            'sent'      => $sent,
            'errors'    => $errors,
        ];
    }

    /**
     * Socios que necesitan alerta y aún no la han recibido.
     */
    public function getPending(): array
    {
        $stmt = $this->db->query(
            "SELECT
                 cm.id                AS member_id,
                 cm.name              AS member_name,
                 cm.email             AS member_email,
                 cm.license_number,
                 cm.license_type,
                 cm.license_expires_at,
                 c.id                 AS club_id,
                 c.name               AS club_name,
                 u.email              AS president_email,
                 u.username           AS president_username,
                 DATEDIFF(cm.license_expires_at, CURDATE()) AS days_left,
                 CASE
                     WHEN cm.license_expires_at < CURDATE()                             THEN 'expired'
                     WHEN cm.license_expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring_soon'
                 END AS alert_type
             FROM club_members cm
             JOIN clubs c  ON c.id  = cm.club_id AND c.status = 'active'
             LEFT JOIN users u ON u.id = c.president_user_id
             WHERE cm.status = 'active'
               AND cm.license_expires_at IS NOT NULL
               AND cm.license_expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
               AND NOT EXISTS (
                   SELECT 1 FROM license_alerts la
                   WHERE la.club_member_id = cm.id
                     AND la.type = CASE
                         WHEN cm.license_expires_at < CURDATE() THEN 'expired'
                         ELSE 'expiring_soon'
                     END
               )
             ORDER BY c.id ASC, cm.license_expires_at ASC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Historial de alertas enviadas (más recientes primero).
     */
    public function getHistory(int $limit = 60): array
    {
        $stmt = $this->db->prepare(
            "SELECT la.*, cm.name AS member_name, cm.license_expires_at,
                    c.name AS club_name
             FROM license_alerts la
             JOIN club_members cm ON cm.id = la.club_member_id
             JOIN clubs c         ON c.id  = cm.club_id
             ORDER BY la.sent_at DESC
             LIMIT $limit"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Plantillas de email ──────────────────────────────────────

    private function buildSubject(string $club, int $expired, int $soon): string
    {
        $parts = [];
        if ($expired > 0) $parts[] = $expired . ' caducada' . ($expired > 1 ? 's' : '');
        if ($soon    > 0) $parts[] = $soon    . ' próxima'  . ($soon    > 1 ? 's' : '');
        return '[Galgospedia] Alertas de licencia en ' . $club . ' — ' . implode(', ', $parts);
    }

    public function buildDigestHtml(string $club, array $expired, array $soon): string
    {
        $rows = '';
        foreach ($expired as $m) {
            $daysAgo = abs((int) $m['days_left']);
            $rows .= $this->memberRow($m, '🔴', 'Caducó hace ' . $daysAgo . ' día' . ($daysAgo !== 1 ? 's' : ''), '#dc2626');
        }
        foreach ($soon as $m) {
            $days = (int) $m['days_left'];
            $rows .= $this->memberRow($m, '🟡', 'Caduca en ' . $days . ' día' . ($days !== 1 ? 's' : ''), '#d97706');
        }

        $appUrl = Config::appUrl();
        return <<<HTML
        <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
        <body style="font-family:sans-serif;background:#f9fafb;margin:0;padding:24px">
          <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:#1a1a2e;padding:24px 32px;text-align:center">
              <span style="color:#d4af37;font-size:20px;font-weight:bold">Galgospedia</span>
              <p style="color:#9ca3af;font-size:13px;margin:4px 0 0">Alertas de licencia — Oficina Virtual</p>
            </div>
            <div style="padding:28px 32px">
              <p style="color:#374151;margin:0 0 16px">
                Hay socios en <strong>{$club}</strong> con licencias que requieren atención:
              </p>
              <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                  <tr style="background:#f3f4f6;color:#6b7280;text-align:left">
                    <th style="padding:8px 12px">Socio</th>
                    <th style="padding:8px 12px">Licencia</th>
                    <th style="padding:8px 12px">Estado</th>
                  </tr>
                </thead>
                <tbody>{$rows}</tbody>
              </table>
              <div style="margin-top:24px;text-align:center">
                <a href="{$appUrl}/oficina/mi-club"
                   style="display:inline-block;background:#d4af37;color:#1a1a2e;padding:10px 24px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px">
                  Ver mi club
                </a>
              </div>
            </div>
            <div style="padding:14px 32px;border-top:1px solid #f3f4f6;text-align:center;color:#9ca3af;font-size:12px">
              Aviso automático — no respondas a este correo.
            </div>
          </div>
        </body></html>
        HTML;
    }

    private function memberRow(array $m, string $icon, string $text, string $color): string
    {
        $expiry  = (new \DateTime($m['license_expires_at']))->format('d/m/Y');
        $lic     = htmlspecialchars($m['license_number'] ?? '—');
        $licType = !empty($m['license_type']) ? ' (' . htmlspecialchars($m['license_type']) . ')' : '';
        $name    = htmlspecialchars($m['member_name']);
        return "<tr style=\"border-bottom:1px solid #f3f4f6\">
                  <td style=\"padding:10px 12px;font-weight:500\">{$icon} {$name}</td>
                  <td style=\"padding:10px 12px;color:#6b7280\">{$lic}{$licType}<br><small>{$expiry}</small></td>
                  <td style=\"padding:10px 12px;color:{$color};font-weight:600\">{$text}</td>
                </tr>";
    }

    private function buildMemberHtml(string $club, array $m): string
    {
        $isExpired = $m['alert_type'] === 'expired';
        $daysLeft  = (int) $m['days_left'];
        $expiry    = (new \DateTime($m['license_expires_at']))->format('d/m/Y');
        $color     = $isExpired ? '#dc2626' : '#d97706';
        $icon      = $isExpired ? '🔴' : '🟡';
        $statusText = $isExpired
            ? 'Tu licencia caducó hace ' . abs($daysLeft) . ' día' . (abs($daysLeft) !== 1 ? 's' : '') . ' (' . $expiry . ').'
            : 'Tu licencia caduca en ' . $daysLeft . ' día' . ($daysLeft !== 1 ? 's' : '') . ' (' . $expiry . ').';
        $licType = !empty($m['license_type']) ? ' — ' . htmlspecialchars($m['license_type']) : '';
        $licNum  = htmlspecialchars($m['license_number'] ?? '—');
        $name    = htmlspecialchars($m['member_name']);
        $clubHtml= htmlspecialchars($club);

        return <<<HTML
        <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
        <body style="font-family:sans-serif;background:#f9fafb;margin:0;padding:24px">
          <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:#1a1a2e;padding:20px 28px;text-align:center">
              <span style="color:#d4af37;font-size:18px;font-weight:bold">Galgospedia</span>
            </div>
            <div style="padding:28px">
              <p style="color:#374151;margin:0 0 12px">Hola, <strong>{$name}</strong>.</p>
              <p style="color:{$color};font-size:16px;margin:0 0 16px">{$icon} {$statusText}</p>
              <p style="color:#6b7280;font-size:14px;margin:0 0 20px">
                Club: <strong>{$clubHtml}</strong><br>
                Licencia: {$licNum}{$licType}
              </p>
              <p style="color:#374151;font-size:14px">
                Por favor, renueva tu licencia y comunícaselo al responsable de tu club.
              </p>
            </div>
            <div style="padding:14px 28px;border-top:1px solid #f3f4f6;text-align:center;color:#9ca3af;font-size:12px">
              Aviso automático de Galgospedia — no respondas a este correo.
            </div>
          </div>
        </body></html>
        HTML;
    }
}
