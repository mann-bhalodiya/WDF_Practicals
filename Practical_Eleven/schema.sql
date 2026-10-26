CREATE DATABASE IF NOT EXISTS practical11_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical11_db;
CREATE TABLE IF NOT EXISTS students (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_name VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL UNIQUE,
 course VARCHAR(80) NOT NULL,
 year_of_study TINYINT UNSIGNED NOT NULL,
 mobile VARCHAR(20) NULL,
 status ENUM('active','deleted') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CHECK (year_of_study BETWEEN 1 AND 6)
) ENGINE=InnoDB;
INSERT IGNORE INTO students (student_name,email,course,year_of_study,mobile) VALUES
('Aarav Patel','aarav.patel@example.com','BCA',1,'9876500001'),
('Diya Shah','diya.shah@example.com','BBA',2,'9876500002'),
('Rohan Mehta','rohan.mehta@example.com','B.Tech',3,'9876500003'),
('Ananya Desai','ananya.desai@example.com','B.Sc',1,'9876500004'),
('Vivaan Joshi','vivaan.joshi@example.com','BCA',3,'9876500005'),
('Ishita Trivedi','ishita.trivedi@example.com','BBA',2,'9876500006'),
('Arjun Modi','arjun.modi@example.com','B.Com',3,'9876500007'),
('Meera Patel','meera.patel@example.com','BCA',2,'9876500008'),
('Krish Malhotra','krish.malhotra@example.com','B.Tech',4,'9876500009'),
('Kiara Shah','kiara.shah@example.com','B.Sc',2,'9876500010'),
('Dev Patel','dev.patel@example.com','B.Com',1,'9876500011'),
('Myra Mehta','myra.mehta@example.com','BBA',3,'9876500012'),
('Aditya Shah','aditya.shah@example.com','B.Tech',2,'9876500013'),
('Sara Desai','sara.desai@example.com','BCA',1,'9876500014'),
('Yash Trivedi','yash.trivedi@example.com','B.Com',4,'9876500015'),
('Nisha Patel','nisha.patel@example.com','B.Sc',3,'9876500016'),
('Rahul Shah','rahul.shah@example.com','BCA',2,'9876500017'),
('Pooja Mehta','pooja.mehta@example.com','BBA',1,'9876500018'),
('Harsh Desai','harsh.desai@example.com','B.Tech',3,'9876500019'),
('Riya Joshi','riya.joshi@example.com','B.Com',2,'9876500020');
