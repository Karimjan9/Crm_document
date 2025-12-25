// Инициализация календаря
let calendar = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
    initEventListeners();
    updateCurrentDate();
});

// Инициализация FullCalendar
function initCalendar() {
    const calendarEl = document.getElementById('calendar');

    // Подключаем плагины
    const plugins = [
        FullCalendar.dayGridPlugin,
        FullCalendar.timeGridPlugin,
        FullCalendar.listPlugin,
        FullCalendar.interactionPlugin
    ];

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ru',
        firstDay: 1,
        selectable: true,
        editable: false,
        navLinks: true,
        dayMaxEvents: true,
        nowIndicator: true,
        height: 'auto',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },

        buttonText: {
            today: 'Сегодня',
            month: 'Месяц',
            week: 'Неделя',
            day: 'День',
            list: 'Список'
        },

        // Загрузка событий
        events: function(fetchInfo, successCallback, failureCallback) {
            showLoader();

            // Форматируем даты для API
            const startStr = fetchInfo.startStr ? fetchInfo.startStr.split('T')[0] : '';
            const endStr = fetchInfo.endStr ? fetchInfo.endStr.split('T')[0] : '';

            console.log('Fetching calendar data:', { start: startStr, end: endStr });

            fetch(`/superadmin/fl/calendar/data?start=${startStr}&end=${endStr}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Проверяем, если пришла ошибка вместо данных
                    if (data.success === false) {
                        console.error('API error:', data.message);
                        showAlert('Ошибка загрузки данных: ' + (data.message || 'неизвестная ошибка'), 'error');
                        successCallback([]);
                        return;
                    }

                    // Проверяем, что это массив
                    if (Array.isArray(data)) {
                        console.log(`Loaded ${data.length} events`);
                        successCallback(data);
                    } else {
                        console.error('Invalid response format:', data);
                        showAlert('Неверный формат данных от сервера', 'error');
                        successCallback([]);
                    }
                })
                .catch(error => {
                    console.error('Error loading calendar data:', error);
                    showAlert('Ошибка загрузки данных календаря: ' + error.message, 'error');
                    successCallback([]);
                })
                .finally(() => {
                    hideLoader();
                });
        },

        // Клик по дате
        dateClick: function(info) {
            console.log('asfasf-asdf1');
            console.log('Date clicked:', info.dateStr);
            checkDateAvailability(info.dateStr);
        },

        // Клик по событию
        eventClick: function(info) {
            console.log('asfasf-asdf');
            console.log(info.event.extendedProps);

            if (info.event.extendedProps.type === 'holiday_label') {
                const holidayId = info.event.extendedProps.holiday_id;
                if (holidayId) {
                    showHolidayDetails(holidayId);
                }
                return false;
            }
            return true;
        },

        // При загрузке событий
        eventDidMount: function(info) {
            if (info.event.extendedProps.type === 'holiday') {
                info.el.title = info.event.title;
                info.el.style.cursor = 'pointer';

                // Добавляем иконку для праздников
                const icon = document.createElement('i');
                icon.className = 'fas fa-gift';
                icon.style.marginRight = '5px';
                icon.style.fontSize = '12px';

                const titleEl = info.el.querySelector('.fc-event-title');
                if (titleEl) {
                    titleEl.prepend(icon);
                }
            }
        },

        // При изменении даты/вью
        datesSet: function(info) {
            console.log('Dates changed:', {
                start: info.startStr,
                end: info.endStr,
                startUTC: info.start,
                endUTC: info.end
            });
        }
    });

    calendar.render();
}

// Инициализация обработчиков событий
function initEventListeners() {
    // Кнопка добавления праздника
    const addHolidayBtn = document.getElementById('addHolidayBtn');
    if (addHolidayBtn) {
        addHolidayBtn.addEventListener('click', () => {
            console.log('Add holiday button clicked');
            openAddHolidayModal();
        });
    }

    // Кнопка обновления
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            console.log('Refresh button clicked');
            calendar.refetchEvents();
            showAlert('Календарь обновлен', 'success');
        });
    }

    // Кнопка "Сегодня"
    const todayBtn = document.getElementById('todayBtn');
    if (todayBtn) {
        todayBtn.addEventListener('click', () => {
            console.log('Today button clicked');
            calendar.today();
            calendar.changeView('dayGridMonth');
        });
    }

    // Закрытие модальных окон
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Close modal button clicked');
            closeModal('addHolidayModal');
            resetHolidayForm();
        });
    });

    // Клик вне модального окна
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            console.log('Clicked outside modal');
            closeModal(event.target.id);
            resetHolidayForm();
        }
    });

    // Форма добавления праздника
    const addHolidayForm = document.getElementById('addHolidayForm');
    if (addHolidayForm) {
        addHolidayForm.addEventListener('submit', handleHolidayFormSubmit);
    }
}

// Проверка доступности даты
function checkDateAvailability(dateStr) {
    console.log('Checking date availability:', dateStr);
    showLoader();

    fetch('/superadmin/fl/calendar/check-date', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ date: dateStr })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Date availability response:', data);
        hideLoader();

        if (data.available === true) {
            openAddHolidayModal(dateStr);
        } else {
            showAlert(data.message || 'День заблокирован', 'warning', 3000);
        }
    })
    .catch(error => {
        console.error('Error checking date:', error);
        hideLoader();
        showAlert('Ошибка проверки даты: ' + error.message, 'error');
    });
}

// Открыть модальное окно добавления праздника
function openAddHolidayModal(dateStr = null) {
    console.log('Opening add holiday modal with date:', dateStr);
    const modal = document.getElementById('addHolidayModal');
    const form = document.getElementById('addHolidayForm');

    if (!modal || !form) {
        console.error('Modal or form not found');
        return;
    }

    // Сбрасываем форму
    form.reset();

    // Устанавливаем дату, если указана
    const dateInput = document.getElementById('holidayDate');
    if (dateInput) {
        if (dateStr) {
            dateInput.value = dateStr;
        } else {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    }

    // Устанавливаем цвет по умолчанию
    const colorInput = document.getElementById('holidayColor');
    if (colorInput) {
        colorInput.value = '#ff6b6b';
    }

    // Скрываем поле ID
    const idField = document.getElementById('holidayId');
    if (idField) {
        idField.remove();
    }

    // Обновляем заголовок
    const modalTitle = modal.querySelector('h3');
    if (modalTitle) {
        modalTitle.textContent = 'Добавить праздник';
    }

    // Показываем модальное окно
    modal.style.display = 'block';

    // Фокусируемся на поле названия
    setTimeout(() => {
        const titleInput = document.getElementById('holidayTitle');
        if (titleInput) {
            titleInput.focus();
        }
    }, 100);
}

// Обработка формы праздника
function handleHolidayFormSubmit(e) {
    e.preventDefault();
    console.log('Holiday form submitted');

    const titleInput = document.getElementById('holidayTitle');
    const dateInput = document.getElementById('holidayDate');
    const typeInput = document.getElementById('holidayType');
    const colorInput = document.getElementById('holidayColor');
    const descriptionInput = document.getElementById('holidayDescription');
    const recurringInput = document.getElementById('holidayRecurring');

    if (!titleInput || !dateInput || !typeInput) {
        showAlert('Не все обязательные поля заполнены', 'error');
        return;
    }

    const formData = {
        title: titleInput.value,
        date: dateInput.value,
        type: typeInput.value,
        color: colorInput ? colorInput.value : '#ff6b6b',
        description: descriptionInput ? descriptionInput.value : '',
        is_recurring: recurringInput ? (recurringInput.checked ? 1 : 0) : 0
    };

    console.log('Form data:', formData);

    // Проверяем, редактируем или создаем
    const idField = document.getElementById('holidayId');
    const url = idField && idField.value ? `/superadmin/fl/holidays/${idField.value}` : '/superadmin/fl/holidays';
    const method = idField && idField.value ? 'PUT' : 'POST';

    console.log(`Sending ${method} request to ${url}`);

    showLoader();

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Save holiday response:', data);
        hideLoader();

        if (data.success) {
            const message = idField && idField.value ? 'Праздник обновлен' : 'Праздник добавлен';
            showAlert(message, 'success');
            closeModal('addHolidayModal');
            resetHolidayForm();

            // Обновляем календарь
            if (calendar) {
                calendar.refetchEvents();
            }
        } else {
            const errorMsg = data.message || data.errors ? JSON.stringify(data.errors) : 'Ошибка сохранения праздника';
            throw new Error(errorMsg);
        }
    })
    .catch(error => {
        console.error('Error saving holiday:', error);
        hideLoader();
        showAlert('Ошибка сохранения праздника: ' + error.message, 'error');
    });
}

// Показать детали праздника
function showHolidayDetails(holidayId) {
    console.log('Showing holiday details for ID:', holidayId);
    showLoader();

    fetch(`/superadmin/fl/holidays/${holidayId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Holiday details response:', data);
            hideLoader();

            if (data.success && data.data) {
                const holiday = data.data;
                openEditHolidayModal(holiday);
            } else {
                throw new Error(data.message || 'Ошибка загрузки данных праздника');
            }
        })
        .catch(error => {
            console.error('Error loading holiday details:', error);
            hideLoader();
            showAlert('Ошибка загрузки информации о празднике: ' + error.message, 'error');
        });
}

// Открыть модальное окно редактирования праздника
function openEditHolidayModal(holiday) {
    console.log('Opening edit modal for holiday:', holiday);
    const modal = document.getElementById('addHolidayModal');
    const form = document.getElementById('addHolidayForm');

    if (!modal || !form) {
        console.error('Modal or form not found');
        return;
    }

    // Заполняем форму
    const titleInput = document.getElementById('holidayTitle');
    const dateInput = document.getElementById('holidayDate');
    const typeInput = document.getElementById('holidayType');
    const colorInput = document.getElementById('holidayColor');
    const descriptionInput = document.getElementById('holidayDescription');
    const recurringInput = document.getElementById('holidayRecurring');

    if (titleInput) titleInput.value = holiday.title || '';
    if (dateInput) dateInput.value = holiday.date ? holiday.date.split('T')[0] : '';
    if (typeInput) typeInput.value = holiday.type || 'national';
    if (colorInput) colorInput.value = holiday.color || '#ff6b6b';
    if (descriptionInput) descriptionInput.value = holiday.description || '';
    if (recurringInput) recurringInput.checked = holiday.is_recurring || false;

    // Добавляем скрытое поле с ID
    let idField = document.getElementById('holidayId');
    if (!idField) {
        idField = document.createElement('input');
        idField.type = 'hidden';
        idField.id = 'holidayId';
        idField.name = 'id';
        form.appendChild(idField);
    }
    idField.value = holiday.id;

    // Изменяем заголовок
    const modalTitle = modal.querySelector('h3');
    if (modalTitle) {
        modalTitle.textContent = 'Редактировать праздник';
    }

    // Показываем модальное окно
    modal.style.display = 'block';

    // Фокусируемся на поле названия
    setTimeout(() => {
        if (titleInput) {
            titleInput.focus();
        }
    }, 100);
}

// Вспомогательные функции
function updateCurrentDate() {
    const now = new Date();
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    const dateElement = document.getElementById('currentDate');
    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('ru-RU', options);
    }
}

function showAlert(message, type = 'info', duration = 5000) {
    console.log(`Showing alert: ${message} (${type})`);

    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        console.error('Alert container not found');
        return;
    }

    const alertId = 'alert-' + Date.now();

    const alert = document.createElement('div');
    alert.id = alertId;
    alert.className = `alert alert-${type}`;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        z-index: 1001;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        background: ${type === 'success' ? '#2ecc71' :
                     type === 'error' ? '#e74c3c' :
                     type === 'warning' ? '#f39c12' : '#3498db'};
    `;

    alert.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; margin-left: 10px; cursor: pointer; font-size: 16px;">×</button>
    `;

    alertContainer.appendChild(alert);

    if (duration > 0) {
        setTimeout(() => {
            const el = document.getElementById(alertId);
            if (el) {
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(() => {
                    if (el.parentNode) {
                        el.parentNode.removeChild(el);
                    }
                }, 300);
            }
        }, duration);
    }
}

function showLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'flex';
    }
}

function hideLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function resetHolidayForm() {
    const form = document.getElementById('addHolidayForm');
    if (form) {
        form.reset();

        const colorInput = document.getElementById('holidayColor');
        if (colorInput) {
            colorInput.value = '#ff6b6b';
        }

        const idField = document.getElementById('holidayId');
        if (idField) {
            idField.remove();
        }

        const modalTitle = document.querySelector('#addHolidayModal h3');
        if (modalTitle) {
            modalTitle.textContent = 'Добавить праздник';
        }
    }
}

// Глобальная функция удаления
window.deleteHoliday = function(id) {
    if (!confirm('Удалить праздник?')) return;

    fetch(`/superadmin/fl/holidays/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(() => {
        showAlert('Удалено', 'success');
        calendar.refetchEvents();
    });
};

// При открытии формы редактирования добавляем кнопку удаления
const originalOpenEdit = openEditHolidayModal;
openEditHolidayModal = function(holiday) {
    originalOpenEdit(holiday);

    const form = document.getElementById('addHolidayForm');
    const actions = form.querySelector('.form-actions');

    // Проверяем, есть ли уже кнопка удаления
    const existingDeleteBtn = actions.querySelector('.delete-holiday-btn');

    if (!existingDeleteBtn) {
        const deleteBtn = `<button type="button" onclick="deleteHoliday(${holiday.id})"
                         class="btn btn-danger delete-holiday-btn" style="margin-right: auto;">
                         <i class="fas fa-trash"></i> Удалить</button>`;
        actions.insertAdjacentHTML('afterbegin', deleteBtn);
    } else {
        // Обновляем onclick для существующей кнопки
        existingDeleteBtn.setAttribute('onclick', `deleteHoliday(${holiday.id})`);
    }
};
