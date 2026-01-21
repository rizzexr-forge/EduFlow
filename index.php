<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("database.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Обработка выхода
if (isset($_POST['logout'])) {
    unset($_SESSION['is_admin']);
    // Сохраняем месяц при перезагрузке
    $redirect = isset($_GET['month']) ? '?month=' . $_GET['month'] : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $redirect);
    exit;
}

// Обработка входа (простое сравнение текста)
$is_admin = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $input_pass = $_POST['password'];
    $res = mysqli_query($conn, "SELECT admin_password FROM admin LIMIT 1");
    if ($row = mysqli_fetch_assoc($res)) {
        if ($input_pass === $row['admin_password']) {
            $_SESSION['is_admin'] = true;
            $is_admin = true;
        }
    }
}
$is_admin = !empty($_SESSION['is_admin']);

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

// Загружаем основное расписание
$schedule_base = [];
$result_base = mysqli_query($conn, "SELECT * FROM schedule");
while ($row = mysqli_fetch_assoc($result_base)) {
    $key = $row['week_type'] . '|' . $row['day_of_week'] . '|' . $row['lesson_number'];
    $schedule_base[$key] = htmlspecialchars($row['subject']);
}

// Загружаем изменения на текущий месяц
$month_start = $first_day->format('Y-m-01');
$month_end = $last_day->format('Y-m-t');
$edited_lessons = [];
$result_edit = mysqli_query($conn, "
    SELECT lesson_date, lesson_number, subject 
    FROM edit_schedule 
    WHERE lesson_date BETWEEN '$month_start' AND '$month_end'
");
while ($row = mysqli_fetch_assoc($result_edit)) {
    $key = $row['lesson_date'] . '|' . $row['lesson_number'];
    $edited_lessons[$key] = htmlspecialchars($row['subject']);
}

// Генерация календаря
$current = clone $first_day;
$current->modify('first day of this month');
$current->modify('Monday this week');
if ($current->format('Y-m') !== $first_day->format('Y-m') && $current > $first_day) {
    $current->modify('-7 days');
}
$end = clone $last_day;
$end->modify('+6 days');
$view_year_month = $view_month;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="media/icons/icon-light.png">
    <title>EduFlow</title>




    <!-- Open Graph / Facebook / Telegram -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://vash-sait.ru">
<meta property="og:title" content="EduFlow">
<meta property="og:description" content="Запись расписаний и освобождений">
<meta property="og:image" content="https://vash-sait.rupath/to/image.jpg">




    <style>
        .lesson-edited {
            color: #000;
            background-color: #00FFEA;
            padding: 1px 4px;
            border-radius: 3px;
            margin: -2px -4px;
        }

        #editModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        #editModal > div {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
        }
        #editModal label {
            display: block;
            margin: 8px 0 4px;
            font-weight: bold;
        }
        #editModal input,
        #editModal select,
        #editModal button {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        #editModal button {
            background: #8967FD;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        #editModal button:nth-child(2) {
            background: #ccc;
            color: black;
        }
    </style>
</head>
<body>
    
    <div class="left-menu">
        <div class="up-left-menu">
            <button id="calendar">Календарь</button>
            <button id="omissions">Пропуски</button>
        </div>
        <div class="down-left-menu">
            <?php if ($is_admin): ?>
                <!-- После входа: только "Править" и "Выйти" -->
                <button id="edit-schedule">Править</button>
                <form method="POST" style="margin-top: 5px;">
                    <button type="submit" name="logout" id="logout" style="background: #ff6b6b; color: white;">Выйти</button>
                </form>
            <?php else: ?>
                <!-- До входа: только поле и "Войти" -->
                <form method="POST" style="display: flex; flex-direction: column; gap: 5px;">
                    <input type="password" name="password" placeholder="пароль" required>
                    <button type="submit" id="log-in">Войти</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <main>
        <div class="calendar-nav">
            <a href="?month=<?= $prev_link ?>" class="nav-btn">назад</a>
            <h2><?= $first_day->format('F Y') ?></h2>
            <a href="?month=<?= $next_link ?>" class="nav-btn">вперед</a>
        </div>

        <div class="calendar-grid">
            <div class="calendar-header">Пн</div>
            <div class="calendar-header">Вт</div>
            <div class="calendar-header">Ср</div>
            <div class="calendar-header">Чт</div>
            <div class="calendar-header">Пт</div>
            <div class="calendar-header">Сб</div>
            <div class="calendar-header" id="vs">Вс</div>

            <?php
            while ($current <= $end) {
                $is_current_month = ($current->format('Y-m') === $view_year_month);
                $day_num = $current->format('j');
                $dow_php = (int)$current->format('N');
                $date_str = $current->format('Y-m-d');

                $classes = ['calendar-day'];
                if (!$is_current_month) $classes[] = 'other-month';
                if ($dow_php == 7) $classes[] = 'sunday';

                echo "<div class=\"" . implode(' ', $classes) . "\">";
                echo "<div class=\"day-number\">$day_num</div>";

                if ($dow_php == 7) {
                    echo "<div class=\"sunday-text\">Выходной</div>";
                } else {
                    $week_type = getWeekType($current);
                    $dow_names = ['monday','tuesday','wednesday','thursday','friday','saturday'];
                    $dow_key = $dow_names[$dow_php - 1];

                    $has_any = false;
                    echo "<ul class=\"lessons\">";
                    for ($lesson_num = 1; $lesson_num <= 6; $lesson_num++) {
                        $edit_key = $date_str . '|' . $lesson_num;
                        if (isset($edited_lessons[$edit_key])) {
                            $subject = $edited_lessons[$edit_key];
                            $is_edited = true;
                            $has_any = true;
                        } else {
                            $base_key = $week_type . '|' . $dow_key . '|' . $lesson_num;
                            if (isset($schedule_base[$base_key])) {
                                $subject = $schedule_base[$base_key];
                                $is_edited = false;
                                $has_any = true;
                            } else {
                                continue;
                            }
                        }

                        $li_class = $is_edited ? 'lesson-edited' : '';
                        echo "<li class=\"$li_class\"><span class=\"lesson-num\">{$lesson_num}.</span> {$subject}</li>";
                    }
                    echo "</ul>";

                    if (!$has_any) {
                        echo "<div class=\"no-lessons\">—</div>";
                    }
                }

                echo "</div>";
                $current->modify('+1 day');
            }
            ?>
        </div>
    </main>

    <!-- Модальное окно редактирования -->
    <div id="editModal">
        <div>
            <h3>Изменить расписание</h3>
            <label>Дата: <input type="date" id="editDate" required></label>
            <label>Номер пары:
                <select id="editLesson" required>
                    <option value="">—</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
            </label>
            <label>Предмет:
                <select id="editSubject" required>
                    <option value="">—</option>
                    <option value="Физ-ра">Физ-ра</option>
                    <option value="Математическое моделирование">Математическое моделирование</option>
                    <option value="Английский">Английский</option>
                    <option value="Охрана труда">Охрана труда</option>
                    <option value="Технология тестирования программного обеспечения">Технология тестирования программного обеспечения</option>
                    <option value="ОАИП (Объектно-ориентированный анализ и проектирование / Основы алгоритмизации и программирования)">ОАИП</option>
                    <option value="АЛОВТ (Архитектурно-логические основы вычислительной техники)">АЛОВТ</option>
                </select>
            </label>
            <button onclick="saveEdit()">Сохранить</button>
            <button type="button" onclick="closeModal()">Отмена</button>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('editModal').style.display = 'flex';
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('editDate').value = today;
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function saveEdit() {
    const date = document.getElementById('editDate').value;
    const lesson = document.getElementById('editLesson').value;
    const subject = document.getElementById('editSubject').value;

    if (!date || !lesson || !subject) {
        alert('Заполните все поля');
        return;
    }

    fetch('save_edit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ date, lesson, subject })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Сохранено!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'неизвестная'));
        }
    })
    .catch(e => {
        alert('Ошибка сети');
    });
}

        const editBtn = document.getElementById('edit-schedule');
        if (editBtn) {
            editBtn.addEventListener('click', openModal);
        }
    </script>
</body>
</html>