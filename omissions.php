<?php
include("database.php");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="media/icons/icon-light.png">
    <title>EduFlow</title>
</head>
<body>
    <div class="left-menu">
        <div class="up-left-menu">
            <button id="calendar">Календарь</button>
            <button id="omissions">Пропуски</button>
        </div>
        <div class="down-left-menu">
                    <input type="password" name="password" placeholder="пароль" required>
                    <button type="submit" id="log-in">Войти</button>
        </div>
    </div>

    <main>
                Календарь
    </main>
</body>
</html>