/* js/script.js */
document.addEventListener('DOMContentLoaded', () => {
    // State
    let currentStartDate = new Date();
    // Adjust to Monday
    const day = currentStartDate.getDay();
    const diff = currentStartDate.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
    currentStartDate.setDate(diff);

    let isAdmin = false;

    // Elements
    const scheduleGrid = document.getElementById('scheduleGrid');
    const prevWeekBtn = document.getElementById('prevWeekBtn');
    const nextWeekBtn = document.getElementById('nextWeekBtn');
    const weekDatesLabel = document.getElementById('weekDates');
    const weekTypeBadge = document.getElementById('weekTypeBadge');

    const loginBtn = document.getElementById('loginBtn');
    const adminControls = document.getElementById('adminControls');
    const logoutBtn = document.getElementById('logoutBtn');
    const editScheduleBtn = document.getElementById('editScheduleBtn');

    // Modals
    const loginModal = document.getElementById('loginModal');
    const editModal = document.getElementById('editModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const closeModals = document.querySelectorAll('.close-modal');

    // Forms
    const loginForm = document.getElementById('loginForm');
    const editForm = document.getElementById('editForm');
    const loginError = document.getElementById('loginError');
    const editError = document.getElementById('editError');
    const editSuccess = document.getElementById('editSuccess');

    // --- init ---
    fetchSchedule();

    // --- Functions ---

    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    function getWeekRange(startDate) {
        const start = new Date(startDate);
        const end = new Date(startDate);
        end.setDate(end.getDate() + 6);
        return {
            start: formatDate(start),
            end: formatDate(end),
            startObj: start,
            endObj: end
        };
    }

    function updateHeader(weekType) {
        const range = getWeekRange(currentStartDate);
        const options = { month: 'long', day: 'numeric' };
        // Russian locale for dates
        const startStr = range.startObj.toLocaleDateString('ru-RU', options);
        const endStr = range.endObj.toLocaleDateString('ru-RU', options);
        weekDatesLabel.textContent = `${startStr} - ${endStr}`;

        // Translate week type
        // odd -> Нечетная (1, 3)
        // even -> Четная (2, 4)
        if (weekType === 'odd') {
            weekTypeBadge.textContent = 'Нечетная (1, 3)';
            weekTypeBadge.style.backgroundColor = '#eef2f4'; // default gray
        } else {
            weekTypeBadge.textContent = 'Четная (2, 4)';
            weekTypeBadge.style.backgroundColor = '#eef2f4';
        }
    }

    async function fetchSchedule() {
        scheduleGrid.innerHTML = '<div class="loading-state">Загрузка...</div>';
        const range = getWeekRange(currentStartDate);

        try {
            const response = await fetch(`api.php?action=get_schedule&start=${range.start}`);
            const data = await response.json();

            if (data.success) {
                isAdmin = data.is_admin;
                updateAuthUI();
                renderSchedule(data.schedule);
                updateHeader(data.week_type);
            } else {
                scheduleGrid.innerHTML = `<div class="error-state">Ошибка: ${data.message}</div>`;
            }
        } catch (e) {
            scheduleGrid.innerHTML = `<div class="error-state">Ошибка соединения: ${e.message}</div>`;
        }
    }

    function renderSchedule(days) {
        scheduleGrid.innerHTML = '';

        days.forEach(day => {
            const card = document.createElement('div');
            card.className = `day-card ${day.is_today ? 'is-today' : ''}`;

            // Translate day name manually if needed or expect API/Date to handle it.
            // API returns English day names usually (e.g. 'Monday').
            // Let's rely on internal mapping or Date object.
            // Actually, let's format the date object here for Day Name
            const dateObj = new Date(day.date);
            const dayNameIndex = dateObj.getDay(); // 0-6
            const daysRu = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
            // API might return day_name, but let's be safe
            // Wait, day.day_name from API is just format('l').

            // Let's use the index from data-day-of-week or just calculate from date.
            // Note: dateObj.getDay() returns 0 for Sunday, 1 for Monday.

            const dayName = daysRu[dayNameIndex];
            const dateStr = dateObj.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' });

            let pairsHtml = '';
            // pairs is an object/array. If array, loop. If object keys 1-6, loop 1-6.
            // In API we returned associative array $finalPairs[1..6].
            // JS receives it as object { "1": {...}, "2": {...} } or array if keys are sequential 0-indexed??
            // PHP array with keys 1,2,3... becomes Object in JSON usually.

            // Let's iterate 1 to 6
            for (let i = 1; i <= 6; i++) {
                const pair = day.pairs[i];
                if (!pair) continue; // Should exist

                let content = '';
                let classes = 'pair-content';

                if (pair.is_cancelled) {
                    // Display original subject name (or 'Отмена' if none) with strikethrough
                    const originalSubject = pair.subject || 'Отмена';
                    content = `<span class="pair-cancelled">${originalSubject}</span>`;
                } else if (pair.subject) {
                    content = pair.subject;
                    if (pair.is_override) {
                        content = `<span class="pair-highlight">${content}</span>`;
                    }
                } else {
                    content = '<span class="pair-empty">—</span>';
                }

                pairsHtml += `
                    <li class="pair-item">
                        <span class="pair-number">${i}</span>
                        <div class="pair-subject">${content}</div>
                    </li>
                `;
            }

            card.innerHTML = `
                <div class="day-header">
                    <span class="day-name">${dayName}</span>
                    <span class="day-date">${dateStr}</span>
                </div>
                <ul class="pair-list">
                    ${pairsHtml}
                </ul>
            `;
            scheduleGrid.appendChild(card);
        });
    }

    function updateAuthUI() {
        if (isAdmin) {
            loginBtn.style.display = 'none';
            adminControls.style.display = 'flex';
        } else {
            loginBtn.style.display = 'block';
            adminControls.style.display = 'none';
        }
    }

    // --- Event Listeners ---

    prevWeekBtn.addEventListener('click', () => {
        currentStartDate.setDate(currentStartDate.getDate() - 7);
        fetchSchedule();
    });

    nextWeekBtn.addEventListener('click', () => {
        currentStartDate.setDate(currentStartDate.getDate() + 7);
        fetchSchedule();
    });

    // Modals
    function openModal(modal) {
        modal.classList.add('active');
        modalBackdrop.classList.add('active');
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        modalBackdrop.classList.remove('active');
        // Clear forms/errors
        loginError.style.display = 'none';
        editError.style.display = 'none';
        editSuccess.style.display = 'none';
        loginForm.reset();
        editForm.reset();
    }

    loginBtn.addEventListener('click', () => {
        openModal(loginModal);
    });

    editScheduleBtn.addEventListener('click', () => {
        openModal(editModal);
        // Set default date to today or start of current view
        document.getElementById('editDate').value = formatDate(new Date());
    });

    modalBackdrop.addEventListener('click', () => {
        closeModal(loginModal);
        closeModal(editModal);
    });

    closeModals.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            closeModal(modal);
        });
    });

    // Auth Logic
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const password = document.getElementById('password').value;
        const username = document.getElementById('username').value;

        try {
            const res = await fetch('api.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            const data = await res.json();

            if (data.success) {
                isAdmin = true;
                updateAuthUI();
                closeModal(loginModal);
            } else {
                loginError.textContent = 'Неверный пароль';
                loginError.style.display = 'block';
            }
        } catch (err) {
            loginError.textContent = 'Ошибка сети';
            loginError.style.display = 'block';
        }
    });

    logoutBtn.addEventListener('click', async () => {
        await fetch('api.php?action=logout');
        isAdmin = false;
        updateAuthUI();
        window.location.reload(); // Reload to refresh server-side session state if needed, or just re-fetch
    });

    // Edit Logic
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const date = document.getElementById('editDate').value;
        const pair = document.getElementById('editPair').value;
        const subject = document.getElementById('editSubject').value;

        try {
            const res = await fetch('api.php?action=update_slot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ date, pair, subject })
            });
            const data = await res.json();

            if (data.success) {
                editSuccess.textContent = 'Сохранено! Обновляем...';
                editSuccess.style.display = 'block';
                setTimeout(() => {
                    closeModal(editModal);
                    fetchSchedule(); // Refresh grid
                }, 1000);
            } else {
                editError.textContent = data.message || 'Ошибка сохранения';
                editError.style.display = 'block';
            }
        } catch (err) {
            editError.textContent = 'Ошибка сети';
            editError.style.display = 'block';
        }
    });

});
