-- Migration 002: Update breed_variant ENUM + no structural changes needed for stallions/broodmares
-- Run this in HeidiSQL against the galgospedia database

USE galgospedia;

-- 1. Map old values to new ones before changing the ENUM
UPDATE dogs SET breed_variant = 'spanish_greyhound' WHERE breed_variant IN ('unknown');
UPDATE dogs SET breed_variant = 'hybrid'            WHERE breed_variant IN ('mixed', 'arabic_galgo');

-- 2. Change the ENUM column
ALTER TABLE dogs
    MODIFY COLUMN `breed_variant`
        ENUM('spanish_greyhound','english_greyhound','hybrid')
        NOT NULL DEFAULT 'spanish_greyhound';
