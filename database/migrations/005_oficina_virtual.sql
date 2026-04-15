-- ============================================================
-- Migración 005 — Oficina Virtual
-- Clubs, socios, documentos, eventos, alertas de licencias
-- ============================================================

-- ── 1. Añadir rol 'president' a users ───────────────────────
ALTER TABLE `users`
    MODIFY `role` ENUM('user','moderator','president','admin')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user';

-- ── 2. Clubs / Cotos ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clubs` (
    `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`                 VARCHAR(150)    NOT NULL,
    `slug`                 VARCHAR(160)    NOT NULL,
    `type`                 ENUM('club','coto','federacion','otro') NOT NULL DEFAULT 'club',
    `status`               ENUM('pending','active','suspended')    NOT NULL DEFAULT 'pending',
    `president_user_id`    INT UNSIGNED    DEFAULT NULL,
    `province`             VARCHAR(100)    DEFAULT NULL,
    `autonomous_community` VARCHAR(100)    DEFAULT NULL,
    `country`              VARCHAR(100)    NOT NULL DEFAULT 'España',
    `contact_email`        VARCHAR(255)    DEFAULT NULL,
    `contact_phone`        VARCHAR(30)     DEFAULT NULL,
    `website`              VARCHAR(255)    DEFAULT NULL,
    `description`          TEXT            DEFAULT NULL,
    `logo_path`            VARCHAR(500)    DEFAULT NULL,
    `is_verified`          TINYINT(1)      NOT NULL DEFAULT 0,
    `created_by`           INT UNSIGNED    DEFAULT NULL,
    `approved_by`          INT UNSIGNED    DEFAULT NULL,
    `approved_at`          TIMESTAMP       DEFAULT NULL,
    `created_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_club_slug` (`slug`),
    KEY `idx_club_status` (`status`),
    KEY `idx_club_president` (`president_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Socios / Miembros del club ────────────────────────────
CREATE TABLE IF NOT EXISTS `club_members` (
    `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `club_id`              INT UNSIGNED    NOT NULL,
    `user_id`              INT UNSIGNED    DEFAULT NULL,  -- NULL si es añadido manualmente
    `name`                 VARCHAR(150)    NOT NULL,
    `email`                VARCHAR(255)    DEFAULT NULL,
    `phone`                VARCHAR(30)     DEFAULT NULL,
    `license_number`       VARCHAR(80)     DEFAULT NULL,
    `license_type`         VARCHAR(80)     DEFAULT NULL,  -- caza, galgo, federativo, etc.
    `license_expires_at`   DATE            DEFAULT NULL,
    `status`               ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
    `is_delegate`          TINYINT(1)      NOT NULL DEFAULT 0,
    `notes`                TEXT            DEFAULT NULL,
    `added_by`             INT UNSIGNED    DEFAULT NULL,
    `created_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_club_user` (`club_id`, `user_id`),
    KEY `idx_member_club` (`club_id`),
    KEY `idx_member_user` (`user_id`),
    KEY `idx_member_status` (`status`),
    KEY `idx_license_expires` (`license_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Bóveda de documentos ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `club_documents` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `club_id`     INT UNSIGNED  NOT NULL,
    `title`       VARCHAR(200)  NOT NULL,
    `category`    ENUM('resolucion_coto','seguro','acta','permiso','federativo','otro') NOT NULL DEFAULT 'otro',
    `file_path`   VARCHAR(500)  NOT NULL,
    `file_size`   INT UNSIGNED  DEFAULT NULL,  -- bytes
    `expires_at`  DATE          DEFAULT NULL,
    `notes`       TEXT          DEFAULT NULL,
    `uploaded_by` INT UNSIGNED  DEFAULT NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_doc_club` (`club_id`),
    KEY `idx_doc_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. Calendario de eventos ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `club_events` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `club_id`     INT UNSIGNED  NOT NULL,
    `title`       VARCHAR(200)  NOT NULL,
    `type`        ENUM('tirada','carrera','veda','reunion','otro') NOT NULL DEFAULT 'otro',
    `description` TEXT          DEFAULT NULL,
    `location`    VARCHAR(255)  DEFAULT NULL,
    `starts_at`   DATETIME      NOT NULL,
    `ends_at`     DATETIME      DEFAULT NULL,
    `created_by`  INT UNSIGNED  DEFAULT NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_club` (`club_id`),
    KEY `idx_event_starts` (`starts_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Log de alertas de licencia ────────────────────────────
CREATE TABLE IF NOT EXISTS `license_alerts` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `club_member_id` INT UNSIGNED  NOT NULL,
    `type`           ENUM('expired','expiring_soon') NOT NULL,
    `sent_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_alert_member` (`club_member_id`),
    KEY `idx_alert_sent` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
