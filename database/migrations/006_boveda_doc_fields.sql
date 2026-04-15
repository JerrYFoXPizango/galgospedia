-- ============================================================
-- Migración 006 — Bóveda: añadir original_name y mime_type
-- ============================================================

ALTER TABLE `club_documents`
    ADD COLUMN `original_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `file_path`,
    ADD COLUMN `mime_type`     VARCHAR(100) NOT NULL DEFAULT '' AFTER `original_name`;
