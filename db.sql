CREATE USER IF NOT EXISTS 'server'@'localhost' IDENTIFIED BY 'password';
CREATE DATABASE IF NOT EXISTS server_data;
GRANT ALL ON server_data.* TO 'server'@'localhost';
use server_data;