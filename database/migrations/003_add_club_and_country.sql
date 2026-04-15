-- Migration 003: Replace registration_number / chip_number with club and country
-- Run in HeidiSQL or any MySQL client

ALTER TABLE `dogs`
  ADD COLUMN `club`    VARCHAR(150) NULL AFTER `chip_number`,
  ADD COLUMN `country` VARCHAR(100) NULL AFTER `club`;
