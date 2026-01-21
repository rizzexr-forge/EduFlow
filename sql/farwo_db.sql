-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Янв 21 2026 г., 18:32
-- Версия сервера: 8.0.43
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `farwo_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `admin_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `admin`
--

INSERT INTO `admin` (`id`, `admin_password`) VALUES
(1, 'nobsac');

-- --------------------------------------------------------

--
-- Структура таблицы `edit_schedule`
--

CREATE TABLE `edit_schedule` (
  `id` int NOT NULL,
  `lesson_date` date NOT NULL,
  `lesson_number` tinyint NOT NULL,
  `subject` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `ringtones`
--

CREATE TABLE `ringtones` (
  `rington_number` int NOT NULL,
  `start_time` time NOT NULL,
  `finish_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `ringtones`
--

INSERT INTO `ringtones` (`rington_number`, `start_time`, `finish_time`) VALUES
(1, '08:00:00', '09:40:00'),
(2, '09:55:00', '11:35:00'),
(3, '12:15:00', '13:55:00'),
(4, '14:10:00', '15:50:00'),
(5, '16:20:00', '18:00:00'),
(6, '18:15:00', '19:55:00');

-- --------------------------------------------------------

--
-- Структура таблицы `schedule`
--

CREATE TABLE `schedule` (
  `id` int NOT NULL,
  `week_type` enum('odd','even') NOT NULL COMMENT 'odd = нечетная (1,3), even = четная (2,4)',
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `lesson_number` tinyint NOT NULL COMMENT 'Номер пары',
  `subject` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `schedule`
--

INSERT INTO `schedule` (`id`, `week_type`, `day_of_week`, `lesson_number`, `subject`, `created_at`) VALUES
(1, 'odd', 'monday', 3, 'Физ-ра', '2026-01-20 20:12:37'),
(2, 'odd', 'monday', 4, 'Математическое моделирование', '2026-01-20 20:12:37'),
(3, 'odd', 'monday', 5, 'Английский', '2026-01-20 20:12:37'),
(4, 'odd', 'tuesday', 3, 'Охрана труда', '2026-01-20 20:12:37'),
(5, 'odd', 'tuesday', 4, 'Технология тестирования программного обеспечения', '2026-01-20 20:12:37'),
(6, 'odd', 'tuesday', 5, 'Математическое моделирование', '2026-01-20 20:12:37'),
(7, 'odd', 'wednesday', 1, 'ОАИП', '2026-01-20 20:12:37'),
(8, 'odd', 'wednesday', 2, 'Охрана труда', '2026-01-20 20:12:37'),
(9, 'odd', 'wednesday', 3, 'Физ-ра', '2026-01-20 20:12:37'),
(10, 'odd', 'thursday', 3, 'Математическое моделирование', '2026-01-20 20:12:37'),
(11, 'odd', 'thursday', 4, 'АЛОВТ', '2026-01-20 20:12:37'),
(12, 'odd', 'thursday', 5, 'АЛОВТ', '2026-01-20 20:12:37'),
(13, 'odd', 'friday', 2, 'Информационные технологии', '2026-01-20 20:12:37'),
(14, 'odd', 'friday', 3, 'Информационные технологии', '2026-01-20 20:12:37'),
(15, 'odd', 'friday', 4, 'Математическое моделирование', '2026-01-20 20:12:37'),
(16, 'odd', 'friday', 5, 'Английский', '2026-01-20 20:12:37'),
(17, 'odd', 'saturday', 1, 'ОАИП', '2026-01-20 20:12:37'),
(18, 'odd', 'saturday', 2, 'ОАИП', '2026-01-20 20:12:37'),
(19, 'even', 'monday', 2, 'Информационные технологии', '2026-01-20 20:12:37'),
(20, 'even', 'monday', 3, 'Физ-ра', '2026-01-20 20:12:37'),
(21, 'even', 'monday', 4, 'Математическое моделирование', '2026-01-20 20:12:37'),
(22, 'even', 'monday', 5, 'Английский', '2026-01-20 20:12:37'),
(23, 'even', 'tuesday', 3, 'Охрана труда', '2026-01-20 20:12:37'),
(24, 'even', 'tuesday', 4, 'Технология тестирования программного обеспечения', '2026-01-20 20:12:37'),
(25, 'even', 'tuesday', 5, 'Технология тестирования программного обеспечения', '2026-01-20 20:12:37'),
(26, 'even', 'wednesday', 1, 'ОАИП', '2026-01-20 20:12:37'),
(27, 'even', 'wednesday', 2, 'Охрана труда', '2026-01-20 20:12:37'),
(28, 'even', 'wednesday', 3, 'Охрана труда', '2026-01-20 20:12:37'),
(29, 'even', 'thursday', 3, 'Математическое моделирование', '2026-01-20 20:12:37'),
(30, 'even', 'thursday', 4, 'АЛОВТ', '2026-01-20 20:12:37'),
(31, 'even', 'thursday', 5, 'АЛОВТ', '2026-01-20 20:12:37'),
(32, 'even', 'friday', 2, 'Информационные технологии', '2026-01-20 20:12:37'),
(33, 'even', 'friday', 3, 'Информационные технологии', '2026-01-20 20:12:37'),
(34, 'even', 'friday', 4, 'Математическое моделирование', '2026-01-20 20:12:37'),
(35, 'even', 'friday', 5, 'Английский', '2026-01-20 20:12:37'),
(36, 'even', 'saturday', 1, 'ОАИП', '2026-01-20 20:12:37'),
(37, 'even', 'saturday', 2, 'ОАИП', '2026-01-20 20:12:37');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `edit_schedule`
--
ALTER TABLE `edit_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_date` (`lesson_date`,`lesson_number`);

--
-- Индексы таблицы `ringtones`
--
ALTER TABLE `ringtones`
  ADD PRIMARY KEY (`rington_number`);

--
-- Индексы таблицы `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `week_type` (`week_type`,`day_of_week`,`lesson_number`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `edit_schedule`
--
ALTER TABLE `edit_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
