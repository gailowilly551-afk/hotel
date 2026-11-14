-- Migration 0008: Add User Profile Verification Fields
ALTER TABLE `users`
  ADD `id_document` VARCHAR(255) DEFAULT NULL AFTER `id_number`,
  ADD `location` TEXT DEFAULT NULL AFTER `address`,
  ADD `profile_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email_verified`,
  ADD `verification_submitted_at` DATETIME DEFAULT NULL AFTER `profile_verified`;
