-- ============================================================
-- GALGOSPEDIA — Schema completo para producción
-- Generado: Wed Apr 15 10:29:39     2026
-- Importar en phpMyAdmin de BanaHosting
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ============================================================
-- GALGOSPEDIA.COM — Database Schema
-- MariaDB 10.6 / MySQL 8.0+
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ============================================================

USE galgospedia;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(50)      NOT NULL,
    `email`          VARCHAR(255)     NOT NULL,
    `password_hash`  VARCHAR(255)     NOT NULL,
    `role`           ENUM('user','moderator','admin') NOT NULL DEFAULT 'user',
    `avatar_path`    VARCHAR(500)     NULL,
    `bio`            TEXT             NULL,
    `is_active`      TINYINT(1)       NOT NULL DEFAULT 1,
    `email_verified` TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email`    (`email`),
    UNIQUE KEY `uq_username` (`username`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AUTH TOKENS (email verification + password reset)
-- ============================================================
CREATE TABLE IF NOT EXISTS `auth_tokens` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED  NOT NULL,
    `token`      VARCHAR(128)  NOT NULL,
    `type`       ENUM('email_verify','password_reset') NOT NULL,
    `expires_at` TIMESTAMP     NOT NULL,
    `used_at`    TIMESTAMP     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_token` (`token`),
    INDEX `idx_user_type` (`user_id`, `type`),
    CONSTRAINT `fk_token_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOGS  (core entity — self-referencing for parent links)
-- ============================================================
CREATE TABLE IF NOT EXISTS `dogs` (
    `id`                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `slug`                VARCHAR(160)  NOT NULL,
    `name`                VARCHAR(120)  NOT NULL,
    `gender`              ENUM('male','female','unknown') NOT NULL DEFAULT 'unknown',
    `date_of_birth`       DATE          NULL,
    `date_of_death`       DATE          NULL,
    `color`               VARCHAR(80)   NULL,
    `registration_number` VARCHAR(100)  NULL,
    `chip_number`         VARCHAR(50)   NULL,
    `breed_variant`       ENUM('spanish_greyhound','arabic_galgo','mixed','unknown') NOT NULL DEFAULT 'spanish_greyhound',
    -- Images (all converted to WebP on upload)
    `photo_original`      VARCHAR(500)  NULL,
    `photo_webp`          VARCHAR(500)  NULL,   -- max 1200px wide
    `photo_thumb`         VARCHAR(500)  NULL,   -- 300x300 crop
    -- Genealogy links (adjacency list — direct parents)
    `father_id`           INT UNSIGNED  NULL,
    `mother_id`           INT UNSIGNED  NULL,
    -- Ownership
    `owner_user_id`       INT UNSIGNED  NULL,
    `created_by`          INT UNSIGNED  NOT NULL,
    -- Flags
    `notes`               TEXT          NULL,
    `is_public`           TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    INDEX `idx_name`   (`name`),
    INDEX `idx_father` (`father_id`),
    INDEX `idx_mother` (`mother_id`),
    INDEX `idx_owner`  (`owner_user_id`),
    INDEX `idx_gender` (`gender`),
    INDEX `idx_reg`    (`registration_number`),
    INDEX `idx_public` (`is_public`),

    CONSTRAINT `fk_dog_father`
        FOREIGN KEY (`father_id`) REFERENCES `dogs`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_dog_mother`
        FOREIGN KEY (`mother_id`) REFERENCES `dogs`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_dog_owner`
        FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_dog_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOG ANCESTRY — Closure Table
-- Stores every ancestor-descendant pair with depth.
-- depth=0 = self-reference. depth=1 = direct parent. etc.
-- Both paternal and maternal lines are stored here.
-- ============================================================
CREATE TABLE IF NOT EXISTS `dog_ancestry` (
    `ancestor_id`   INT UNSIGNED   NOT NULL,
    `descendant_id` INT UNSIGNED   NOT NULL,
    `depth`         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `path_line`     ENUM('paternal','maternal','mixed','self') NOT NULL DEFAULT 'self',
    PRIMARY KEY (`ancestor_id`, `descendant_id`),
    INDEX `idx_desc_depth` (`descendant_id`, `depth`),
    INDEX `idx_anc_depth`  (`ancestor_id`,   `depth`),
    CONSTRAINT `fk_anc_dog`
        FOREIGN KEY (`ancestor_id`)   REFERENCES `dogs`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_desc_dog`
        FOREIGN KEY (`descendant_id`) REFERENCES `dogs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STALLIONS  (Sementales)
-- ============================================================
CREATE TABLE IF NOT EXISTS `stallions` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `dog_id`         INT UNSIGNED  NOT NULL,
    `description`    TEXT          NULL,
    `achievements`   TEXT          NULL,
    `featured_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_stallion_dog` (`dog_id`),
    INDEX `idx_order_active` (`featured_order`, `is_active`),
    CONSTRAINT `fk_stallion_dog`
        FOREIGN KEY (`dog_id`) REFERENCES `dogs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BROODMARES  (Reproductoras)
-- ============================================================
CREATE TABLE IF NOT EXISTS `broodmares` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `dog_id`         INT UNSIGNED  NOT NULL,
    `description`    TEXT          NULL,
    `achievements`   TEXT          NULL,
    `featured_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_broodmare_dog` (`dog_id`),
    INDEX `idx_order_active` (`featured_order`, `is_active`),
    CONSTRAINT `fk_broodmare_dog`
        FOREIGN KEY (`dog_id`) REFERENCES `dogs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LITTERS  (Camadas) — Phase 2
-- ============================================================
CREATE TABLE IF NOT EXISTS `litters` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `father_id`   INT UNSIGNED  NOT NULL,
    `mother_id`   INT UNSIGNED  NOT NULL,
    `whelp_date`  DATE          NULL,
    `litter_code` VARCHAR(80)   NULL,
    `notes`       TEXT          NULL,
    `created_by`  INT UNSIGNED  NOT NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_parents` (`father_id`, `mother_id`),
    CONSTRAINT `fk_litter_father`
        FOREIGN KEY (`father_id`) REFERENCES `dogs`(`id`),
    CONSTRAINT `fk_litter_mother`
        FOREIGN KEY (`mother_id`) REFERENCES `dogs`(`id`),
    CONSTRAINT `fk_litter_creator`
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `litter_dogs` (
    `litter_id` INT UNSIGNED NOT NULL,
    `dog_id`    INT UNSIGNED NOT NULL,
    PRIMARY KEY (`litter_id`, `dog_id`),
    CONSTRAINT `fk_ld_litter`
        FOREIGN KEY (`litter_id`) REFERENCES `litters`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ld_dog`
        FOREIGN KEY (`dog_id`)    REFERENCES `dogs`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- STORED PROCEDURE: Rebuild ancestry rows for a dog
-- Call this whenever father_id or mother_id is set/changed.
-- ============================================================
DROP PROCEDURE IF EXISTS `sp_rebuild_ancestry`;

DELIMITER $$

CREATE PROCEDURE `sp_rebuild_ancestry`(IN p_dog_id INT UNSIGNED)
BEGIN
    -- Remove existing ancestry rows where this dog is the descendant
    DELETE FROM `dog_ancestry` WHERE `descendant_id` = p_dog_id;

    -- Self-reference (depth 0)
    INSERT INTO `dog_ancestry` (`ancestor_id`, `descendant_id`, `depth`, `path_line`)
    VALUES (p_dog_id, p_dog_id, 0, 'self');

    -- Father's ancestors → become this dog's paternal ancestors
    INSERT IGNORE INTO `dog_ancestry` (`ancestor_id`, `descendant_id`, `depth`, `path_line`)
    SELECT
        da.`ancestor_id`,
        p_dog_id,
        da.`depth` + 1,
        CASE
            WHEN da.`path_line` = 'self'     THEN 'paternal'
            WHEN da.`path_line` = 'paternal' THEN 'paternal'
            WHEN da.`path_line` = 'maternal' THEN 'mixed'
            ELSE 'mixed'
        END
    FROM `dogs` d
    JOIN `dog_ancestry` da ON da.`descendant_id` = d.`father_id`
    WHERE d.`id` = p_dog_id AND d.`father_id` IS NOT NULL;

    -- Mother's ancestors → become this dog's maternal ancestors
    INSERT IGNORE INTO `dog_ancestry` (`ancestor_id`, `descendant_id`, `depth`, `path_line`)
    SELECT
        da.`ancestor_id`,
        p_dog_id,
        da.`depth` + 1,
        CASE
            WHEN da.`path_line` = 'self'     THEN 'maternal'
            WHEN da.`path_line` = 'maternal' THEN 'maternal'
            WHEN da.`path_line` = 'paternal' THEN 'mixed'
            ELSE 'mixed'
        END
    FROM `dogs` d
    JOIN `dog_ancestry` da ON da.`descendant_id` = d.`mother_id`
    WHERE d.`id` = p_dog_id AND d.`mother_id` IS NOT NULL;
END$$

DELIMITER ;

-- ============================================================
-- STORED PROCEDURE: Propagate ancestry changes down to children
-- Call after sp_rebuild_ancestry for the parent.
-- ============================================================
DROP PROCEDURE IF EXISTS `sp_propagate_ancestry`;

DELIMITER $$

CREATE PROCEDURE `sp_propagate_ancestry`(IN p_ancestor_id INT UNSIGNED)
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE child_id INT UNSIGNED;
    DECLARE cur CURSOR FOR
        SELECT `id` FROM `dogs`
        WHERE `father_id` = p_ancestor_id OR `mother_id` = p_ancestor_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO child_id;
        IF done THEN LEAVE read_loop; END IF;
        CALL sp_rebuild_ancestry(child_id);
        CALL sp_propagate_ancestry(child_id);
    END LOOP;
    CLOSE cur;
END$$
DELIMITER ;

-- ── Migración: 001_add_plan_to_users.sql ──────────────────────────────────
-- Migration 001: Add plan and club_logo_path to users
USE galgospedia;

ALTER TABLE `users`
    ADD COLUMN `plan`            ENUM('free','club') NOT NULL DEFAULT 'free' AFTER `role`,
    ADD COLUMN `club_name`       VARCHAR(120) NULL AFTER `plan`,
    ADD COLUMN `club_logo_path`  VARCHAR(500) NULL AFTER `club_name`;

-- ── Migración: 002_breed_variant_and_reproductor.sql ──────────────────────────────────
-- Migration 002: Update breed_variant ENUM + no structural changes needed for stallions/broodmares
-- Run this in HeidiSQL against the galgospedia database

USE galgospedia;

-- 1. Map old values to new ones before changing the ENUM
UPDATE dogs SET breed_variant = 'spanish_greyhound' WHERE breed_variant IN ('unknown');
UPDATE dogs SET breed_variant = 'hybrid'            WHERE breed_variant IN ('mixed', 'arabic_galgo');

-- 2. Change the ENUM column
ALTER TABLE dogs
    MODIFY COLUMN `breed_variant`
        ENUM('spanish_greyhound','english_greyhound','hybrid')
        NOT NULL DEFAULT 'spanish_greyhound';

-- ── Migración: 003_add_club_and_country.sql ──────────────────────────────────
-- Migration 003: Replace registration_number / chip_number with club and country
-- Run in HeidiSQL or any MySQL client

ALTER TABLE `dogs`
  ADD COLUMN `club`    VARCHAR(150) NULL AFTER `chip_number`,
  ADD COLUMN `country` VARCHAR(100) NULL AFTER `club`;

-- ── Migración: 004_add_champion.sql ──────────────────────────────────
-- Migration 004: Add champion field to dogs table
ALTER TABLE `dogs`
  ADD COLUMN `champion` VARCHAR(200) NULL AFTER `country`;

-- ── Migración: 005_oficina_virtual.sql ──────────────────────────────────
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

-- ── Migración: 006_boveda_doc_fields.sql ──────────────────────────────────
-- ============================================================
-- Migración 006 — Bóveda: añadir original_name y mime_type
-- ============================================================

ALTER TABLE `club_documents`
    ADD COLUMN `original_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `file_path`,
    ADD COLUMN `mime_type`     VARCHAR(100) NOT NULL DEFAULT '' AFTER `original_name`;

-- ── Migración: 007_tournaments.sql ──────────────────────────────────
-- ============================================================
-- Migración 007 — Torneos / Eventos de competición
-- ============================================================

CREATE TABLE IF NOT EXISTS `tournaments` (
    `id`                    INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `slug`                  VARCHAR(220)        NOT NULL,
    `title`                 VARCHAR(200)        NOT NULL,
    `discipline`            ENUM('campo','liebre_mecanica','campeonato') NOT NULL,
    `category`              VARCHAR(80)         DEFAULT NULL,
    `starts_at`             DATETIME            NOT NULL,
    `ends_at`               DATETIME            DEFAULT NULL,
    `location_name`         VARCHAR(200)        DEFAULT NULL,
    `location_address`      VARCHAR(300)        DEFAULT NULL,
    `location_lat`          DECIMAL(10,7)       DEFAULT NULL,
    `location_lng`          DECIMAL(10,7)       DEFAULT NULL,
    `meeting_point`         TEXT                DEFAULT NULL,
    `meeting_time`          TIME                DEFAULT NULL,
    `map_url`               VARCHAR(500)        DEFAULT NULL,
    `notes`                 TEXT                DEFAULT NULL,
    `description`           TEXT                DEFAULT NULL,
    `organizer_name`        VARCHAR(150)        DEFAULT NULL,
    `contact_info`          VARCHAR(255)        DEFAULT NULL,
    `registration_required` TINYINT(1)          NOT NULL DEFAULT 0,
    `registration_url`      VARCHAR(500)        DEFAULT NULL,
    `max_participants`      INT UNSIGNED        DEFAULT NULL,
    `status`                ENUM('published','draft','cancelled') NOT NULL DEFAULT 'published',
    `created_by`            INT UNSIGNED        DEFAULT NULL,
    `created_at`            TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tournament_slug` (`slug`),
    KEY `idx_tournament_discipline` (`discipline`),
    KEY `idx_tournament_status` (`status`),
    KEY `idx_tournament_starts` (`starts_at`),
    KEY `idx_tournament_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Migración: 008_user_wallet.sql ──────────────────────────────────
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

-- ── Migración: 009_vet_records.sql ──────────────────────────────────
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

-- ── Migración: 010_training.sql ──────────────────────────────────
-- Diario de Entrenamiento — Galgospedia
-- Tabla de sesiones de entrenamiento
CREATE TABLE IF NOT EXISTS `training_sessions` (
    `id`            INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `dog_id`        INT UNSIGNED        NOT NULL,
    `user_id`       INT UNSIGNED        NOT NULL,
    `date`          DATE                NOT NULL,
    `type`          ENUM('run_free','run_hare','walk','track','active_rest','competition') NOT NULL,
    `terrain`       ENUM('campo','monte','pista','arena','hierba','barro','mixto') DEFAULT NULL,
    `distance_m`    INT UNSIGNED        DEFAULT NULL,
    `duration_min`  SMALLINT UNSIGNED   DEFAULT NULL,
    `intensity`     ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    `dog_condition` ENUM('good','tired','very_tired') DEFAULT NULL,
    `temperature_c` TINYINT             DEFAULT NULL,
    `notes`         TEXT                DEFAULT NULL,
    `created_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_dog_date` (`dog_id`, `date`),
    KEY `idx_user`     (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración de límites de entrenamiento por galgo
CREATE TABLE IF NOT EXISTS `training_config` (
    `id`                            INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `dog_id`                        INT UNSIGNED        NOT NULL,
    `user_id`                       INT UNSIGNED        NOT NULL,
    `max_weekly_km`                 DECIMAL(5,2)        NOT NULL DEFAULT 30.00,
    `max_consecutive_high`          TINYINT UNSIGNED    NOT NULL DEFAULT 3,
    `rest_days_after_competition`   TINYINT UNSIGNED    NOT NULL DEFAULT 2,
    `updated_at`                    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_dog` (`dog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Migración: 011_sponsors.sql ──────────────────────────────────
-- Patrocinadores / Auspiciadores — Galgospedia
CREATE TABLE IF NOT EXISTS `sponsors` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(120)  NOT NULL,
    `logo_path`   VARCHAR(300)  NOT NULL,
    `website_url` VARCHAR(300)  DEFAULT NULL,
    `active`      TINYINT(1)    NOT NULL DEFAULT 1,
    `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_active_order` (`active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Placeholder inicial: logo de Galgospedia como "Tu logo aquí"
INSERT INTO `sponsors` (`name`, `logo_path`, `website_url`, `active`, `sort_order`)
VALUES ('Tu logo aquí', '/img/sponsors/placeholder.png', NULL, 1, 0);

SET foreign_key_checks = 1;
