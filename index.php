<?php
include("database.php");

/* ==== АВТОРИЗАЦИЯ ==== */
if (isset($_POST['login']) && $_POST['pass'] === 'odessabest') $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); }
$isAdmin = isset($_SESSION['admin']);

/* ==== СОХРАНЕНИЕ ПРАВОК ==== */
if ($isAdmin && isset($_POST['update_lesson'])) {
    $dateVal = $_POST['date'];
    $num = (int)$_POST['lesson_num'];
    $sub = mysqli_real_escape_string($conn, $_POST['subject_name']);
    mysqli_query($conn,"
        INSERT INTO schedule_exceptions (date_val, lesson_num, subject_name)
        VALUES ('$dateVal',$num,'$sub')
        ON DUPLICATE KEY UPDATE subject_name='$sub'
    ");
}

/* ==== КАЛЕНДАРЬ ==== */
$month = $_GET['m'] ?? date('m');
$year  = $_GET['y'] ?? date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDay = date('N', strtotime("$year-$month-01"));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>FARWO System</title>

<style>
/* ================= RESET ================= */
* { box-sizing: border-box; }
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    overflow: hidden; /* ❗ НЕ КРУТИТСЯ */
    font-family: Inter, system-ui, sans-serif;
    background: #f4f6fb;
}

/* ================= LAYOUT ================= */
.wrapper {
    display: grid;
    grid-template-columns: 80px 1fr;
    height: 100vh;
}

/* ================= SIDEBAR ================= */
.sidebar {
    background: #fff;
    border-right: 1px solid #eaeaea;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 0;
    gap: 22px;
}
.sidebar span {
    font-size: 20px;
    opacity: .5;
    cursor: pointer;
}
.sidebar span:hover { opacity: 1; }

/* ================= CONTENT ================= */
.container {
    display: grid;
    grid-template-rows: auto 1fr auto;
    padding: 24px;
    gap: 16px;
}

/* ================= CALLS ================= */
.calls-card {
    background: #fff;
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,.05);
}
.calls-grid {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.call-item {
    background: #f3f5fa;
    border-radius: 12px;
    padding: 10px 16px;
    font-size: 13px;
}

/* ================= CALENDAR ================= */
.calendar-card {
    background: #fff;
    border-radius: 20px;
    padding: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,.06);
    overflow: hidden;
}
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7,1fr);
    gap: 8px;
    height: calc(100vh - 260px);
    overflow-y: auto;
}
.day-name {
    text-align: center;
    font-size: 12px;
    color: #999;
}
.day-cell {
    background: #f6f7fb;
    border-radius: 14px;
    padding: 8px;
    font-size: 11px;
}
.day-cell.today {
    outline: 2px solid #6c5ce7;
}
.day-num {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}
.lesson-mini {
    background: #fff;
    border-left: 3px solid #6c5ce7;
    border-radius: 6px;
    padding: 2px 6px;
    margin-bottom: 3px;
    font-size: 11px;
    position: relative;
}
.dot {
    width: 6px;
    height: 6px;
    background: #f1c40f;
    border-radius: 50%;
    position: absolute;
    right: 4px;
    top: 4px;
}

/* ================= ADMIN ================= */
.admin-edit {
    margin-top: 6px;
    font-size: 10px;
    border: none;
    background: #6c5ce7;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    padding: 3px 6px;
}
.btn {
    border: none;
    background: #6c5ce7;
    color: #fff;
    border-radius: 10px;
    padding: 8px 14px;
    cursor: pointer;
}

/* ================= MODAL ================= */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    align-items: center;
    justify-content: center;
}
.modal form {
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    width: 260px;
}
.modal input {
    width: 100%;
    padding: 8px;
    margin-bottom: 8px;
}
</style>
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <span>🏠</span>
        <span>📚</span>
        <span>📅</span>
        <span>🔔</span>
        <span>⚙️</span>
    </aside>

    <!-- CONTENT -->
    <div class="container">

        <!-- ЗВОНКИ -->
        <div class="calls-card">
            <h3 style="margin:0 0 10px">Расписание звонков</h3>
            <div class="calls-grid">
                <?php
                $calls = mysqli_query($conn,"SELECT * FROM time_schedule");
                while($c=mysqli_fetch_assoc($calls))
                    echo "<div class='call-item'><b>{$c['id']} пара</b><br>".substr($c['start_time'],0,5)."–".substr($c['end_time'],0,5)."</div>";
                ?>
            </div>
        </div>

        <!-- КАЛЕНДАРЬ -->
        <div class="calendar-card">
            <div class="calendar-header">
                <a class="btn" href="?m=<?= $month-1 ?>&y=<?= $year ?>">←</a>
                <b><?= date('F Y',strtotime("$year-$month-01")) ?></b>
                <a class="btn" href="?m=<?= $month+1 ?>&y=<?= $year ?>">→</a>
            </div>

            <div class="calendar-grid">
                <?php
                $days=['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
                foreach($days as $d) echo "<div class='day-name'>$d</div>";
                for($i=1;$i<$firstDay;$i++) echo "<div></div>";

                for($day=1;$day<=$daysInMonth;$day++):
                    $date=sprintf("%04d-%02d-%02d",$year,$month,$day);
                    $dow=date('N',strtotime($date));
                ?>
                <div class="day-cell <?= $date==date('Y-m-d')?'today':'' ?>">
                    <span class="day-num"><?= $day ?></span>
                    <?php
                    if($dow<7){
                        $l=mysqli_query($conn,"
                            SELECT s.lesson_num,
                                   COALESCE(e.subject_name,s.subject_name) subj,
                                   e.subject_name IS NOT NULL ch
                            FROM standard_schedule s
                            LEFT JOIN schedule_exceptions e
                                ON e.lesson_num=s.lesson_num AND e.date_val='$date'
                            WHERE s.day_of_week=$dow
                        ");
                        while($r=mysqli_fetch_assoc($l)){
                            echo "<div class='lesson-mini'><b>{$r['lesson_num']}.</b> {$r['subj']}".($r['ch']?"<span class='dot'></span>":"")."</div>";
                        }
                        if($isAdmin) echo "<button class='admin-edit' onclick=\"openEditor('$date')\">Править</button>";
                    }
                    ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- АДМИН -->
        <div style="text-align:center">
        <?php if(!$isAdmin): ?>
            <form method="post">
                <input type="password" name="pass" placeholder="Пароль">
                <button class="btn" name="login">Войти</button>
            </form>
        <?php else: ?>
            <a href="?logout=1">Выйти</a>
        <?php endif; ?>
        </div>

    </div>
</div>

<!-- MODAL -->
<div class="modal" id="editor">
<form method="post">
    <input type="hidden" name="date" id="date">
    <input type="number" name="lesson_num" placeholder="№ пары">
    <input type="text" name="subject_name" placeholder="Предмет">
    <button class="btn" name="update_lesson">Сохранить</button>
</form>
</div>

<script>
function openEditor(d){
    editor.style.display='flex';
    date.value=d;
}
editor.onclick=e=>{
    if(e.target.id==='editor') editor.style.display='none';
}
</script>

</body>
</html>
