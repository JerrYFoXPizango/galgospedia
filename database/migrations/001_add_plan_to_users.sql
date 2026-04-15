-- Migration 001: Add plan and club_logo_path to users
USE galgospedia;

ALTER TABLE `users`
    ADD COLUMN `plan`            ENUM('free','club') NOT NULL DEFAULT 'free' AFTER `role`,
    ADD COLUMN `club_name`       VARCHAR(120) NULL AFTER `plan`,
    ADD COLUMN `club_logo_path`  VARCHAR(500) NULL AFTER `club_name`;
