-- ============================================================
-- Migración 009 — Historial Veterinario por Galgo
-- ============================================================

CREATE TABLE IF NOT EXISTS `vet_records` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `dog_id`        INT UNSIGNED    NOT NULL,
    `user_id`       INT UNSIGNED    NOT NULL,
    `type`          ENUM('vaccine','deworming','injury','visit','weight') NOT NULL,
    `title`         VARCHAR(200)    NOT NULL,
    `date`          DATE            NOT NULL,
    `next_due_date` DATE            DEFAULT NULL,
    `body_part`     VARCHAR(100)    DEFAULT NULL,
    `severity`      ENUM('mild','moderate','severe') DEFAULT NULL,
    `treatment`     TEXT            DEFAULT NULL,
    `notes`         TEXT            DEFAULT NULL,
    `resolved_at`   DATE            DEFAULT NULL,
    `weight_kg`     DECIMAL(5,2)    DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_vet_dog`    (`dog_id`),
    KEY `idx_vet_user`   (`user_id`),
    KEY `idx_vet_type`   (`type`),
    KEY `idx_vet_date`   (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
