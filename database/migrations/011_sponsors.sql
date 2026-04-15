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
VALUES ('Tu logo aquí', '/img/sponsors/placeholder.svg', NULL, 1, 0);
