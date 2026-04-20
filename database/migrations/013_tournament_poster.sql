-- ============================================================
-- Migración 013 — Añadir columna poster a tournaments
-- ============================================================

ALTER TABLE `tournaments`
    ADD COLUMN `poster` VARCHAR(500) DEFAULT NULL AFTER `max_participants`;
