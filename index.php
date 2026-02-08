<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Университетское Расписание</title>
    <link rel="icon" href="icons/icon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=YS+Text:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Fallback to system fonts if YS Text isn't available, but trying to match Yandex style -->
</head>
<body>

    <header class="app-header">
        <div class="header-content">
            <h1 class="logo">Расписание</h1>
            
            <div class="week-navigation">
                <button id="prevWeekBtn" class="nav-btn" aria-label="Предыдущая неделя">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="current-week-info">
                    <span id="weekDates">...</span>
                    <span id="weekTypeBadge" class="week-badge">...</span>
                </div>
                <button id="nextWeekBtn" class="nav-btn" aria-label="Следующая неделя">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            <div class="auth-section">
                <button id="loginBtn" class="btn btn-primary">Войти</button>
                <div id="adminControls" class="admin-controls" style="display: none;">
                    <button id="editScheduleBtn" class="btn btn-secondary">Править</button>
                    <button id="logoutBtn" class="btn btn-text">Выйти</button>
                </div>
            </div>
        </div>
    </header>

    <main class="schedule-container" id="scheduleGrid">
        <!-- Schedule cards will be injected here by JS -->
        <div class="loading-state">Загрузка...</div>
    </main>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Вход в систему</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required placeholder="Введите пароль">
                </div>
                <!-- Username is hidden/fixed as 'admin' for this simple use case -->
                <input type="hidden" id="username" value="admin">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Войти</button>
                </div>
                <div id="loginError" class="error-message"></div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Редактирование пары</h2>
            <form id="editForm">
                <div class="form-group">
                    <label for="editDate">Дата</label>
                    <input type="date" id="editDate" required>
                </div>
                <div class="form-group">
                    <label for="editPair">Номер пары</label>
                    <select id="editPair" required>
                        <option value="1">1 пара</option>
                        <option value="2">2 пара</option>
                        <option value="3">3 пара</option>
                        <option value="4">4 пара</option>
                        <option value="5">5 пара</option>
                        <option value="6">6 пара</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editSubject">Предмет</label>
                    <input type="text" id="editSubject" placeholder="Название предмета или пусто для отмены">
                    <small class="hint">Оставьте пустым, чтобы отменить пару</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Сохранить</button>
                </div>
                <div id="editError" class="error-message"></div>
                <div id="editSuccess" class="success-message"></div>
            </form>
        </div>
    </div>

    <!-- Overlay backdrop -->
    <div id="modalBackdrop" class="modal-backdrop"></div>

    <script src="js/script.js"></script>
</body>
</html>
