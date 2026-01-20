-- Создать таблицу со звонками:
CREATE TABLE ringtones (
    rington_number INT PRIMARY KEY,
    start_time TIME NOT NULL,
    finish_time TIME NOT NULL
);

INSERT INTO ringtones (rington_number, start_time, finish_time) VALUES
(1, '08:00:00', '09:40:00'),
(2, '09:55:00', '11:35:00'),
(3, '12:15:00', '13:55:00'),
(4, '14:10:00', '15:50:00'),
(5, '16:20:00', '18:00:00'),
(6, '18:15:00', '19:55:00');



-- Таблица расписания
CREATE TABLE schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,

    week_type ENUM('odd', 'even') NOT NULL
        COMMENT 'odd = нечетная (1,3), even = четная (2,4)',

    day_of_week ENUM(
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday'
    ) NOT NULL,

    lesson_number TINYINT NOT NULL
        COMMENT 'Номер пары',

    subject VARCHAR(255) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO schedule (week_type, day_of_week, lesson_number, subject) VALUES
-- Понедельник
('odd','monday',3,'Физ-ра'),
('odd','monday',4,'Математическое моделирование'),
('odd','monday',5,'Английский'),

-- Вторник
('odd','tuesday',3,'Охрана труда'),
('odd','tuesday',4,'Технология тестирования программного обеспечения'),
('odd','tuesday',5,'Математическое моделирование'),

-- Среда
('odd','wednesday',1,'ОАИП'),
('odd','wednesday',2,'Охрана труда'),
('odd','wednesday',3,'Физ-ра'),

-- Четверг
('odd','thursday',3,'Математическое моделирование'),
('odd','thursday',4,'АЛОВТ'),
('odd','thursday',5,'АЛОВТ'),

-- Пятница
('odd','friday',2,'Информационные технологии'),
('odd','friday',3,'Информационные технологии'),
('odd','friday',4,'Математическое моделирование'),
('odd','friday',5,'Английский'),

-- Суббота
('odd','saturday',1,'ОАИП'),
('odd','saturday',2,'ОАИП');
INSERT INTO schedule (week_type, day_of_week, lesson_number, subject) VALUES
-- Понедельник
('even','monday',2,'Информационные технологии'),
('even','monday',3,'Физ-ра'),
('even','monday',4,'Математическое моделирование'),
('even','monday',5,'Английский'),

-- Вторник
('even','tuesday',3,'Охрана труда'),
('even','tuesday',4,'Технология тестирования программного обеспечения'),
('even','tuesday',5,'Технология тестирования программного обеспечения'),

-- Среда
('even','wednesday',1,'ОАИП'),
('even','wednesday',2,'Охрана труда'),
('even','wednesday',3,'Охрана труда'),

-- Четверг
('even','thursday',3,'Математическое моделирование'),
('even','thursday',4,'АЛОВТ'),
('even','thursday',5,'АЛОВТ'),

-- Пятница
('even','friday',2,'Информационные технологии'),
('even','friday',3,'Информационные технологии'),
('even','friday',4,'Математическое моделирование'),
('even','friday',5,'Английский'),

-- Суббота
('even','saturday',1,'ОАИП'),
('even','saturday',2,'ОАИП');



-- Таблица измененного расписания
CREATE TABLE edit_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,

    week_type ENUM('odd', 'even') NOT NULL
        COMMENT 'odd = нечетная (1,3), even = четная (2,4)',

    day_of_week ENUM(
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday'
    ) NOT NULL,

    lesson_number TINYINT NOT NULL
        COMMENT 'Номер пары',

    subject VARCHAR(255) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



ALTER TABLE edit_schedule
ADD UNIQUE (week_type, day_of_week, lesson_number);

ALTER TABLE schedule
ADD UNIQUE (week_type, day_of_week, lesson_number);
