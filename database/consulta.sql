CREATE DATABASE galgospedia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'galgo_user'@'localhost' IDENTIFIED BY 'dev_password_here';
GRANT ALL PRIVILEGES ON galgospedia.* TO 'galgo_user'@'localhost';
FLUSH PRIVILEGES;