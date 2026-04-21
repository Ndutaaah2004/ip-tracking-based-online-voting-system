-- Add IP audit columns for existing databases (run once).
-- mysql -u root -p voting_system < migrate_add_ip_columns.sql

USE voting_system;

ALTER TABLE students
  ADD COLUMN registration_ip VARCHAR(45) DEFAULT NULL AFTER password,
  ADD COLUMN last_login_ip VARCHAR(45) DEFAULT NULL AFTER registration_ip;

ALTER TABLE staff
  ADD COLUMN registration_ip VARCHAR(45) DEFAULT NULL AFTER password,
  ADD COLUMN last_login_ip VARCHAR(45) DEFAULT NULL AFTER registration_ip;
