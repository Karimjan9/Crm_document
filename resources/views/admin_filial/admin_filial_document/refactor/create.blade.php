@extends('template')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css" rel="stylesheet">

<style>
.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    margin-bottom: 5px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
}
.file-item:hover { background: #e9ecef; }
.file-info { flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.file-name { font-weight: 500; color: #212529; }
.file-size { font-size: 0.85em; color: #6c757d; }
.file-remove { color: #dc3545; background: none; border: none; cursor: pointer; font-size: 1.2em; padding: 0 5px; }
.file-remove:hover { color: #bb2d3b; }
.selected-files-list { border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 15px; min-height: 100px; max-height: 200px; overflow-y: auto; }
.no-files-message { text-align: center; padding: 20px; color: #6c757d; font-style: italic; }
.additional-services { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
.service-addon-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; margin-bottom: 8px; background: white; border: 1px solid #e9ecef; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
.service-addon-item:hover { background: #f8f9fa; border-color: #0d6efd; }
.service-addon-item.selected { background: #e7f3ff; border-color: #0d6efd; }
.service-addon-name { font-weight: 500; }
.service-addon-price { color: #28a745; font-weight: 600; }
.service-addon-checkbox { margin-right: 10px; cursor: pointer; }
</style>
@endsection

@section('body')
<div class="page-wrapper">

    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <button class="btn btn-outline-primary mb-3" id="addWizard">+ Добавить ещё</button>
                <button class="btn btn-success mb-3" id="saveAllWizards">💾 Сохранить всё</button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h1>Поиск клиента:</h1>
                    </div>
                    <div class="col-12">
                        <select id="client_id" style="width:100%"></select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createClientModal"> + Новый клиент</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12"><h1>Xizmat : <span id="totalService">0</span> сум</h1></div>
                    <div class="col-12"><h1>Diskont : <span id="totalDiscount">0</span> сум</h1></div>
                    <div class="col-12"><h1>Final narx : <span id="finalPrice">0</span> сум</h1></div>
                </div>
            </div>
        </div>

        <div id="wizardContainer"></div>
    </div>
</div>

<template id="wizardTemplate">
    <div class="card wizard-wrapper border rounded p-3 mb-4">
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-wizard">✕</button>
        <h1>Документ <span class="doc-number">0</span></h1>

        <div class="bs-stepper wizard">
            <div class="bs-stepper-header">
                <div class="step" data-target=".step-1">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label">Hujjat ma'lumotlari</span>
                    </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target=".step-2">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label">Xizmat va addonlar</span>
                    </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target=".step-3">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label">To'lov</span>
                    </button>
                </div>
                <div class="line"></div>
                <!-- <div class="step" data-target=".step-4">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">4</span>
                        <span class="bs-stepper-label">Подтверждение</span>
                    </button>
                </div> -->
            </div>

            <div class="file-upload-section">
                <div class="mb-3">
                    <label class="form-label">Загрузите файлы:</label>
                    <input type="file" class="form-control file-input" multiple accept="*/*">
                    <small class="form-text text-muted">Максимальный размер: 10MB</small>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm btn-add-more-files">+ Добавить еще</button>
                <div class="selected-files-list mb-3">
                    <div class="files-container"></div>
                    <div class="no-files-message">Файлы не выбраны</div>
                </div>
                <div class="file-stats small text-muted">
                    <span class="file-count">Файлов: 0</span>
                    <span class="total-size ms-3">Размер: 0 MB</span>
                </div>
            </div>

            <div class="bs-stepper-content">
                <div class="content step-1">
                    <div class="mb-3">
                        <label class="form-label">Hujjat turi</label>
                        <select class="form-select doc-type" style="width: 100%">
                            <option value="">Tanlang...</option>
                            @foreach($documentTypes as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apostil</label>
                        <select class="form-select direction-type" style="width: 100%">
                            <option value="">Tanlang...</option>
                            @foreach($directionTypes as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konsullik kerak</label>
                        <input class="form-check-input consulate consulate-checkbox" type="checkbox" data-price="{{ $consul_price }}">
                    </div>
                    <div class="mb-3 legalization-container">
                        <label class="form-label">Konsullik qo'shimcha</label>
                        <select class="form-select legalization" style="width: 100%">
                            <option value="">Tanlang...</option>
                            @foreach($consulateTypes as $c)
                                <option value="{{ $c->id }}" data-price="{{ $c->price }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Контейнер для дополнительных услуг от документа/direction-type/legalization -->
                    <div class="additional-services-document mb-3" style="display: none;">
                        <h6>Дополнительные услуги (документ):</h6>
                        <div class="services-list-document">
                            <!-- Список услуг будет добавлен динамически -->
                        </div>
                        <div class="text-muted small mt-2">
                            Выбрано услуг: <strong class="selected-count-document">0</strong> |
                            Общая стоимость: <strong class="addon-total-document">0</strong> сум
                        </div>
                    </div>

                    <button class="btn btn-primary btn-next">Далее</button>
                </div>

                <div class="content step-2">
                    <div class="mb-3">
                        <label class="form-label">Xizmat</label>
                        <select class="form-select service" style="width: 100%">
                            <option value="">Tanlang...</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" data-price="{{ $service->price }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Контейнер для дополнительных услуг от Xizmat -->
                    <div class="additional-services-service mb-3" style="display: none;">
                        <h6>Дополнительные услуги (Xizmat):</h6>
                        <div class="services-list-service">
                            <!-- Список услуг будет добавлен динамически -->
                        </div>
                        <div class="text-muted small mt-2">
                            Выбрано услуг: <strong class="selected-count-service">0</strong> |
                            Общая стоимость: <strong class="addon-total-service">0</strong> сум
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Xizmat narxi</label>
                        <input class="form-control total-amount" type="text" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diskont (сум)</label>
                        <input class="form-control discount" type="number" min="0" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final narx</label>
                        <input class="form-control final-amount" type="text" readonly>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-secondary btn-prev">Назад</button>
                        <button class="btn btn-primary btn-next">Далее</button>
                    </div>
                </div>

                <div class="content step-3">
                    <div class="mb-3">
                        <label class="form-label">To'lov miqdori</label>
                        <input class="form-control payment-amount" type="number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">To'lov turi</label>
                        <select class="form-select payment-type" style="width: 100%">
                            <option value="">Tanlang...</option>
                            <option value="cash">Naqd</option>
                            <option value="card">Plastik karta</option>
                            <option value="online">Onlayn</option>
                            <option value="admin_entry">Boshqalar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Izoh</label>
                        <textarea class="form-control description"></textarea>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-secondary btn-prev">Назад</button>
                        <!-- <button class="btn btn-primary btn-next">Далее</button> -->
                    </div>
                </div>

                <div class="content step-4">
                    <h5>Подтверждение данных:</h5>
                    <div class="confirm-info"></div>
                    <div class="mt-3">
                        <button class="btn btn-secondary btn-prev">Назад</button>
                        <button class="btn btn-success btn-finish">Готово</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Модальное окно для создания клиента -->
<div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createClientModalLabel">Создать нового клиента</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createClientForm">
                    <div class="mb-3">
                        <label for="clientName" class="form-label">Имя клиента <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clientName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="clientPhone" class="form-label">Номер телефона <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="clientPhone" name="phone_number"
                               placeholder="+998 90 123 45 67" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="clientNotes" class="form-label">Примечания</label>
                        <textarea class="form-control" id="clientNotes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveClientBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Сохранить
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script_include_end_body')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>

<script>
// Глобальные переменные для отслеживания общих сумм
let globalTotalAmount = 0;        // Общая сумма всех заказов
let globalTotalDiscount = 0;      // Общая скидка (не в процентах, в деньгах)
let globalFinalAmount = 0;        // Итоговая сумма с учетом скидки

// Функция обновления глобальных сумм
function updateGlobalTotals() {
    globalTotalAmount = 0;
    globalTotalDiscount = 0;
    globalFinalAmount = 0;

    // Проходим по всем wizard'ам и суммируем
    document.querySelectorAll('.wizard-wrapper').forEach(wrapper => {
        const controller = wrapper._wizardController;
        if (controller) {
            const totals = controller.getTotals();
            globalTotalAmount += totals.serviceAmount + totals.addonsAmount;
            globalTotalDiscount += totals.discountAmount;
            globalFinalAmount += totals.finalAmount;
        }
    });

    // Обновляем отображение
    document.getElementById('totalService').textContent = globalTotalAmount.toLocaleString();
    document.getElementById('totalDiscount').textContent = globalTotalDiscount.toLocaleString();
    document.getElementById('finalPrice').textContent = globalFinalAmount.toLocaleString();
}

class WizardManager {
    constructor() {
        this.wizardIndex = 0;
        this.init();
    }

    init() {
        this.initMainClient();
        this.attachEvents();
    }

    initMainClient() {
        $('#client_id').select2({
            placeholder: 'Выберите клиента',
            minimumInputLength: 2,
            ajax: {
                url: '/admin_filial/api/clients/search',
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(c => ({ id: c.id, text: `${c.name} (${c.phone_number})` }))
                })
            }
        });
    }

    attachEvents() {
        document.getElementById('addWizard').addEventListener('click', () => this.addWizard());
        document.getElementById('saveAllWizards').addEventListener('click', () => this.saveAll());
    }

    addWizard() {
        const template = document.getElementById('wizardTemplate');
        const clone = template.content.cloneNode(true);

        this.wizardIndex++;

        // Сначала добавляем в DOM
        const container = document.getElementById('wizardContainer');
        container.appendChild(clone);

        // Теперь находим добавленный wrapper (последний в списке)
        const wrappers = container.querySelectorAll('.wizard-wrapper');
        const wrapper = wrappers[wrappers.length - 1];

        // Устанавливаем номер документа
        wrapper.querySelector('.doc-number').textContent = this.wizardIndex;

        // Инициализируем контроллер
        const stepperEl = wrapper.querySelector('.wizard');
        const controller = new WizardController(stepperEl);

        // Сохраняем ссылку на контроллер для доступа к методам
        wrapper._wizardController = controller;
    }

    collectData() {
        const data = [];
        document.querySelectorAll('.wizard-wrapper').forEach((w, i) => {
            const getData = sel => w.querySelector(sel)?.value || '';
            const controller = w._wizardController;

            // Получаем данные о консульстве
            const consulateCheckbox = w.querySelector('.consulate-checkbox');
            const consulateChecked = consulateCheckbox?.checked || false;
            const consulatePrice = consulateChecked
                ? parseFloat(consulateCheckbox.dataset.price || 0)
                : 0;

            // Получаем данные о легализации
            const legalizationSelect = w.querySelector('.legalization');
            const legalizationValue = legalizationSelect?.value || '';
            const legalizationPrice = legalizationValue
                ? parseFloat(legalizationSelect.selectedOptions[0]?.dataset.price || 0)
                : 0;

            const wizardData = {
                document_type: getData('.doc-type'),
                direction_type: getData('.direction-type'),
                consulate: {
                    enabled: consulateChecked,
                    price: consulatePrice
                },
                legalization: {
                    id: legalizationValue,
                    price: legalizationPrice
                },
                selected_addons: controller ? controller.getSelectedAddons() : [],
                service: getData('.service'),
                discount: getData('.discount'),
                payment_amount: getData('.payment-amount'),
                payment_type: getData('.payment-type'),
                description: getData('.description'),
                totals: controller ? controller.getTotals() : {},
                files: Array.from(w.querySelector('.file-input')?.files || []).map(f => ({
                    name: f.name,
                    size: f.size,
                    type: f.type
                }))
            };
            data.push(wizardData);
        });
        return data;
    }

    async saveAll() {
        const data = this.collectData();
        if (data.length === 0) {
            alert('Нет данных для сохранения');
            return;
        }

        // Получаем ID клиента
        const clientId = $('#client_id').val();
        if (!clientId) {
            alert('Выберите клиента перед сохранением');
            return;
        }

        // Блокируем кнопку сохранения
        const saveBtn = document.getElementById('saveAllWizards');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение...';

        try {
            const results = [];
            let successCount = 0;
            let errorCount = 0;

            // Отправляем каждый wizard по очереди
            for (let i = 0; i < data.length; i++) {
                const wizardData = data[i];

                // Показываем прогресс
                saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Сохранение ${i + 1} из ${data.length}...`;

                try {
                    // Создаем FormData для отправки файлов
                    const formData = new FormData();

                    // Добавляем ID клиента
                    formData.append('client_id', clientId);

                    // Добавляем основные данные
                    formData.append('document_type', wizardData.document_type);
                    formData.append('direction_type', wizardData.direction_type);
                    formData.append('consulate_enabled', wizardData.consulate.enabled);
                    formData.append('consulate_price', wizardData.consulate.price);
                    formData.append('legalization_id', wizardData.legalization.id);
                    formData.append('legalization_price', wizardData.legalization.price);
                    formData.append('service', wizardData.service);
                    formData.append('discount', wizardData.discount);
                    formData.append('payment_amount', wizardData.payment_amount);
                    formData.append('payment_type', wizardData.payment_type);
                    formData.append('description', wizardData.description);

                    // Добавляем выбранные аддоны
                    formData.append('selected_addons', JSON.stringify(wizardData.selected_addons));

                    // Добавляем totals
                    formData.append('totals', JSON.stringify(wizardData.totals));

                    // Добавляем файлы
                    const wrappers = document.querySelectorAll('.wizard-wrapper');
                    const fileInput = wrappers[i].querySelector('.file-input');
                    if (fileInput && fileInput.files.length > 0) {
                        Array.from(fileInput.files).forEach((file, index) => {
                            formData.append(`files[${index}]`, file);
                        });
                    }

                    // Отправляем запрос
                    const response = await fetch('/admin_filial/api/document', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || `Ошибка при сохранении документа ${i + 1}`);
                    }

                    results.push({
                        index: i + 1,
                        success: true,
                        data: result
                    });
                    successCount++;

                    console.log(`Документ ${i + 1} успешно сохранен:`, result);

                } catch (error) {
                    console.error(`Ошибка при сохранении документа ${i + 1}:`, error);
                    results.push({
                        index: i + 1,
                        success: false,
                        error: error.message
                    });
                    errorCount++;
                }
            }

            // Показываем результаты
            this.showSaveResults(results, successCount, errorCount);

            // Если все успешно сохранено, очищаем форму
            if (errorCount === 0) {
                setTimeout(() => {
                    if (confirm('Все документы успешно сохранены! Очистить форму?')) {
                        this.clearAllWizards();
                    }
                }, 1000);
            }

        } catch (error) {
            console.error('Общая ошибка при сохранении:', error);
            alert('Произошла ошибка при сохранении данных');
        } finally {
            // Восстанавливаем кнопку
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    showSaveResults(results, successCount, errorCount) {
        const total = results.length;
        let message = `Обработано документов: ${total}\n`;
        message += `Успешно: ${successCount}\n`;

        if (errorCount > 0) {
            message += `Ошибок: ${errorCount}\n\n`;
            message += 'Документы с ошибками:\n';
            results.forEach(result => {
                if (!result.success) {
                    message += `- Документ ${result.index}: ${result.error}\n`;
                }
            });
        }

        // Показываем красивое уведомление
        const alertType = errorCount === 0 ? 'success' : (successCount === 0 ? 'danger' : 'warning');
        const alertIcon = errorCount === 0 ? 'check-circle-fill' : (successCount === 0 ? 'x-circle-fill' : 'exclamation-triangle-fill');

        const alertHtml = `
            <div class="alert alert-${alertType} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
                role="alert" style="z-index: 9999; max-width: 500px;">
                <i class="bi bi-${alertIcon} me-2"></i>
                <strong>${errorCount === 0 ? 'Успех!' : 'Завершено с ошибками'}</strong>
                <div class="mt-2">
                    <div>Всего документов: ${total}</div>
                    <div class="text-success">✓ Успешно: ${successCount}</div>
                    ${errorCount > 0 ? `<div class="text-danger">✗ Ошибок: ${errorCount}</div>` : ''}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Автоматически убираем через 5 секунд
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);

        // Если есть ошибки, выводим детали в консоль
        if (errorCount > 0) {
            console.log('Детали сохранения:', results);
        }
    }

    clearAllWizards() {
        // Удаляем все wizard'ы
        const container = document.getElementById('wizardContainer');
        container.innerHTML = '';

        // Сбрасываем счетчик
        this.wizardIndex = 0;

        // Сбрасываем выбор клиента
        $('#client_id').val(null).trigger('change');

        // Обновляем глобальные суммы
        updateGlobalTotals();
    }
}

class WizardController {
    constructor(element) {
        this.element = element;
        this.wrapper = element.closest('.wizard-wrapper');
        this.stepper = new Stepper(element);
        this.init();
    }

    init() {
        this.attachNavigation();
        this.initFileHandlers();
        this.initPriceCalculation();
        this.initAddonTracking();
        this.initConsulateToggle();
        this.attachRemoveHandler();
    }

    attachNavigation() {
        this.element.querySelectorAll('.btn-next').forEach(btn =>
            btn.onclick = () => this.stepper.next()
        );
        this.element.querySelectorAll('.btn-prev').forEach(btn =>
            btn.onclick = () => this.stepper.previous()
        );
        // this.element.querySelector('[data-target=".step-4"]').addEventListener('click', () =>
        //     this.updateConfirm()
        // );
    }

    initFileHandlers() {
        const fileInput = this.element.querySelector('.file-input');
        const addBtn = this.element.querySelector('.btn-add-more-files');

        addBtn.onclick = () => fileInput.click();
        fileInput.onchange = () => this.updateFileList();

        this.updateFileList();
    }

    updateFileList() {
        const fileInput = this.element.querySelector('.file-input');
        const container = this.element.querySelector('.files-container');
        const noFiles = this.element.querySelector('.no-files-message');
        const files = fileInput.files;

        container.innerHTML = '';
        noFiles.style.display = files.length ? 'none' : 'block';

        let totalSize = 0;
        Array.from(files).forEach((file, i) => {
            totalSize += file.size;
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${this.formatSize(file.size)}</div>
                </div>
                <button type="button" class="file-remove" data-index="${i}">×</button>
            `;
            container.appendChild(item);
        });

        container.querySelectorAll('.file-remove').forEach(btn => {
            btn.onclick = () => this.removeFile(parseInt(btn.dataset.index));
        });

        this.element.querySelector('.file-count').textContent = `Файлов: ${files.length}`;
        this.element.querySelector('.total-size').textContent = `Размер: ${this.formatSize(totalSize)}`;
    }

    removeFile(index) {
        const fileInput = this.element.querySelector('.file-input');
        const dt = new DataTransfer();
        Array.from(fileInput.files).forEach((f, i) => {
            if (i !== index) dt.items.add(f);
        });
        fileInput.files = dt.files;
        this.updateFileList();
    }

    formatSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    initPriceCalculation() {
        const serviceSelect = this.wrapper.querySelector('.service');
        const discountInput = this.wrapper.querySelector('.discount');
        const totalAmount = this.wrapper.querySelector('.total-amount');
        const finalAmount = this.wrapper.querySelector('.final-amount');
        const consulateCheckbox = this.wrapper.querySelector('.consulate-checkbox');
        const legalizationSelect = this.wrapper.querySelector('.legalization');

        const calculate = () => {
            // Цена услуги
            const servicePrice = parseFloat(serviceSelect.selectedOptions[0]?.dataset.price || 0);

            // Цена дополнительных услуг (аддонов)
            const addonsTotal = this.getAddonsTotal();

            // Цена консульства (если чекбокс выбран)
            const consulatePrice = consulateCheckbox.checked
                ? parseFloat(consulateCheckbox.dataset.price || 0)
                : 0;

            // Цена легализации (если выбрана)
            const legalizationPrice = legalizationSelect.value
                ? parseFloat(legalizationSelect.selectedOptions[0]?.dataset.price || 0)
                : 0;

            // Общая сумма до скидки
            const totalBeforeDiscount = servicePrice + addonsTotal + consulatePrice + legalizationPrice;

            // Скидка
            const discountAmount = parseFloat(discountInput.value || 0);

            // Финальная сумма
            const final = totalBeforeDiscount - discountAmount;

            totalAmount.value = totalBeforeDiscount.toLocaleString();
            finalAmount.value = final.toLocaleString();

            // Обновляем глобальные суммы
            updateGlobalTotals();
        };

        // Привязываем обработчики
        serviceSelect.addEventListener('change', calculate);
        discountInput.addEventListener('input', calculate);
        consulateCheckbox.addEventListener('change', calculate);
        legalizationSelect.addEventListener('change', calculate);

        // Начальный расчет
        calculate();
    }

    getAddonsTotal() {
        // Суммируем аддоны из обоих контейнеров
        let total = 0;

        // Аддоны документа
        const documentCheckboxes = this.wrapper.querySelectorAll('.service-addon-checkbox-document:checked');
        documentCheckboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });

        // Аддоны услуги
        const serviceCheckboxes = this.wrapper.querySelectorAll('.service-addon-checkbox-service:checked');
        serviceCheckboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });

        return total;
    }

    getTotals() {
        const serviceSelect = this.wrapper.querySelector('.service');
        const discountInput = this.wrapper.querySelector('.discount');
        const consulateCheckbox = this.wrapper.querySelector('.consulate-checkbox');
        const legalizationSelect = this.wrapper.querySelector('.legalization');

        // Цена услуги
        const servicePrice = parseFloat(serviceSelect.selectedOptions[0]?.dataset.price || 0);

        // Цена дополнительных услуг
        const addonsAmount = this.getAddonsTotal();

        // Цена консульства
        const consulatePrice = consulateCheckbox.checked
            ? parseFloat(consulateCheckbox.dataset.price || 0)
            : 0;

        // Цена легализации
        const legalizationPrice = legalizationSelect.value
            ? parseFloat(legalizationSelect.selectedOptions[0]?.dataset.price || 0)
            : 0;

        // Общая сумма до скидки
        const totalBeforeDiscount = servicePrice + addonsAmount + consulatePrice + legalizationPrice;

        // Скидка
        const discountAmount = parseFloat(discountInput.value || 0);

        // Финальная сумма
        const finalAmount = totalBeforeDiscount - discountAmount;

        return {
            serviceAmount: servicePrice,
            addonsAmount: addonsAmount,
            consulateAmount: consulatePrice,
            legalizationAmount: legalizationPrice,
            totalAmount: totalBeforeDiscount,
            discountAmount: discountAmount,
            finalAmount: finalAmount
        };
    }

    updateConfirm() {
        const getData = sel => this.wrapper.querySelector(sel);
        const confirmInfo = this.element.querySelector('.confirm-info');

        // Получаем все данные
        const docType = getData('.doc-type').selectedOptions[0]?.text || 'Не выбрано';
        const directionType = getData('.direction-type').selectedOptions[0]?.text || 'Не выбрано';
        const consulateChecked = getData('.consulate-checkbox').checked;
        const legalization = getData('.legalization').selectedOptions[0]?.text || 'Не выбрано';
        const service = getData('.service').selectedOptions[0]?.text || 'Не выбрано';
        const finalAmount = getData('.final-amount').value || '0';
        const paymentType = getData('.payment-type').selectedOptions[0]?.text || 'Не выбрано';
        const paymentAmount = getData('.payment-amount').value || '0';

        // Получаем детали цен
        const totals = this.getTotals();

        // Получаем выбранные аддоны
        const selectedAddons = this.getSelectedAddons();
        let addonsHtml = '';
        if (selectedAddons.length > 0) {
            addonsHtml = '<div class="mt-2"><strong>Дополнительные услуги:</strong><ul class="mb-0">';
            selectedAddons.forEach(addon => {
                addonsHtml += `<li>${addon.name} - ${parseFloat(addon.price).toLocaleString()} сум (${addon.type})</li>`;
            });
            addonsHtml += '</ul></div>';
        }

        confirmInfo.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Информация о документе</h6>
                    <p class="mb-1"><strong>Тип документа:</strong> ${docType}</p>
                    <p class="mb-1"><strong>Apostil:</strong> ${directionType}</p>
                    <p class="mb-1"><strong>Консульство:</strong> ${consulateChecked ? 'Да' : 'Нет'}</p>
                    ${consulateChecked && legalization !== 'Не выбрано' ? `<p class="mb-1"><strong>Легализация:</strong> ${legalization}</p>` : ''}

                    <hr>

                    <h6 class="card-subtitle mb-3 text-muted">Информация об услуге</h6>
                    <p class="mb-1"><strong>Услуга:</strong> ${service}</p>

                    ${addonsHtml}

                    <hr>

                    <h6 class="card-subtitle mb-3 text-muted">Детали стоимости</h6>
                    <p class="mb-1"><strong>Стоимость услуги:</strong> ${totals.serviceAmount.toLocaleString()} сум</p>
                    ${totals.consulateAmount > 0 ? `<p class="mb-1"><strong>Консульство:</strong> ${totals.consulateAmount.toLocaleString()} сум</p>` : ''}
                    ${totals.legalizationAmount > 0 ? `<p class="mb-1"><strong>Легализация:</strong> ${totals.legalizationAmount.toLocaleString()} сум</p>` : ''}
                    ${totals.addonsAmount > 0 ? `<p class="mb-1"><strong>Дополнительные услуги:</strong> ${totals.addonsAmount.toLocaleString()} сум</p>` : ''}
                    <p class="mb-1"><strong>Общая сумма:</strong> ${totals.totalAmount.toLocaleString()} сум</p>
                    ${totals.discountAmount > 0 ? `<p class="mb-1 text-danger"><strong>Скидка:</strong> -${totals.discountAmount.toLocaleString()} сум</p>` : ''}
                    <p class="mb-1 text-success"><strong>Финальная цена:</strong> ${totals.finalAmount.toLocaleString()} сум</p>

                    <hr>

                    <h6 class="card-subtitle mb-3 text-muted">Оплата</h6>
                    <p class="mb-1"><strong>Тип оплаты:</strong> ${paymentType}</p>
                    <p class="mb-1"><strong>Сумма оплаты:</strong> ${parseFloat(paymentAmount || 0).toLocaleString()} сум</p>
                    ${parseFloat(paymentAmount) < totals.finalAmount ? `<p class="mb-1 text-warning"><strong>Остаток:</strong> ${(totals.finalAmount - parseFloat(paymentAmount)).toLocaleString()} сум</p>` : ''}

                    <hr>

                    <p class="mb-0"><strong>Файлов загружено:</strong> ${getData('.file-input').files.length}</p>
                </div>
            </div>
        `;
    }

    attachRemoveHandler() {
        this.wrapper.querySelector('.btn-remove-wizard').onclick = () => {
            if (confirm('Удалить этот wizard?')) {
                this.wrapper.remove();
                updateGlobalTotals(); // Обновляем глобальные суммы после удаления
            }
        };
    }

    initAddonTracking() {
        const docType = this.wrapper.querySelector('.doc-type');
        const directionType = this.wrapper.querySelector('.direction-type');
        const legalization = this.wrapper.querySelector('.legalization');
        const serviceSelect = this.wrapper.querySelector('.service');

        // Загрузка аддонов для документа/direction-type/legalization
        const loadDocumentAddons = async () => {
            const docTypeVal = docType.value;
            const directionTypeVal = directionType.value;
            const legalizationVal = legalization.value;

            if (!docTypeVal && !directionTypeVal && !legalizationVal) {
                this.hideAddons('document');
                return;
            }

            const container = this.wrapper.querySelector('.additional-services-document');
            const servicesList = container.querySelector('.services-list-document');
            servicesList.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Загрузка...</div>';
            container.style.display = 'block';

            try {
                const promises = [];
                const addonTypes = []; // Массив для хранения типов

                if (docTypeVal) {
                    promises.push(this.fetchAddonsByType('document', docTypeVal));
                    addonTypes.push('document');
                }
                if (directionTypeVal) {
                    promises.push(this.fetchAddonsByType('direction', directionTypeVal));
                    addonTypes.push('direction');
                }
                // if (legalizationVal) {
                //     promises.push(this.fetchAddonsByType('consulate', legalizationVal));
                //     addonTypes.push('consulate');
                // }

                const results = await Promise.all(promises);

                // Добавляем тип к каждому аддону
                const allAddons = [];
                results.forEach((addons, index) => {
                    addons.forEach(addon => {
                        allAddons.push({
                            ...addon,
                            sourceType: addonTypes[index] // Добавляем тип источника
                        });
                    });
                });

                this.renderAddons(allAddons, 'document');
            } catch (error) {
                console.error('Ошибка при загрузке дополнительных услуг (документ):', error);
                servicesList.innerHTML = '<div class="text-danger small">Ошибка загрузки данных</div>';
            }
        };

        // Загрузка аддонов для услуги
        const loadServiceAddons = async () => {
            const serviceVal = serviceSelect.value;

            if (!serviceVal) {
                this.hideAddons('service');
                return;
            }

            const container = this.wrapper.querySelector('.additional-services-service');
            const servicesList = container.querySelector('.services-list-service');
            servicesList.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Загрузка...</div>';
            container.style.display = 'block';

            try {
                const addons = await this.fetchAddonsByType('service', serviceVal);
                // Добавляем тип к каждому аддону
                const addonsWithType = addons.map(addon => ({
                    ...addon,
                    sourceType: 'service'
                }));
                this.renderAddons(addonsWithType, 'service');
            } catch (error) {
                console.error('Ошибка при загрузке дополнительных услуг (xizmat):', error);
                servicesList.innerHTML = '<div class="text-danger small">Ошибка загрузки данных</div>';
            }
        };

        // Отслеживаем изменения
        docType.addEventListener('change', loadDocumentAddons);
        directionType.addEventListener('change', loadDocumentAddons);
        legalization.addEventListener('change', loadDocumentAddons);
        serviceSelect.addEventListener('change', loadServiceAddons);
    }

    async fetchAddonsByType(type, id) {
        try {
            const response = await fetch(`/admin_filial/api/get-addons/${type}/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) throw new Error(`Ошибка загрузки ${type}`);

            const data = await response.json();
            return Array.isArray(data) ? data : [];
        } catch (error) {
            console.error(`Ошибка при загрузке ${type}:`, error);
            return [];
        }
    }

    renderAddons(addons, containerType) {
        const suffix = containerType === 'document' ? '-document' : '-service';
        const container = this.wrapper.querySelector(`.additional-services${suffix}`);
        const servicesList = container.querySelector(`.services-list${suffix}`);

        if (!addons || addons.length === 0) {
            this.hideAddons(containerType);
            return;
        }

        let html = '';

        addons.forEach((addon, index) => {
            const uniqueId = `addon-${containerType}-${Date.now()}-${index}`;
            const sourceType = addon.sourceType || containerType; // Используем sourceType если есть

            html += `
                <div class="service-addon-item" data-price="${addon.amount}" data-id="${addon.id || index}" data-source-type="${sourceType}">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input type="checkbox"
                            class="form-check-input service-addon-checkbox service-addon-checkbox-${containerType}"
                            id="${uniqueId}"
                            data-price="${addon.amount}"
                            data-name="${addon.name}"
                            data-id="${addon.id || index}"
                            data-container="${containerType}"
                            data-source-type="${sourceType}">
                        <label for="${uniqueId}" class="flex-grow-1 mb-0 cursor-pointer">
                            <div class="service-addon-name">${addon.name}</div>
                            ${addon.description ? `<small class="text-muted">${addon.description}</small>` : ''}
                        </label>
                    </div>
                    <div class="service-addon-price">${parseFloat(addon.amount).toLocaleString()} сум</div>
                </div>
            `;
        });

        servicesList.innerHTML = html;
        container.style.display = 'block';

        // Добавляем обработчики для чекбоксов
        servicesList.querySelectorAll('.service-addon-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateAddonTotal(containerType));
        });

        // Добавляем обработчики клика по всему элементу
        servicesList.querySelectorAll('.service-addon-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.type !== 'checkbox') {
                    const checkbox = item.querySelector('.service-addon-checkbox');
                    checkbox.checked = !checkbox.checked;
                    this.updateAddonTotal(containerType);
                }
            });
        });

        this.updateAddonTotal(containerType);
    }

    updateAddonTotal(containerType) {
        const suffix = containerType === 'document' ? '-document' : '-service';
        const container = this.wrapper.querySelector(`.additional-services${suffix}`);
        const checkboxes = container.querySelectorAll(`.service-addon-checkbox-${containerType}:checked`);
        const totalSpan = container.querySelector(`.addon-total${suffix}`);
        const countSpan = container.querySelector(`.selected-count${suffix}`);

        let total = 0;
        checkboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
            cb.closest('.service-addon-item').classList.add('selected');
        });

        // Убираем класс selected у невыбранных
        container.querySelectorAll(`.service-addon-checkbox-${containerType}:not(:checked)`).forEach(cb => {
            cb.closest('.service-addon-item').classList.remove('selected');
        });

        totalSpan.textContent = total.toLocaleString();
        countSpan.textContent = checkboxes.length;

        // Пересчитываем общую сумму заказа
        const serviceSelect = this.wrapper.querySelector('.service');
        if (serviceSelect.value) {
            const discountInput = this.wrapper.querySelector('.discount');
            discountInput.dispatchEvent(new Event('input'));
        }

        updateGlobalTotals();
    }

    getSelectedAddons() {
        const selected = [];

        // Собираем аддоны из обоих контейнеров
        this.wrapper.querySelectorAll('.service-addon-checkbox:checked').forEach(cb => {
            selected.push({
                id: cb.dataset.id,
                name: cb.dataset.name,
                price: cb.dataset.price,
                type: cb.dataset.container, // 'document' или 'service'
                sourceType: cb.dataset.sourceType // 'document', 'direction', 'consulate', 'service'
            });
        });

        return selected;
    }

    hideAddons(containerType) {
        const suffix = containerType === 'document' ? '-document' : '-service';
        const container = this.wrapper.querySelector(`.additional-services${suffix}`);
        container.style.display = 'none';
        container.querySelector(`.services-list${suffix}`).innerHTML = '';
        container.querySelector(`.addon-total${suffix}`).textContent = '0';
        container.querySelector(`.selected-count${suffix}`).textContent = '0';
    }

    initConsulateToggle() {
        const consulateCheckbox = this.wrapper.querySelector('.consulate-checkbox');
        const legalizationContainer = this.wrapper.querySelector('.legalization-container');
        const legalizationSelect = this.wrapper.querySelector('.legalization');

        if (!consulateCheckbox || !legalizationContainer || !legalizationSelect) {
            console.warn('Consulate toggle elements not found');
            return;
        }

        const toggleLegalization = () => {
            if (consulateCheckbox.checked) {
                legalizationContainer.style.display = 'block';
            } else {
                legalizationContainer.style.display = 'none';
                // Сбрасываем значение селекта при скрытии
                legalizationSelect.value = '';

                // Перезагружаем addons
                const docTypeSelect = this.wrapper.querySelector('.doc-type');
                if (docTypeSelect) {
                    docTypeSelect.dispatchEvent(new Event('change'));
                }

                // Пересчитываем цены (так как легализация сброшена)
                const discountInput = this.wrapper.querySelector('.discount');
                if (discountInput) {
                    discountInput.dispatchEvent(new Event('input'));
                }
            }
        };

        consulateCheckbox.addEventListener('change', toggleLegalization);
        toggleLegalization();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new WizardManager();

    const modal = document.getElementById('createClientModal');
    const form = document.getElementById('createClientForm');
    const saveBtn = document.getElementById('saveClientBtn');
    const spinner = saveBtn.querySelector('.spinner-border');

    // Сброс формы при открытии модального окна
    modal.addEventListener('show.bs.modal', function() {
        form.reset();
        clearValidationErrors();
    });

    // Обработчик кнопки "Сохранить"
    saveBtn.addEventListener('click', async function() {
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        await createClient();
    });

    // Функция создания клиента
    async function createClient() {
        // Отключаем кнопку и показываем спиннер
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        // Собираем данные формы
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/admin_filial/api/clients', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                // Обработка ошибок валидации
                if (result.errors) {
                    displayValidationErrors(result.errors);
                } else {
                    throw new Error(result.message || 'Ошибка при создании клиента');
                }
                return;
            }

            // Успешное создание
            showSuccessMessage('Клиент успешно создан!');

            // Добавляем нового клиента в Select2
            // if (window.$ && $('#client_id').length) {
            //     const newOption = new Option(
            //         `${result.data.name} (${result.data.phone_number})`,
            //         result.data.id,
            //         true,
            //         true
            //     );
            //     $('#client_id').append(newOption).trigger('change');
            // }

            // Закрываем модальное окно
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            bootstrapModal.hide();

        } catch (error) {
            console.error('Error:', error);
            showErrorMessage(error.message || 'Произошла ошибка при создании клиента');
        } finally {
            // Включаем кнопку и скрываем спиннер
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    // Функция отображения ошибок валидации
    function displayValidationErrors(errors) {
        clearValidationErrors();

        for (const [field, messages] of Object.entries(errors)) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = messages[0];
                }
            }
        }
    }

    // Функция очистки ошибок валидации
    function clearValidationErrors() {
        form.classList.remove('was-validated');
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }

    // Функция показа сообщения об успехе
    function showSuccessMessage(message) {
        // Используем Bootstrap alert или toast
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
                 role="alert" style="z-index: 9999;">
                <i class="bi bi-check-circle-fill me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Автоматически убираем через 3 секунды
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }

    // Функция показа сообщения об ошибке
    function showErrorMessage(message) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
                 role="alert" style="z-index: 9999;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);

        setTimeout(() => {
            const alert = document.querySelector('.alert-danger');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endsection
