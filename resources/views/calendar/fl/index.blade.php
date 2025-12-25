@extends('template')

@section('style')

<!-- FullCalendar v6 -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/ru.global.min.js'></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/calendar.css') }}">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background: #f5f7fa;
        padding: 20px;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Шапка */
    .header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header h1 {
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #currentDate {
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
    }

    /* Панель управления */
    .control-panel {
        background: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .legend {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        padding: 5px 12px;
        background: #f8f9fa;
        border-radius: 20px;
    }

    .color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .controls {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        background: #3498db;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        transition: background 0.3s;
    }

    .btn:hover {
        background: #2980b9;
    }

    .btn-danger {
        background: #e74c3c;
    }

    .btn-danger:hover {
        background: #c0392b;
    }

    .btn i { font-size: 14px; }

    /* Календарь */
    #calendar {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    /* Модальные окна */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
    }

    .modal-content {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 10px;
        min-width: 400px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        font-size: 20px;
        color: #2c3e50;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 14px;
        cursor: pointer;
        color: white;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 30px;
    }

    .btn-cancel {
        background: #95a5a6;
    }

    .btn-cancel:hover {
        background: #7f8c8d;
    }

    /* Загрузчик */
    .loader {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(255,255,255,0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loader-spinner {
        width: 50px; height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container">
            <!-- Шапка -->
            <div class="header">
                <h1>
                    <i class="fas fa-calendar-alt"></i>
                    Календарь праздников
                </h1>
                <div id="currentDate">{{ now()->format('d.m.Y') }}</div>
            </div>

            <!-- Панель управления -->
            <div class="control-panel">
                <div class="legend">
                    <span class="legend-item">
                        <span class="color-dot" style="background: #ff6b6b;"></span>
                        Государственные
                    </span>
                    <span class="legend-item">
                        <span class="color-dot" style="background: #4ecdc4;"></span>
                        Корпоративные
                    </span>
                    <span class="legend-item">
                        <span class="color-dot" style="background: #ffd166;"></span>
                        Региональные
                    </span>
                    <span class="legend-item">
                        <span class="color-dot" style="background: #9b5de5;"></span>
                        Религиозные
                    </span>
                    <span class="legend-item">
                        <span class="color-dot" style="background: #cccccc;"></span>
                        Выходные
                    </span>
                </div>

                <div class="controls">
                    <button id="addHolidayBtn" class="btn">
                        <i class="fas fa-plus"></i> Добавить праздник
                    </button>
                    <button id="refreshBtn" class="btn">
                        <i class="fas fa-sync-alt"></i> Обновить
                    </button>
                    <button id="todayBtn" class="btn">
                        <i class="fas fa-calendar-day"></i> Сегодня
                    </button>
                </div>
            </div>

            <!-- Календарь -->
            <div id="calendar"></div>
        </div>

        <!-- Модальное окно добавления/редактирования праздника -->
        <div id="addHolidayModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitle">Добавить праздник</h3>
                    <button class="modal-close">&times;</button>
                </div>

                <form id="addHolidayForm">
                    <div class="form-group">
                        <label for="holidayTitle">Название праздника *</label>
                        <input type="text" id="holidayTitle" class="form-control" required
                            placeholder="Введите название праздника">
                    </div>

                    <div class="form-group">
                        <label for="holidayDate">Дата праздника *</label>
                        <input type="date" id="holidayDate" class="form-control" required
                            value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label for="holidayType">Тип праздника *</label>
                        <select id="holidayType" class="form-control" required>
                            <option value="">Выберите тип</option>
                            <option value="national">Государственный</option>
                            <option value="company">Корпоративный</option>
                            <option value="regional">Региональный</option>
                            <option value="religious">Религиозный</option>
                            <option value="other">Другой</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="holidayColor">Цвет оформления</label>
                        <input type="color" id="holidayColor" value="#ff6b6b"
                            style="width: 60px; height: 40px;">
                    </div>

                    <div class="form-group">
                        <label for="holidayDescription">Описание (необязательно)</label>
                        <textarea id="holidayDescription" class="form-control" rows="3"
                                placeholder="Краткое описание праздника..."></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="holidayRecurring">
                            <span>Повторяющийся праздник (каждый год)</span>
                        </label>
                    </div>



                    <div class="form-actions">
                        <button type="button" class="btn btn-cancel modal-close">Отмена</button>
                        <button type="submit" class="btn" id="submitBtn">
                            Сохранить праздник
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Лоадер -->
        <div id="loader" class="loader">
            <div class="loader-spinner"></div>
        </div>

        <!-- Контейнер для алертов -->
        <div id="alertContainer"></div>
    </div>
</div>

<script src="{{ asset('js/calendar.js') }}"></script>
@endsection
