CREATE DATABASE IF NOT EXISTS practical8_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical8_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course VARCHAR(80) NOT NULL,
    year_of_study TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_student_year CHECK (year_of_study BETWEEN 1 AND 6)
) ENGINE=InnoDB;

CREATE TABLE events (
    event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    category VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE registrations (
    registration_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    registration_date DATE NOT NULL,
    status ENUM('Registered', 'Attended', 'Cancelled') NOT NULL DEFAULT 'Registered',
    CONSTRAINT uq_student_event UNIQUE (student_id, event_id),
    CONSTRAINT fk_reg_student FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO students (full_name, email, course, year_of_study) VALUES
('Aarav Patel', 'aarav.patel@example.com', 'BCA', 1),
('Diya Shah', 'diya.shah@example.com', 'BBA', 2),
('Rohan Mehta', 'rohan.mehta@example.com', 'B.Tech', 3),
('Ananya Desai', 'ananya.desai@example.com', 'B.Sc', 1),
('Vivaan Joshi', 'vivaan.joshi@example.com', 'BCA', 3),
('Ishita Trivedi', 'ishita.trivedi@example.com', 'BBA', 2),
('Arjun Modi', 'arjun.modi@example.com', 'B.Com', 3),
('Meera Patel', 'meera.patel@example.com', 'BCA', 2),
('Krish Malhotra', 'krish.malhotra@example.com', 'B.Tech', 4),
('Kiara Shah', 'kiara.shah@example.com', 'B.Sc', 2),
('Dev Patel', 'dev.patel@example.com', 'B.Com', 1),
('Myra Mehta', 'myra.mehta@example.com', 'BBA', 3),
('Aditya Shah', 'aditya.shah@example.com', 'B.Tech', 2),
('Sara Desai', 'sara.desai@example.com', 'BCA', 1),
('Yash Trivedi', 'yash.trivedi@example.com', 'B.Com', 4);

INSERT INTO events (event_name, event_date, venue, category) VALUES
('Web Development Workshop', '2026-11-05', 'Computer Lab 1', 'Workshop'),
('Annual Sports Day', '2026-11-10', 'College Ground', 'Sports'),
('Coding Competition', '2026-11-14', 'Programming Lab', 'Competition'),
('Career Guidance Seminar', '2026-11-18', 'Seminar Hall', 'Seminar'),
('Cultural Festival', '2026-11-22', 'Main Auditorium', 'Cultural');

INSERT INTO registrations (student_id, event_id, registration_date, status) VALUES
(1, 1, '2026-10-20', 'Registered'),
(2, 2, '2026-10-20', 'Registered'),
(3, 3, '2026-10-21', 'Registered'),
(4, 1, '2026-10-21', 'Attended'),
(5, 4, '2026-10-22', 'Registered'),
(6, 5, '2026-10-22', 'Registered'),
(7, 2, '2026-10-23', 'Attended'),
(8, 3, '2026-10-23', 'Registered'),
(9, 1, '2026-10-24', 'Registered'),
(10, 4, '2026-10-24', 'Cancelled'),
(11, 5, '2026-10-24', 'Registered'),
(12, 2, '2026-10-25', 'Registered'),
(13, 3, '2026-10-25', 'Registered'),
(14, 4, '2026-10-25', 'Registered'),
(15, 5, '2026-10-26', 'Registered');

SELECT COUNT(*) AS student_count FROM students;
SELECT COUNT(*) AS event_count FROM events;
SELECT COUNT(*) AS registration_count FROM registrations;
