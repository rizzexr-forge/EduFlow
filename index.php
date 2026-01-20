<?php
    include ("database.php");
?>
<?php
function getWeekType(DateTime $date) {
    $year = (int)$date->format('Y');
    if ((int)$date->format('n') < 9) {
        $year--;
    }

    $start = new DateTime("$year-09-01");
    $days = $start->diff($date)->days;
    $weekNumber = intdiv($days, 7) + 1;

    return ($weekNumber % 2 === 1) ? 'odd' : 'even';
}

function getSchedule($conn, $weekType, $day) {
    $sql = "
        SELECT lesson_number, subject FROM edit_schedule
        WHERE week_type=? AND day_of_week=?
        UNION ALL
        SELECT lesson_number, subject FROM schedule
        WHERE week_type=? AND day_of_week=?
        AND NOT EXISTS (
            SELECT 1 FROM edit_schedule
            WHERE week_type=? AND day_of_week=?
        )
        ORDER BY lesson_number
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $weekType, $day,
        $weekType, $day,
        $weekType, $day
    );
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="media/icons/icon.png">
    <title>FARWO</title>
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
        Календарь
    </main>
</body>
</html>