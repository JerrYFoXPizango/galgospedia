-- ============================================================
-- Migración 008 — Billetera de documentos por usuario
-- ============================================================

CREATE TABLE IF NOT EXISTS `user_wallet_docs` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    NOT NULL,
    `dog_id`        INT UNSIGNED    DEFAULT NULL,
    `doc_type`      ENUM(
                        'cartilla_veterinaria',
                        'microchip',
                        'cesion',
                        'licencia_federativa',
                        'permiso_caza',
                        'vacuna',
                        'otro'
                    ) NOT NULL DEFAULT 'otro',
    `title`         VARCHAR(200)    NOT NULL,
    `file_path`     VARCHAR(500)    NOT NULL,
    `original_name` VARCHAR(255)    NOT NULL DEFAULT '',
    `mime_type`     VARCHAR(100)    NOT NULL DEFAULT '',
    `file_size`     INT UNSIGNED    DEFAULT NULL,
    `expires_at`    DATE            DEFAULT NULL,
    `notes`         TEXT            DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_wallet_user`     (`user_id`),
    KEY `idx_wallet_dog`      (`dog_id`),
    KEY `idx_wallet_doc_type` (`doc_type`),
    KEY `idx_wallet_expires`  (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
