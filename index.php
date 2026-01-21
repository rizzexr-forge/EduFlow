<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("database.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Определяем текущий месяц
$view_month = isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])
    ? $_GET['month']
    : date('Y-m');

$first_day = new DateTime($view_month . '-01');
$last_day = clone $first_day;
$last_day->modify('last day of this month');

// Кнопки навигации
$prev_month = clone $first_day;
$prev_month->modify('-1 month');
$prev_link = $prev_month->format('Y-m');

$next_month = clone $first_day;
$next_month->modify('+1 month');
$next_link = $next_month->format('Y-m');

// Функция: определить тип недели (odd/even)
if (!function_exists('getWeekType')) {
    function getWeekType(DateTime $date): string {
        $start_academic = new DateTime('2025-09-01');
        if ($date < $start_academic) {
            return 'odd';
        }
        $interval = $start_academic->diff($date);
        $weeks = floor($interval->days / 7) + 1;
        return ($weeks % 2 === 1) ? 'odd' : 'even';
    }
}

// Получаем расписание (edit_schedule имеет приоритет)
$query = "
    SELECT s.day_of_week, s.lesson_number, s.subject, s.week_type
    FROM (
        SELECT * FROM edit_schedule
        UNION ALL
        SELECT * FROM schedule WHERE NOT EXISTS (
            SELECT 1 FROM edit_schedule e
            WHERE e.week_type = schedule.week_type
              AND e.day_of_week = schedule.day_of_week
              AND e.lesson_number = schedule.lesson_number
        )
    ) AS s
    ORDER BY s.week_type, s.day_of_week, s.lesson_number
";

$result = mysqli_query($conn, $query);
$schedule_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $key = $row['week_type'] . '|' . $row['day_of_week'];
    if (!isset($schedule_data[$key])) {
        $schedule_data[$key] = [];
    }
    $schedule_data[$key][] = [
        'lesson' => $row['lesson_number'],
        'subject' => htmlspecialchars($row['subject'])
    ];
}

// Генерация календаря: начинаем с понедельника!
$current = clone $first_day;
$current->modify('first day of this month');
// Сдвигаемся к понедельнику той же недели
$current->modify('Monday this week');
if ($current->format('Y-m') !== $first_day->format('Y-m') && $current > $first_day) {
    $current->modify('-7 days');
}

$end = clone $last_day;
$end->modify('+6 days'); // чтобы показать полную последнюю неделю
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="media/icons/icon-light.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">

    <title>EduFlow</title>
</head>
<body>
    
    <div class="left-menu">
        <div class="up-left-menu">
            <button id="calendar">Календарь</button>
            <button id="omissions">Пропуски</button>
        </div>
        <div class="down-left-menu">
            <input type="text" id="password" placeholder="пароль">
            <button id="log-in">Войти</button>
        </div>
    </div>

    <main>
        <div class="calendar-nav">
            <!-- <a href="?month=<?= $prev_link ?>" class="nav-btn">&laquo; Пред.</a> -->
            <a href="?month=<?= $prev_link ?>" class="nav-btn">назад</a>
            <h2><?= $first_day->format('F Y') ?></h2>
            <!-- <a href="?month=<?= $next_link ?>" class="nav-btn">След. &raquo;</a> -->
            <a href="?month=<?= $next_link ?>" class="nav-btn">вперед</a>
        </div>

        <div class="calendar-grid">
            <!-- Заголовки дней недели -->
            <div class="calendar-header">Пн</div>
            <div class="calendar-header">Вт</div>
            <div class="calendar-header">Ср</div>
            <div class="calendar-header">Чт</div>
            <div class="calendar-header">Пт</div>
            <div class="calendar-header">Сб</div>
            <div class="calendar-header" id="vs">Вс</div>

            <?php
            while ($current <= $end) {
                $is_current_month = ($current->format('Y-m') === $view_month);
                $day_num = $current->format('j');
                $dow_php = (int)$current->format('N'); // 1=Пн, 7=Вс
                $dow_names = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                $dow_key = $dow_names[$dow_php - 1];
                $week_type = getWeekType($current);

                $classes = ['calendar-day'];
                if (!$is_current_month) $classes[] = 'other-month';
                if ($dow_php == 7) $classes[] = 'sunday';

                echo "<div class=\"" . implode(' ', $classes) . "\">";
                echo "<div class=\"day-number\">$day_num</div>";

                if ($dow_php == 7) {
                    echo "<div class=\"sunday-text\">Выходной</div>";
                } else {
                    $key = $week_type . '|' . $dow_key;
                    if (!empty($schedule_data[$key])) {
                        echo "<ul class=\"lessons\">";
                        foreach ($schedule_data[$key] as $lesson) {
                            echo "<li><span class=\"lesson-num\">{$lesson['lesson']}.</span> {$lesson['subject']}</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<div class=\"no-lessons\">—</div>";
                    }
                }

                echo "</div>";

                $current->modify('+1 day');
            }
            ?>
        </div>
    </main>
</body>
</html>