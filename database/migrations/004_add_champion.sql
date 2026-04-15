-- Migration 004: Add champion field to dogs table
ALTER TABLE `dogs`
  ADD COLUMN `champion` VARCHAR(200) NULL AFTER `country`;
