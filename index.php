<?php
include("database.php");

// SQL-запрос (исправлено 'if' на 'id' и добавлены нужные колонки)
$sql = "SELECT id, start_time, end_time FROM time_schedule";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FARWO</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #ccc; }
        .time { font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>

    <h1>Расписание звонков:</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <li>
                    <?php echo $row["id"]; ?> пара:
                    <span class="time">
                        <?php echo $row["start_time"]; ?> — <?php echo $row["end_time"]; ?>
                    </span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>Расписание пусто.</p>
    <?php endif; ?>

    <?php
    // Закрытие соединения
    mysqli_close($conn);
    ?>
</body>
</html>
