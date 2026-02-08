-- Database Setup Script

CREATE DATABASE IF NOT EXISTS university_schedule;
USE university_schedule;

-- Users Table (for admin access)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Insert default admin user (password: 'nobsac')
-- Note: In a real app, use password_hash(). For simplicity/demo as requested, we store it directly or simple hash.
-- User requested "password also in db" and "nobsac".
-- We will store it as a hash for security best practice, but PHP will verify it.
-- INSERT INTO users (username, password) VALUES ('admin', 'nobsac'); (If cleartext)
-- Let's use standard PHP password_hash for 'nobsac' -> '$2y$10$...'
-- For now, we'll insert a placeholder and update it via PHP or just use cleartext if strictly requested "password in db". 
-- Examples show "password - nobsac". We will use cleartext for *simplicity* of the user checking the DB, 
-- but I will add a comment that hashing is better.
-- Actually, let's just use password_hash in the PHP script and insert a known hash here.
-- Hash for 'nobsac' (bcrypt default cost): $2y$10$z.S.7.1.5.3.9. . . (just kidding, I'll generate one or lets just use plain text for the 'password' field and hash in PHP? No, PHP verify needs hash).
-- Okay, I will insert the user via PHP code if it doesn't exist, OR just provide the SQL.
-- Let's just use cleartext for this specific request "password also in db" to ensure they can see it. 
-- Wait, "password also in db" implies storage. I'll use a simple table, and in PHP I'll check `if ($input === $row['password'])`. 
-- This is insecure but matches the "store password in db" literal request for a simple student project.
CREATE TABLE IF NOT EXISTS users_simple (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO users_simple (username, password) SELECT 'admin', 'nobsac' WHERE NOT EXISTS (SELECT * FROM users_simple WHERE username = 'admin');

-- Base Schedule Template
CREATE TABLE IF NOT EXISTS week_template (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_type ENUM('odd', 'even') NOT NULL,
    day_of_week INT NOT NULL, -- 1=Monday, 7=Sunday
    pair_number INT NOT NULL, -- 1-6
    subject_name VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_slot (week_type, day_of_week, pair_number)
);

-- Schedule Overrides (for specific dates)
CREATE TABLE IF NOT EXISTS schedule_overrides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    override_date DATE NOT NULL,
    pair_number INT NOT NULL,
    new_subject_name VARCHAR(255), -- NULL if cancelled
    is_cancelled BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_override (override_date, pair_number)
);

-- Initial Data Population (Week Templates)

-- ODD WEEK (1, 3)
INSERT INTO week_template (week_type, day_of_week, pair_number, subject_name) VALUES
-- Monday
('odd', 1, 3, 'Физ-ра'),
('odd', 1, 4, 'Математическое моделирование'),
('odd', 1, 5, 'Английский'),
-- Tuesday
('odd', 2, 3, 'Охрана труда'),
('odd', 2, 4, 'Технология тестирования программного обеспечения'),
('odd', 2, 5, 'Математическое моделирование'),
-- Wednesday
('odd', 3, 1, 'ОАИП'),
('odd', 3, 2, 'Охрана труда'),
('odd', 3, 3, 'Физ-ра'),
-- Thursday
('odd', 4, 3, 'Математическое моделирование'),
('odd', 4, 4, 'АЛОВТ'),
('odd', 4, 5, 'АЛОВТ'),
-- Friday
('odd', 5, 2, 'Информационные технологии'),
('odd', 5, 3, 'Информационные технологии'),
('odd', 5, 4, 'Математическое моделирование'),
('odd', 5, 5, 'Английский'),
-- Saturday
('odd', 6, 1, 'ОАИП'),
('odd', 6, 2, 'ОАИП');

-- EVEN WEEK (2, 4)
INSERT INTO week_template (week_type, day_of_week, pair_number, subject_name) VALUES
-- Monday
('even', 1, 2, 'Информационные технологии'),
('even', 1, 3, 'Физ-ра'),
('even', 1, 4, 'Математическое моделирование'),
('even', 1, 5, 'Английский'),
-- Tuesday
('even', 2, 3, 'Охрана труда'),
('even', 2, 4, 'Технология тестирования программного обеспечения'),
('even', 2, 5, 'Технология тестирования программного обеспечения'),
-- Wednesday
('even', 3, 1, 'ОАИП'),
('even', 3, 2, 'Охрана труда'),
('even', 3, 3, 'Охрана труда'),
-- Thursday
('even', 4, 3, 'Математическое моделирование'),
('even', 4, 4, 'АЛОВТ'),
('even', 4, 5, 'АЛОВТ'),
-- Friday
('even', 5, 2, 'Информационные технологии'),
('even', 5, 3, 'Информационные технологии'),
('even', 5, 4, 'Математическое моделирование'),
('even', 5, 5, 'Английский'),
-- Saturday
('even', 6, 1, 'ОАИП'),
('even', 6, 2, 'ОАИП');
