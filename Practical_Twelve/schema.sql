CREATE DATABASE IF NOT EXISTS event_db;
USE event_db;

CREATE TABLE IF NOT EXISTS events (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    location   VARCHAR(150) NOT NULL,
    category   VARCHAR(50) NOT NULL,
    poster     VARCHAR(255) NULL    -- only the file NAME is stored
);
