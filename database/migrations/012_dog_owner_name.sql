-- Añade campo owner_name para propietario en texto libre
-- Permite subir galgos indicando el propietario sin que tenga cuenta en Galgospedia
ALTER TABLE `dogs`
    ADD COLUMN `owner_name` VARCHAR(120) NULL DEFAULT NULL AFTER `owner_user_id`;
