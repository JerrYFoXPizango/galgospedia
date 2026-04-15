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
