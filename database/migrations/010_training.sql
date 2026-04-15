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
