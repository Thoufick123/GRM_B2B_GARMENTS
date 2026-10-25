-- ==============================================================================
-- GRM B2B — Patch: Add profile_picture column to users table
-- Safe & Idempotent (Can be executed directly in Hostinger phpMyAdmin)
-- Run this if you get: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'profile_picture' in 'SET'
-- ==============================================================================

ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `profile_picture` VARCHAR(500) NULL AFTER `phone`;
