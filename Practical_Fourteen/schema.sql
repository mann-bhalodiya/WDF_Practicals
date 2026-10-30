CREATE DATABASE IF NOT EXISTS studenthub_admin;
USE studenthub_admin;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(60) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE students (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(60) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    course VARCHAR(10) NOT NULL,
    year INT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE events (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(100) NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    PRIMARY KEY (id)
);

CREATE TABLE registrations (
    id INT NOT NULL AUTO_INCREMENT,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE (student_id, event_id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE TABLE audit_logs (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    details VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (name, email, password, role, status, created_at) VALUES
('Admin User',    'admin@studenthub.com',  '$2y$10$GBKO9TBXFBkZa4rPAj83hu6cM/QTArDA2mMGBmiyeWyYuEwFCxYRi', 'admin',   'active',   '2026-09-01 09:00:00'),
('Aarav Patel',   'aarav@studenthub.com',  '$2y$10$6g36rukt5PX6Owcj8xAwVuHFSx0lZeRm9u/jzR0pR6Rz7KN8Azz6q',  'student', 'active',   '2026-09-05 10:15:00'),
('Diya Shah',     'diya@studenthub.com',   '$2y$10$6g36rukt5PX6Owcj8xAwVuHFSx0lZeRm9u/jzR0pR6Rz7KN8Azz6q',  'student', 'active',   '2026-09-08 11:30:00'),
('Kabir Mehta',   'kabir@studenthub.com',  '$2y$10$6g36rukt5PX6Owcj8xAwVuHFSx0lZeRm9u/jzR0pR6Rz7KN8Azz6q',  'student', 'inactive', '2026-09-12 14:45:00'),
('Isha Desai',    'isha@studenthub.com',   '$2y$10$6g36rukt5PX6Owcj8xAwVuHFSx0lZeRm9u/jzR0pR6Rz7KN8Azz6q',  'student', 'active',   '2026-09-18 16:20:00'),
('Rohan Joshi',   'rohan@studenthub.com',  '$2y$10$6g36rukt5PX6Owcj8xAwVuHFSx0lZeRm9u/jzR0pR6Rz7KN8Azz6q',  'student', 'active',   '2026-09-25 12:10:00');

INSERT INTO students (name, email, course, year) VALUES
('Aarav Patel',   'aarav@studenthub.com',  'CSE', 2),
('Diya Shah',     'diya@studenthub.com',   'IT',  2),
('Kabir Mehta',   'kabir@studenthub.com',  'CE',  3),
('Isha Desai',    'isha@studenthub.com',   'CSE', 1),
('Rohan Joshi',   'rohan@studenthub.com',  'EC',  4),
('Meera Trivedi', 'meera@studenthub.com',  'IT',  3),
('Yash Parmar',   'yash@studenthub.com',   'ME',  2),
('Ananya Vyas',   'ananya@studenthub.com', 'CSE', 3);

INSERT INTO events (title, event_date, venue, status) VALUES
('Tech Fest 2026',        '2026-11-15', 'Main Auditorium', 'open'),
('Web Dev Workshop',      '2026-11-22', 'Lab 3',           'open'),
('Hackathon 24 Hours',    '2026-12-05', 'CSE Block',       'open'),
('Cultural Night',        '2026-12-12', 'Open Ground',     'open'),
('Annual Sports Day',     '2026-10-20', 'Sports Complex',  'closed');

INSERT INTO registrations (student_id, event_id, registered_at) VALUES
(1, 1, '2026-10-20 09:30:00'),
(2, 1, '2026-10-21 11:00:00'),
(3, 2, '2026-10-22 14:15:00'),
(4, 2, '2026-10-23 10:05:00'),
(5, 3, '2026-10-24 16:40:00'),
(1, 3, '2026-10-25 09:10:00'),
(6, 4, '2026-10-25 13:25:00'),
(7, 1, '2026-10-26 15:50:00'),
(8, 2, '2026-10-27 10:30:00'),
(2, 3, '2026-10-27 17:45:00');
