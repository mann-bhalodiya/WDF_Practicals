-- Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD
-- Import this file in phpMyAdmin (or: mysql -u root < schema.sql)

CREATE DATABASE IF NOT EXISTS studenthub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE studenthub;

DROP TABLE IF EXISTS students;

CREATE TABLE students (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(60)  NOT NULL,
  email      VARCHAR(100) NOT NULL,
  mobile     CHAR(10)     NOT NULL,
  course     VARCHAR(10)  NOT NULL,
  year       TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_students_email (email),
  KEY idx_students_course (course),
  CONSTRAINT chk_students_year CHECK (year BETWEEN 1 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data
INSERT INTO students (name, email, mobile, course, year) VALUES
('Aarav Patel',     'aarav.patel@example.com',    '9876543210', 'CSE', 2),
('Diya Shah',       'diya.shah@example.com',      '9825012345', 'IT',  2),
('Kabir Mehta',     'kabir.mehta@example.com',    '9712345678', 'CE',  3),
('Isha Desai',      'isha.desai@example.com',     '9898989898', 'CSE', 1),
('Rohan Joshi',     'rohan.joshi@example.com',    '9099012345', 'EC',  4),
('Meera Trivedi',   'meera.trivedi@example.com',  '9638527410', 'IT',  3),
('Yash Parmar',     'yash.parmar@example.com',    '9586741230', 'ME',  2),
('Ananya Vyas',     'ananya.vyas@example.com',    '9427012345', 'CSE', 3),
('Harsh Chauhan',   'harsh.chauhan@example.com',  '9033445566', 'CE',  1),
('Nisha Solanki',   'nisha.solanki@example.com',  '9724681357', 'IT',  4);
