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
