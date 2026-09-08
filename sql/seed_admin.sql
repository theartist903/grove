-- Run this AFTER importing schema.sql into thegrovepk_db.
-- Creates the admin login: username admin@thegrove.pk / password Passw903rd
-- (change the password afterwards from the database if you want a different one).

INSERT INTO admin_users (username, password_hash)
VALUES ('admin@thegrove.pk', '$2y$10$tEmMGCGea0EGZIZodUUvG.S7V0TQ3kt7pii4wocPmzKusDdmeKLt.')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);
