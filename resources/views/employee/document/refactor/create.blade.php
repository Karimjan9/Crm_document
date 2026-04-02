@extends('template')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --ink: #0f172a;
    --muted: #64748b;
    --surface: #ffffff;
    --surface-2: #f8fafc;
    --border: #e2e8f0;
    --accent: #0ea5a4;
    --accent-2: #2563eb;
    --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    --radius: 12px;
}

.page-wrapper {
    font-family: 'Manrope', sans-serif;
    color: var(--ink);
    background:
        radial-gradient(1200px 500px at 0% -10%, rgba(14, 165, 164, 0.12), transparent 60%),
        linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: 100vh;
    padding-bottom: 40px;
}

.page-wrapper h1 {
    font-size: 1.2rem;
    font-weight: 700;
    letter-spacing: 0.2px;
    margin-bottom: 0.5rem;
}

.page-wrapper .card {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.page-wrapper .card-body {
    padding: 1.25rem 1.5rem;
}

.page-wrapper .btn {
    border-radius: 10px;
    font-weight: 600;
}

.page-wrapper .btn-outline-primary {
    border-color: var(--accent-2);
    color: var(--accent-2);
}
.page-wrapper .btn-outline-primary:hover {
    background: var(--accent-2);
    color: #fff;
}

.page-wrapper .form-control,
.page-wrapper .form-select,
.page-wrapper .select2-container .select2-selection--single {
    border-radius: 10px;
    border-color: var(--border);
    min-height: 42px;
}

.page-wrapper .form-control:focus,
.page-wrapper .form-select:focus,
.page-wrapper .select2-container--default .select2-selection--single:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 164, 0.15);
}

.page-wrapper .bs-stepper .step-trigger {
    border-radius: 999px;
    padding: 10px 14px;
}
.page-wrapper .bs-stepper .step.active .bs-stepper-circle {
    background-color: var(--accent);
}

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
.selected-files-list { border: 1px solid var(--border); border-radius: 0.75rem; padding: 15px; min-height: 100px; max-height: 200px; overflow-y: auto; background: var(--surface); }
.no-files-message { text-align: center; padding: 20px; color: var(--muted); font-style: italic; }
.additional-services,
.additional-services-document,
.additional-services-direction,
.additional-services-service {
    background: var(--surface-2);
    padding: 14px;
    border-radius: 10px;
    border: 1px solid var(--border);
}
.service-addon-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.service-addon-item:hover { background: #f1f5f9; border-color: var(--accent-2); }
.service-addon-item.selected { background: rgba(14, 165, 164, 0.08); border-color: var(--accent); }
.service-addon-name { font-weight: 600; }
.service-addon-price { color: #16a34a; font-weight: 700; }
.service-addon-checkbox { margin-right: 10px; cursor: pointer; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    @php
        $directions = $directions ?? $directionTypes ?? collect();
    @endphp

    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <button class="btn btn-outline-primary mb-3" id="addWizard">+ Yana qo'shish</button>
                <button class="btn btn-success mb-3" id="saveAllWizards">Barchasini saqlash</button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h1>Mijozni qidirish:</h1>
                    </div>
                    <div class="col-12">
                        <select id="client_id" style="width:100%"></select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createClientModal"> + Yangi mijoz</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12"><h1>Xizmat : <span id="totalService">0</span> so'm</h1></div>
                    <div class="col-12"><h1>Diskont : <span id="totalDiscount">0</span> so'm</h1></div>
                    <div class="col-12"><h1>Final narx : <span id="finalPrice">0</span> so'm</h1></div>
                </div>
            </div>
        </div>

        <div id="wizardContainer"></div>
    </div>
</div>

<template id="wizardTemplate">
    <div class="card wizard-wrapper border rounded p-3 mb-4">
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-wizard">✕</button>
        <h1>Hujjat <span class="doc-number">0</span></h1>

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
                        <span class="bs-stepper-label">Tasdiqlash</span>
                    </button>
                </div> -->
            </div>

            <div class="file-upload-section">
                <div class="mb-3">
                    <label class="form-label">Fayllarni yuklang:</label>
                    <input type="file" class="form-control file-input" multiple accept="*/*">
                    <small class="form-text text-muted">Maksimal hajm: 10MB</small>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm btn-add-more-files">+ Yana qo'shish</button>
                <div class="selected-files-list mb-3">
                    <div class="files-container"></div>
                    <div class="no-files-message">Fayllar tanlanmagan</div>
                </div>
                <div class="file-stats small text-muted">
                    <span class="file-count">Fayllar: 0</span>
                    <span class="total-size ms-3">Hajm: 0 MB</span>
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

{{-- ✅ Yo'nalish tugmalari: faqat doc-type tanlangandan keyin ko‘rinadi --}}
<div class="mb-3 process-wrapper" style="display:none;">
    <label class="form-label d-block">Yo'nalish</label>

    <div class="btn-group w-100" role="group">
        <button type="button" class="btn btn-outline-primary btn-process" data-mode="apostil">
            Apostil
        </button>
        <button type="button" class="btn btn-outline-primary btn-process" data-mode="consul">
            Konsullik
        </button>
    </div>

    <input type="hidden" class="process-mode" value="">
</div>

{{-- ✅ APOSTIL --}}
<div class="mb-3 apostil-block" style="display:none;">
    <div class="border rounded p-3 mb-2">
        <div class="fw-semibold mb-2">Apostil - 1-guruh</div>
        <div class="row g-2">
            @foreach($apostilStatics->where('group_id', 1) as $a)
                <div class="col-12 col-md-6">
                    <label class="border rounded p-2 w-100 d-flex align-items-center gap-2">
                        <input type="radio"
                               name="apostil_g1___U___"
                               class="form-check-input apostil-g1"
                               value="{{ $a->id }}"
                               data-price="{{ $a->price }}"
                               data-days="{{ $a->days }}">
                        <span class="flex-grow-1">{{ $a->name }}</span>
                        <span class="text-success fw-semibold">{{ number_format($a->price,0,'',' ') }} so'm</span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border rounded p-3">
        <div class="fw-semibold mb-2">Apostil - 2-guruh</div>
        <div class="row g-2">
            @foreach($apostilStatics->where('group_id', 2) as $a)
                <div class="col-12 col-md-6">
                    <label class="border rounded p-2 w-100 d-flex align-items-center gap-2">
                        <input type="radio"
                               name="apostil_g2___U___"
                               class="form-check-input apostil-g2"
                               value="{{ $a->id }}"
                               data-price="{{ $a->price }}"
                               data-days="{{ $a->days }}">
                        <span class="flex-grow-1">{{ $a->name }}</span>
                        <span class="text-success fw-semibold">{{ number_format($a->price,0,'',' ') }} so'm</span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-3 direction-block" style="display:none;">
    <label class="form-label">Yo'nalish (Direction)</label>
    <select class="form-select direction-type" style="width:100%">
        <option value="">Tanlang...</option>
        @foreach($directions as $d)
            <option value="{{ $d->id }}"
                    data-price="{{ $d->price ?? $d->amount ?? 0 }}"
                    data-days="{{ $d->day ?? 0 }}">
                {{ $d->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- ✅ KONSULLIK --}}
<div class="mb-3 consul-block" style="display:none;">
    <div class="border rounded p-3">
        <div class="fw-semibold mb-2">Konsullik</div>
        <div class="row g-2">
            @foreach($consuls as $c)
                <div class="col-12 col-md-6">
                    <label class="border rounded p-2 w-100 d-flex align-items-center gap-2">
                        <input type="radio"
                               name="consul_main___U___"
                               class="form-check-input consul-main"
                               value="{{ $c->id }}"
                               data-price="{{ $c->amount }}"
                               data-days="{{ $c->day }}">
                        <span class="flex-grow-1">{{ $c->name }}</span>
                        <span class="text-success fw-semibold">{{ number_format($c->amount,0,'',' ') }} so'm</span>
                    </label>
                </div>
            @endforeach
        </div>
        <div class="mt-3 legalization-container" style="display:none;">
            <label class="form-label">Konsullik turi</label>
            <select class="form-select legalization" style="width:100%">
                <option value="">Tanlang...</option>
                @foreach($consulateTypes as $ct)
                    <option value="{{ $ct->id }}" data-price="{{ $ct->amount ?? 0 }}">
                        {{ $ct->name }} - {{ number_format($ct->amount ?? 0,0,'',' ') }} so'm
                    </option>
                @endforeach
            </select>
        </div>
        <input type="checkbox" class="consulate-checkbox d-none" data-price="0">
    </div>
</div>

{{-- ✅ Addonlar faqat yo'nalish tanlovi tugaganda ko‘rinadi --}}
<div class="after-choice-block" style="display:none;">
    <div class="additional-services-direction mb-3" style="display:none;">
        <h6>Qo'shimcha xizmatlar (yo'nalish):</h6>
        <div class="services-list-direction"></div>
        <div class="text-muted small mt-2">
            Tanlangan xizmatlar: <strong class="selected-count-direction">0</strong> |
            Umumiy qiymat: <strong class="addon-total-direction">0</strong> so'm
        </div>
    </div>
    <div class="additional-services-document mb-3" style="display:none;">
        <h6>Qo'shimcha xizmatlar (hujjat):</h6>
        <div class="services-list-document"></div>
        <div class="text-muted small mt-2">
            Tanlangan xizmatlar: <strong class="selected-count-document">0</strong> |
            Umumiy qiymat: <strong class="addon-total-document">0</strong> so'm
        </div>
    </div>
</div>
                  

                    <button class="btn btn-primary btn-next">Keyingi</button>
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

                    <!-- Xizmatdan qo'shimcha xizmatlar konteyneri -->
                    <div class="additional-services-service mb-3" style="display: none;">
                        <h6>Qo'shimcha xizmatlar (Xizmat):</h6>
                        <div class="services-list-service">
                            <!-- Xizmatlar ro'yxati dinamik qo'shiladi -->
                        </div>
                        <div class="text-muted small mt-2">
                            Tanlangan xizmatlar: <strong class="selected-count-service">0</strong> |
                            Umumiy qiymat: <strong class="addon-total-service">0</strong> so'm
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Xizmat narxi</label>
                        <input class="form-control total-amount" type="text" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diskont (so'm)</label>
                        <input class="form-control discount" type="number" min="0" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final narx</label>
                        <input class="form-control final-amount" type="text" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Izoh</label>
                        <textarea class="form-control description" rows="5"></textarea>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-secondary btn-prev">Orqaga</button>
                        <button class="btn btn-primary btn-next">Keyingi</button>
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
                    <div class="mt-3">
                        <button class="btn btn-secondary btn-prev">Orqaga</button>
                        <!-- <button class="btn btn-primary btn-next">Keyingi</button> -->
                    </div>
                </div>

                <div class="content step-4">
                    <h5>Ma'lumotlarni tasdiqlash:</h5>
                    <div class="confirm-info"></div>
                    <div class="mt-3">
                        <button class="btn btn-secondary btn-prev">Orqaga</button>
                        <button class="btn btn-success btn-finish">Tayyor</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Mijoz yaratish uchun modal oyna -->
<div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createClientModalLabel">Yangi mijoz yaratish</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createClientForm">
                    <div class="mb-3">
                        <label for="clientName" class="form-label">Mijoz ismi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clientName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="clientPhone" class="form-label">Telefon raqami <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="clientPhone" name="phone_number"
                               placeholder="+998 90 123 45 67" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="clientNotes" class="form-label">Izoh</label>
                        <textarea class="form-control" id="clientNotes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="button" class="btn btn-primary" id="saveClientBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Saqlash
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
const apiBase = "{{ url('employee/api') }}";
// Umumiy summalarni kuzatish uchun global o'zgaruvchilar
let globalTotalAmount = 0;        // Barcha buyurtmalar jami summasi
let globalTotalDiscount = 0;      // Umumiy chegirma (foiz emas, aniq summa)
let globalFinalAmount = 0;        // Chegirma hisobga olingan yakuniy summa

// Umumiy summalarni yangilash funksiyasi
function updateGlobalTotals() {
    globalTotalAmount = 0;
    globalTotalDiscount = 0;
    globalFinalAmount = 0;

    // Barcha wizardlar bo'yicha yurib, yig'amiz
    document.querySelectorAll('.wizard-wrapper').forEach(wrapper => {
        const controller = wrapper._wizardController;
        if (controller) {
            const totals = controller.getTotals();
            globalTotalAmount += totals.serviceAmount + totals.addonsAmount;
            globalTotalDiscount += totals.discountAmount;
            globalFinalAmount += totals.finalAmount;
        }
    });

    // Ko'rinishni yangilaymiz
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
            placeholder: 'Mijozni tanlang',
            minimumInputLength: 2,
            ajax: {
                url: `${apiBase}/clients/search`,
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

        // Avval DOMga qo'shamiz
        const container = document.getElementById('wizardContainer');
        container.appendChild(clone);

        // Endi qo'shilgan wrapperni topamiz (ro'yxatdagi oxirgisi)
        const wrappers = container.querySelectorAll('.wizard-wrapper');
        const wrapper = wrappers[wrappers.length - 1];

        // Hujjat raqamini o'rnatamiz
        wrapper.querySelector('.doc-number').textContent = this.wizardIndex;

        // Kontrollerni ishga tushiramiz
        const stepperEl = wrapper.querySelector('.wizard');
        const controller = new WizardController(stepperEl);

        // Metodlarga kirish uchun kontrollerga havolani saqlaymiz
        wrapper._wizardController = controller;
    }

    collectData() {
        
        const data = [];
        
        document.querySelectorAll('.wizard-wrapper').forEach((w, i) => {
            const getData = sel => w.querySelector(sel)?.value || '';
            
            const controller = w._wizardController;

            // Konsullik ma'lumotlarini olamiz
            const consulateCheckbox = w.querySelector('.consulate-checkbox');
            const consulateChecked = consulateCheckbox?.checked || false;
            const consulatePrice = consulateChecked
                ? parseFloat(consulateCheckbox.dataset.price || 0)
                : 0;

            // Legallashtirish ma'lumotlarini olamiz
            const legalizationSelect = w.querySelector('.legalization');
            const legalizationValue = legalizationSelect?.value || '';
            const legalizationPrice = legalizationValue
                ? parseFloat(legalizationSelect.selectedOptions[0]?.dataset.price || 0)
                : 0;

            const wizardData = {
                process_mode: getData('.process-mode'),
                    apostil: {
                    group1_id: w.querySelector('.apostil-g1:checked')?.value || null,
                    group2_id: w.querySelector('.apostil-g2:checked')?.value || null,
                    },
                    consul: {
                    consul_id: w.querySelector('.consul-main:checked')?.value || null,
                    },

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
            alert("Saqlash uchun ma'lumot yo'q");
            return;
        }

        // Mijoz ID sini olamiz
        const clientId = $('#client_id').val();
        if (!clientId) {
            alert('Saqlashdan oldin mijozni tanlang');
            return;
        }
        
        // Saqlash tugmasini bloklaymiz
        const saveBtn = document.getElementById('saveAllWizards');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saqlanmoqda...';

        try {
            const results = [];
            let successCount = 0;
            let errorCount = 0;

            // Har bir wizardni navbatma-navbat yuboramiz
            for (let i = 0; i < data.length; i++) {
                const wizardData = data[i];

                // Jarayonni ko'rsatamiz
                saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Saqlanmoqda ${i + 1} / ${data.length}...`; 

                try {
                    // Fayllarni yuborish uchun FormData yaratamiz
                    const formData = new FormData();
                    const appendIfFilled = (key, value) => {
                        if (value === null || value === undefined) return;
                        const normalized = String(value).trim();
                        if (!normalized) return;
                        const lowered = normalized.toLowerCase();
                        if (lowered === 'null' || lowered === 'undefined') return;
                        formData.append(key, normalized);
                    };

                    // Mijoz ID sini qo'shamiz
                    formData.append('client_id', clientId);

                    // Asosiy ma'lumotlarni qo'shamiz
                    appendIfFilled('document_type_id', wizardData.document_type);
                    appendIfFilled('direction_type_id', wizardData.direction_type);
                    formData.append('consulate_enabled', wizardData.consulate.enabled);
                    formData.append('consulate_price', wizardData.consulate.price);
                    appendIfFilled('consulate_type_id', wizardData.legalization.id);
                    appendIfFilled('legalization_price', wizardData.legalization.price);
                    appendIfFilled('service_id', wizardData.service);
                    appendIfFilled('discount', wizardData.discount);
                    appendIfFilled('payment_amount', wizardData.payment_amount);
                    appendIfFilled('payment_type', wizardData.payment_type);
                    appendIfFilled('description', wizardData.description);
                    appendIfFilled('process_mode', wizardData.process_mode);
                    if (wizardData.process_mode === 'apostil') {
                        appendIfFilled('apostil_group1_id', wizardData.apostil.group1_id);
                        appendIfFilled('apostil_group2_id', wizardData.apostil.group2_id);
                    }
                    if (wizardData.process_mode === 'consul') {
                        appendIfFilled('consul_id', wizardData.consul.consul_id);
                    }
                    // Tanlangan qo'shimchalarni qo'shamiz
                    formData.append('selected_addons', JSON.stringify(wizardData.selected_addons));

                    // Totallarni qo'shamiz
                    formData.append('totals', JSON.stringify(wizardData.totals));

                    // Fayllarni qo'shamiz
                    const wrappers = document.querySelectorAll('.wizard-wrapper');
                    const fileInput = wrappers[i].querySelector('.file-input');
                    if (fileInput && fileInput.files.length > 0) {
                        Array.from(fileInput.files).forEach((file, index) => {
                            formData.append(`files[${index}]`, file);
                        });
                    }

                    // So'rov yuboramiz
                    const response = await fetch(`${apiBase}/document`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: formData
                    });

                    const responseText = await response.text();
                    let result = {};
                    let invalidJson = false;
                    try {
                        result = responseText ? JSON.parse(responseText) : {};
                    } catch (e) {
                        invalidJson = true;
                        result = {
                            message: `Server JSON qaytarmadi (status ${response.status})`,
                            raw: responseText
                        };
                    }

                    if (!response.ok || invalidJson) {
                        let errorMessage = result.message || `Hujjatni saqlashda xato ${i + 1} (status ${response.status})`;
                        if (result.errors) {
                            const details = Object.values(result.errors).flat().filter(Boolean);
                            if (details.length) {
                                errorMessage += ': ' + details.join('; ');
                            }
                        } else if (result.raw) {
                            const rawText = String(result.raw)
                                .replace(/<[^>]*>/g, ' ')
                                .replace(/\s+/g, ' ')
                                .trim()
                                .slice(0, 200);
                            if (rawText) {
                                errorMessage += `: ${rawText}`;
                            }
                        }
                        throw new Error(errorMessage);
                    }

                    results.push({
                        index: i + 1,
                        success: true,
                        data: result
                    });
                    successCount++;

                    console.log(`Hujjat ${i + 1} muvaffaqiyatli saqlandi:`, result);

                } catch (error) {
                    console.error(`Hujjatni saqlashda xato ${i + 1}:`, error);
                    results.push({
                        index: i + 1,
                        success: false,
                        error: error.message
                    });
                    errorCount++;
                }
            }

         
            this.showSaveResults(results, successCount, errorCount);

           
            if (errorCount === 0) {
                setTimeout(() => {
                    if (confirm('Barcha hujjatlar saqlandi! Formani tozalaysizmi?')) {
                        this.clearAllWizards();
                    }
                }, 1000);
            }

        } catch (error) {
            console.error('Saqlashdagi umumiy xato:', error);
            alert("Ma'lumotlarni saqlashda xatolik yuz berdi");
        } finally {
            // Tugmani tiklaymiz
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    showSaveResults(results, successCount, errorCount) {
        const total = results.length;
        let message = `Qayta ishlangan hujjatlar: ${total}\n`;
        message += `Muvaffaqiyatli: ${successCount}\n`;

        if (errorCount > 0) {
            message += `Xatolar: ${errorCount}\n\n`;
            message += 'Xatoli hujjatlar:\n';
            results.forEach(result => {
                if (!result.success) {
                    message += `- Hujjat ${result.index}: ${result.error}\n`;
                }
            });
        }

        const escapeHtml = (str) => String(str).replace(/[&<>"']/g, (m) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[m]));

        const errorItems = results
            .filter(r => !r.success)
            .map(r => `<li>Hujjat ${r.index}: ${escapeHtml(r.error)}</li>`)
            .join('');

        const errorListHtml = errorItems
            ? `<div class="mt-2"><div class="text-danger">Xatolar:</div><ul class="mb-0">${errorItems}</ul></div>`
            : '';

        // Chiroyli bildirishnomani ko'rsatamiz
        const alertType = errorCount === 0 ? 'success' : (successCount === 0 ? 'danger' : 'warning');
        const alertIcon = errorCount === 0 ? 'check-circle-fill' : (successCount === 0 ? 'x-circle-fill' : 'exclamation-triangle-fill');

        const alertHtml = `
            <div class="alert alert-${alertType} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
                role="alert" style="z-index: 9999; max-width: 500px;">
                <i class="bi bi-${alertIcon} me-2"></i>
                <strong>${errorCount === 0 ? 'Muvaffaqiyat!' : 'Xatolar bilan yakunlandi'}</strong>
                <div class="mt-2">
                    <div>Jami hujjatlar: ${total}</div>
                    <div class="text-success">? Muvaffaqiyatli: ${successCount}</div>
                    ${errorCount > 0 ? `<div class="text-danger">? Xatolar: ${errorCount}</div>` : ''}
                </div>
                ${errorListHtml}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // 5 soniyadan keyin avtomatik olib tashlaymiz
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);

        // Xatolar bo'lsa, tafsilotlarni konsolga chiqaramiz
        if (errorCount > 0) {
            console.log('Saqlash tafsilotlari:', results);
        }
    }

    clearAllWizards() {
        // Barcha wizardlarni o'chiramiz
        const container = document.getElementById('wizardContainer');
        container.innerHTML = '';

        // Hisoblagichni nolga tushiramiz
        this.wizardIndex = 0;

        // Mijoz tanlovini tozalaymiz
        $('#client_id').val(null).trigger('change');

        // Umumiy summalarni yangilaymiz
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

        this.initApostilConsulSwitch(); 
        this.initAddonTracking();       
        this.initPriceCalculation();   

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
    initApostilConsulSwitch() {
    const w = this.wrapper;

    // ✅ unik radio name (har wizard mustaqil)
    const uid = 'w' + Math.random().toString(36).slice(2, 9);
    w.querySelectorAll('input[type="radio"][name*="___U___"]').forEach(r => {
        r.name = r.name.replace('___U___', uid);
    });

    const docType = w.querySelector('.doc-type');

    const processWrapper = w.querySelector('.process-wrapper');
    const btns = w.querySelectorAll('.btn-process');
    const modeInput = w.querySelector('.process-mode');

    const apostilBlock = w.querySelector('.apostil-block');
    const consulBlock  = w.querySelector('.consul-block');
    const afterBlock   = w.querySelector('.after-choice-block');
    const directionBlock = w.querySelector('.direction-block');
    const directionSelect = w.querySelector('.direction-type');
    const serviceSelect = w.querySelector('.service');
    const legalizationContainer = w.querySelector('.legalization-container');
    const legalizationSelect = w.querySelector('.legalization');
    const consulateCheckbox = w.querySelector('.consulate-checkbox');

    const clearRadios = (selector) => w.querySelectorAll(selector).forEach(r => r.checked = false);

    const setActiveBtn = (mode) => {
        btns.forEach(b => {
            const active = b.dataset.mode === mode;
            b.classList.toggle('btn-primary', active);
            b.classList.toggle('btn-outline-primary', !active);
            b.classList.toggle('active', active);
        });
    };

    const showMode = (mode) => {
        apostilBlock.style.display = (mode === 'apostil') ? 'block' : 'none';
        consulBlock.style.display  = (mode === 'consul')  ? 'block' : 'none';
    };

    const hideAll = () => {
        apostilBlock.style.display = 'none';
        consulBlock.style.display  = 'none';
        afterBlock.style.display   = 'none';
        if (directionBlock) directionBlock.style.display = 'none';
    };

    const resetAll = () => {
        clearRadios('.apostil-g1, .apostil-g2, .consul-main');
        afterBlock.style.display = 'none';
        if (directionBlock) directionBlock.style.display = 'none';
        if (directionSelect) directionSelect.value = '';
        this.hideAddons('direction');
        if (legalizationContainer) legalizationContainer.style.display = 'none';
        if (legalizationSelect) legalizationSelect.value = '';
        if (consulateCheckbox) consulateCheckbox.checked = false;

        // ✅ totals qayta hisob
        w.querySelector('.discount')?.dispatchEvent(new Event('input'));
        updateGlobalTotals();
    };

    const checkReady = () => {
        const mode = modeInput.value;

        if (mode === 'apostil') {
            const ok = !!w.querySelector('.apostil-g1:checked') && !!w.querySelector('.apostil-g2:checked');
            if (directionBlock) directionBlock.style.display = ok ? 'block' : 'none';

            const hasDirection = !!directionSelect?.value;
            afterBlock.style.display = ok && hasDirection ? 'block' : 'none';
            if (!hasDirection) {
                this.hideAddons('direction');
                this.hideAddons('document');
            }

            if (ok && hasDirection) {
                docType?.dispatchEvent(new Event('change'));
                directionSelect?.dispatchEvent(new Event('change'));
            }

            if (legalizationContainer) legalizationContainer.style.display = 'none';
            if (legalizationSelect) legalizationSelect.value = '';
            if (consulateCheckbox) consulateCheckbox.checked = false;
        } else if (mode === 'consul') {
            const hasConsul = !!w.querySelector('.consul-main:checked');
            const hasLegalization = !!(legalizationSelect && legalizationSelect.value);
            const ok = hasConsul || hasLegalization;
            afterBlock.style.display = ok ? 'block' : 'none';
            if (directionBlock) directionBlock.style.display = 'none';
            if (directionSelect) directionSelect.value = '';
            this.hideAddons('direction');
            if (legalizationContainer) legalizationContainer.style.display = 'block';
            if (consulateCheckbox) consulateCheckbox.checked = ok;
            if (hasConsul && !hasLegalization && legalizationSelect) {
                const firstOption = legalizationSelect.querySelector('option[value]:not([value=\"\"])');
                if (firstOption) {
                    legalizationSelect.value = firstOption.value;
                    legalizationSelect.dispatchEvent(new Event('change'));
                }
            }
        } else {
            afterBlock.style.display = 'none';
            if (directionBlock) directionBlock.style.display = 'none';
            if (directionSelect) directionSelect.value = '';
            this.hideAddons('direction');
            if (legalizationContainer) legalizationContainer.style.display = 'none';
            if (legalizationSelect) legalizationSelect.value = '';
            if (consulateCheckbox) consulateCheckbox.checked = false;
        }

        // ✅ totals qayta hisob
        w.querySelector('.discount')?.dispatchEvent(new Event('input'));
    };

    // ✅ doc-type tanlanmaguncha tugmalar ko‘rinmasin
    const toggleProcessVisibility = () => {
        const hasDoc = !!docType?.value;
        processWrapper.style.display = hasDoc ? 'block' : 'none';

        if (!hasDoc) {
            modeInput.value = '';
            setActiveBtn('__none__');
            hideAll();
            resetAll();
        }
    };

    docType?.addEventListener('change', () => {
        toggleProcessVisibility();
    });

    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.mode;

            resetAll();              // eski tanlov tozalanadi
            modeInput.value = mode;  // yangi mode
            setActiveBtn(mode);
            showMode(mode);
            checkReady();
        });
    });

    w.querySelectorAll('.apostil-g1, .apostil-g2, .consul-main').forEach(r => {
        r.addEventListener('change', checkReady);
    });


    legalizationSelect?.addEventListener('change', () => {
        if (consulateCheckbox) {
            const price = parseFloat(legalizationSelect.selectedOptions[0]?.dataset.price || 0);
            consulateCheckbox.dataset.price = price;
            consulateCheckbox.checked = !!legalizationSelect.value && modeInput.value === 'consul';
        }
        checkReady();
        w.querySelector('.discount')?.dispatchEvent(new Event('input'));
    });

    // start state
    toggleProcessVisibility();
    hideAll();
}

    initFileHandlers() {
        const fileInput = this.element.querySelector('.file-input');
        const addBtn = this.element.querySelector('.btn-add-more-files');

        this._files = Array.from(fileInput.files || []);

        addBtn.onclick = () => fileInput.click();
        fileInput.onchange = () => {
            const newFiles = Array.from(fileInput.files || []);
            if (!newFiles.length) return;

            if (typeof DataTransfer === 'undefined') {
                this.updateFileList();
                return;
            }

            const dt = new DataTransfer();
            const seen = new Set();
            const addUnique = (f) => {
                const key = `${f.name}_${f.size}_${f.lastModified}`;
                if (seen.has(key)) return;
                seen.add(key);
                dt.items.add(f);
            };

            (this._files || []).forEach(addUnique);
            newFiles.forEach(addUnique);

            fileInput.files = dt.files;
            this.updateFileList();
        };

        this.updateFileList();
    }

    updateFileList() {
        const fileInput = this.element.querySelector('.file-input');
        const container = this.element.querySelector('.files-container');
        const noFiles = this.element.querySelector('.no-files-message');
        const files = Array.from(fileInput.files || []);
        this._files = files;

        container.innerHTML = '';
        noFiles.style.display = files.length ? 'none' : 'block';

        let totalSize = 0;
        files.forEach((file, i) => {
            totalSize += file.size;
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${this.formatSize(file.size)}</div>
                </div>
                <button type="button" class="file-remove" data-index="${i}">x</button>
            `;
            container.appendChild(item);
        });

        container.querySelectorAll('.file-remove').forEach(btn => {
            btn.onclick = () => this.removeFile(parseInt(btn.dataset.index));
        });

        this.element.querySelector('.file-count').textContent = `Fayllar: ${files.length}`;
        this.element.querySelector('.total-size').textContent = `Hajm: ${this.formatSize(totalSize)}`;
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
    const w = this.wrapper;

    const serviceSelect = w.querySelector('.service');
    const discountInput = w.querySelector('.discount');
    const legalizationSelect = w.querySelector('.legalization');
    const totalAmount   = w.querySelector('.total-amount');
    const finalAmount   = w.querySelector('.final-amount');

    const getMode = () => w.querySelector('.process-mode')?.value || '';

    const calculate = () => {
        const servicePrice = parseFloat(serviceSelect?.selectedOptions?.[0]?.dataset?.price || 0);
        const addonsTotal  = this.getAddonsTotal();

        // ✅ Apostil/Konsullik narxlari
        const mode = getMode();

        let apostilPrice = 0;
        if (mode === 'apostil') {
            apostilPrice += parseFloat(w.querySelector('.apostil-g1:checked')?.dataset.price || 0);
            apostilPrice += parseFloat(w.querySelector('.apostil-g2:checked')?.dataset.price || 0);
        }

        let consulRadioPrice = 0;
        if (mode === 'consul') {
            consulRadioPrice += parseFloat(w.querySelector('.consul-main:checked')?.dataset.price || 0);
        }

        let legalizationPrice = 0;
        if (mode === 'consul') {
            legalizationPrice += parseFloat(legalizationSelect?.selectedOptions?.[0]?.dataset.price || 0);
        }

        const totalBeforeDiscount = servicePrice + addonsTotal + apostilPrice + consulRadioPrice + legalizationPrice;

        const discountAmount = parseFloat(discountInput?.value || 0);
        const final = totalBeforeDiscount - discountAmount;

        if (totalAmount) totalAmount.value = totalBeforeDiscount.toLocaleString();
        if (finalAmount) finalAmount.value = final.toLocaleString();

        updateGlobalTotals();
    };

    serviceSelect?.addEventListener('change', calculate);
    discountInput?.addEventListener('input', calculate);

    // radio / tugma o'zgarsa ham qayta hisoblash
    w.querySelectorAll('.apostil-g1, .apostil-g2, .consul-main, .btn-process')
        .forEach(el => el.addEventListener('change', calculate));

    calculate();
}


    getAddonsTotal() {
        // Ikkala konteynerdagi addonlarni jamlaymiz
        let total = 0;

        // Hujjat addonlari
        const documentCheckboxes = this.wrapper.querySelectorAll('.service-addon-checkbox-document:checked');
        documentCheckboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });

        // Xizmat addonlari
        const directionCheckboxes = this.wrapper.querySelectorAll('.service-addon-checkbox-direction:checked');
        directionCheckboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });

        const serviceCheckboxes = this.wrapper.querySelectorAll('.service-addon-checkbox-service:checked');
        serviceCheckboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });

        return total;
    }

getTotals() {
    const w = this.wrapper;

    const serviceSelect = w.querySelector('.service');
    const discountInput = w.querySelector('.discount');
    const legalizationSelect = w.querySelector('.legalization');

    const servicePrice = parseFloat(serviceSelect?.selectedOptions?.[0]?.dataset?.price || 0);
    const addonsAmount = this.getAddonsTotal();

    const mode = w.querySelector('.process-mode')?.value || '';

    let apostilPrice = 0;
    if (mode === 'apostil') {
        apostilPrice += parseFloat(w.querySelector('.apostil-g1:checked')?.dataset.price || 0);
        apostilPrice += parseFloat(w.querySelector('.apostil-g2:checked')?.dataset.price || 0);
    }

    let consulPrice = 0;
    if (mode === 'consul') {
        consulPrice += parseFloat(w.querySelector('.consul-main:checked')?.dataset.price || 0);
    }

    let legalizationPrice = 0;
    if (mode === 'consul') {
        legalizationPrice += parseFloat(legalizationSelect?.selectedOptions?.[0]?.dataset.price || 0);
    }

    const totalBeforeDiscount = servicePrice + addonsAmount + apostilPrice + consulPrice + legalizationPrice;
    const discountAmount = parseFloat(discountInput?.value || 0);
    const finalAmount = totalBeforeDiscount - discountAmount;

    return {
        serviceAmount: servicePrice,
        addonsAmount: addonsAmount,
        apostilAmount: apostilPrice,
        consulAmount: consulPrice,
        legalizationAmount: legalizationPrice,
        totalAmount: totalBeforeDiscount,
        discountAmount: discountAmount,
        finalAmount: finalAmount
    };
}

    updateConfirm() {
        const getData = sel => this.wrapper.querySelector(sel);
        const confirmInfo = this.element.querySelector('.confirm-info');

        // Barcha ma'lumotlarni olamiz
        const docType = getData('.doc-type').selectedOptions[0]?.text || 'Tanlanmagan';
        const directionType = getData('.direction-type').selectedOptions[0]?.text || 'Tanlanmagan';
        const consulateChecked = getData('.consulate-checkbox').checked;
        const legalization = getData('.legalization').selectedOptions[0]?.text || 'Tanlanmagan';
        const service = getData('.service').selectedOptions[0]?.text || 'Tanlanmagan';
        const finalAmount = getData('.final-amount').value || '0';
        const paymentType = getData('.payment-type').selectedOptions[0]?.text || 'Tanlanmagan';
        const paymentAmount = getData('.payment-amount').value || '0';

        // Narx tafsilotlarini olamiz
        const totals = this.getTotals();

        // Tanlangan addonlarni olamiz
        const selectedAddons = this.getSelectedAddons();
        let addonsHtml = '';
        if (selectedAddons.length > 0) {
            addonsHtml = `<div class="mt-2"><strong>Qo'shimcha xizmatlar:</strong><ul class="mb-0">`;
            selectedAddons.forEach(addon => {
                addonsHtml += `<li>${addon.name} - ${parseFloat(addon.price).toLocaleString()} so'm (${addon.type})</li>`;
            });
            addonsHtml += '</ul></div>';
        }

        confirmInfo.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Hujjat ma'lumoti</h6>
                    <p class="mb-1"><strong>Hujjat turi:</strong> ${docType}</p>
                    <p class="mb-1"><strong>Apostil:</strong> ${directionType}</p>
                    <p class="mb-1"><strong>Konsullik:</strong> ${consulateChecked ? "Ha" : "Yo'q"}</p>
                    ${consulateChecked && legalization !== 'Tanlanmagan' ? `<p class="mb-1"><strong>Legallashtirish:</strong> ${legalization}</p>` : ''}

                    <hr>

                    <h6 class="card-subtitle mb-3 text-muted">Xizmat ma'lumoti</h6>
                    <p class="mb-1"><strong>Xizmat:</strong> ${service}</p>

                    ${addonsHtml}

                    <hr>

                    <h6 class="card-subtitle mb-3 text-muted">Narx tafsilotlari</h6>
                    <p class="mb-1"><strong>Xizmat narxi:</strong> ${totals.serviceAmount.toLocaleString()} so'm</p>
                    ${totals.consulateAmount > 0 ? `<p class="mb-1"><strong>Konsullik:</strong> ${totals.consulateAmount.toLocaleString()} so'm</p>` : ''}
                    ${totals.legalizationAmount > 0 ? `<p class="mb-1"><strong>Legallashtirish:</strong> ${totals.legalizationAmount.toLocaleString()} so'm</p>` : ''}
                    ${totals.addonsAmount > 0 ? `<p class="mb-1"><strong>Qo'shimcha xizmatlar:</strong> ${totals.addonsAmount.toLocaleString()} so'm</p>` : ''}
                    <p class="mb-1"><strong>Umumiy summa:</strong> ${totals.totalAmount.toLocaleString()} so'm</p>
                    ${totals.discountAmount > 0 ? `<p class="mb-1 text-danger"><strong>Chegirma:</strong> -${totals.discountAmount.toLocaleString()} so'm</p>` : ''}
                    <p class="mb-1 text-success"><strong>Yakuniy narx:</strong> ${totals.finalAmount.toLocaleString()} so'm</p>

                    <hr>

                    <h6 class="card-subtitle mb-3 text-muted">To'lov</h6>
                    <p class="mb-1"><strong>To'lov turi:</strong> ${paymentType}</p>
                    <p class="mb-1"><strong>To'lov summasi:</strong> ${parseFloat(paymentAmount || 0).toLocaleString()} so'm</p>
                    ${parseFloat(paymentAmount) < totals.finalAmount ? `<p class="mb-1 text-warning"><strong>Qoldiq:</strong> ${(totals.finalAmount - parseFloat(paymentAmount)).toLocaleString()} so'm</p>` : ''}

                    <hr>

                    <p class="mb-0"><strong>Yuklangan fayllar:</strong> ${getData('.file-input').files.length}</p>
                </div>
            </div>
        `;
    }

    attachRemoveHandler() {
        this.wrapper.querySelector('.btn-remove-wizard').onclick = () => {
            if (confirm("Ushbu wizardni o'chirasizmi?")) {
                this.wrapper.remove();
                updateGlobalTotals(); // Global summalarni yangilaymiz
            }
        };
    }

   initAddonTracking() {
    const w = this.wrapper;
    const serviceSelect = w.querySelector('.service');

    const docType = w.querySelector('.doc-type');
    const afterBlock = w.querySelector('.after-choice-block'); // faqat tanlov tugaganda ko'rinadi
    const directionSelect = w.querySelector('.direction-type');

    const loadDocumentAddons = async () => {
        // afterBlock yopiq bo'lsa ? addon ham kerak emas
        if (afterBlock.style.display === 'none') {
            this.hideAddons('document');
            return;
        }

        const docTypeVal = docType?.value || '';
        if (!docTypeVal) {
            this.hideAddons('document');
            return;
        }

        const container = w.querySelector('.additional-services-document');
        const servicesList = container.querySelector('.services-list-document');

        servicesList.innerHTML =
            '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Yuklanmoqda...</div>';
        container.style.display = 'block';

        try {
            const addons = await this.fetchAddonsByType('document', docTypeVal);
            const addonsWithType = addons.map(a => Object.assign({}, a, { sourceType: 'document' }));
            this.renderAddons(addonsWithType, 'document');
        } catch (e) {
            console.error(e);
            servicesList.innerHTML = "<div class=\"text-danger small\">Ma'lumotlarni yuklashda xatolik</div>";
        }
    };

    docType?.addEventListener('change', loadDocumentAddons);

    const loadDirectionAddons = async () => {
        // afterBlock yopiq bo'lsa ? addon ham kerak emas
        if (afterBlock.style.display === 'none') {
            this.hideAddons('direction');
            return;
        }

        const directionVal = directionSelect?.value || '';
        if (!directionVal) {
            this.hideAddons('direction');
            return;
        }

        const container = w.querySelector('.additional-services-direction');
        const servicesList = container.querySelector('.services-list-direction');

        servicesList.innerHTML =
            '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Yuklanmoqda...</div>';
        container.style.display = 'block';

        try {
            const addons = await this.fetchAddonsByType('direction', directionVal);
            const addonsWithType = addons.map(a => Object.assign({}, a, { sourceType: 'direction' }));
            this.renderAddons(addonsWithType, 'direction');
        } catch (e) {
            console.error(e);
            servicesList.innerHTML = '<div class="text-danger small">Ma\'lumotlarni yuklashda xatolik</div>';
        }
    };

    directionSelect?.addEventListener('change', () => {
        const mode = w.querySelector('.process-mode')?.value || '';
        if (mode === 'apostil') {
            const ok = !!w.querySelector('.apostil-g1:checked') && !!w.querySelector('.apostil-g2:checked');
            const hasDirection = !!directionSelect?.value;
            afterBlock.style.display = ok && hasDirection ? 'block' : 'none';
        }

        loadDirectionAddons();
        loadDocumentAddons();
    });

    const loadServiceAddons = async () => {
        const serviceVal = serviceSelect?.value || '';
        if (!serviceVal) {
            this.hideAddons('service');
            return;
        }

        const container = w.querySelector('.additional-services-service');
        const servicesList = container.querySelector('.services-list-service');

         servicesList.innerHTML =
            '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Yuklanmoqda...</div>';
        container.style.display = 'block';

        try {
            const addons = await this.fetchAddonsByType('service', serviceVal);
            const addonsWithType = addons.map(a => Object.assign({}, a, { sourceType: 'service' }));
            this.renderAddons(addonsWithType, 'service');
        } catch (e) {
            console.error(e);
            servicesList.innerHTML = '<div class="text-danger small">Ma\'lumotlarni yuklashda xatolik</div>';
        }
    };

    serviceSelect?.addEventListener('change', loadServiceAddons);

    // ✅ afterBlock ochilganda ham addon yuklansin
    // (tanlov tugaganda initPriceCalculation input trigger qiladi; shu yerda ham hook)
    w.addEventListener('change', (e) => {
        if (e.target.classList.contains('apostil-g1') ||
            e.target.classList.contains('apostil-g2') ||
            e.target.classList.contains('consul-main') ||
            e.target.classList.contains('legalization')) {
            loadDocumentAddons();
        }
    });

    loadServiceAddons();
}

    async fetchAddonsByType(type, id) {
        try {
            const response = await fetch(`${apiBase}/get-addons/${type}/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) throw new Error(`Yuklashda xato: ${type}`);

            const data = await response.json();
            return Array.isArray(data) ? data : [];
        } catch (error) {
            console.error(`Yuklashda xato: ${type}:`, error);
            return [];
        }
    }

    renderAddons(addons, containerType) {
        const suffix = '-' + containerType;
        const container = this.wrapper.querySelector(`.additional-services${suffix}`);
        const servicesList = container.querySelector(`.services-list${suffix}`);

        if (!addons || addons.length === 0) {
            this.hideAddons(containerType);
            return;
        }

        let html = '';

        addons.forEach((addon, index) => {
            const uniqueId = `addon-${containerType}-${Date.now()}-${index}`;
            const sourceType = addon.sourceType || containerType; // sourceType bo'lsa ishlatamiz

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
                    <div class="service-addon-price">${parseFloat(addon.amount).toLocaleString()} so'm</div>
                </div>
            `;
        });

        servicesList.innerHTML = html;
        container.style.display = 'block';

        // Checkboxlar uchun handler qo'shamiz
        servicesList.querySelectorAll('.service-addon-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateAddonTotal(containerType));
        });

        // Butun elementga bosish handlerlarini qo'shamiz
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
        const suffix = '-' + containerType;
        const container = this.wrapper.querySelector(`.additional-services${suffix}`);
        const checkboxes = container.querySelectorAll(`.service-addon-checkbox-${containerType}:checked`);
        const totalSpan = container.querySelector(`.addon-total${suffix}`);
        const countSpan = container.querySelector(`.selected-count${suffix}`);

        let total = 0;
        checkboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
            cb.closest('.service-addon-item').classList.add('selected');
        });

        // Tanlanmaganlardan selected klassini olib tashlaymiz
        container.querySelectorAll(`.service-addon-checkbox-${containerType}:not(:checked)`).forEach(cb => {
            cb.closest('.service-addon-item').classList.remove('selected');
        });

        totalSpan.textContent = total.toLocaleString();
        countSpan.textContent = checkboxes.length;

        // Buyurtma umumiy summasini qayta hisoblaymiz
        const serviceSelect = this.wrapper.querySelector('.service');
        if (serviceSelect.value) {
            const discountInput = this.wrapper.querySelector('.discount');
            discountInput.dispatchEvent(new Event('input'));
        }

        updateGlobalTotals();
    }

    getSelectedAddons() {
        const selected = [];

        // Ikkala konteynerdan addonlarni yig'amiz
        this.wrapper.querySelectorAll('.service-addon-checkbox:checked').forEach(cb => {
            selected.push({
                id: cb.dataset.id,
                name: cb.dataset.name,
                price: cb.dataset.price,
                type: cb.dataset.container, // 'document' yoki 'service'
                sourceType: cb.dataset.sourceType // 'document', 'direction', 'consulate', 'service'
            });
        });

        return selected;
    }

    hideAddons(containerType) {
        const suffix = '-' + containerType;
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
                // Yopilganda select qiymatini tozalaymiz
                legalizationSelect.value = '';

                // Addonlarni qayta yuklaymiz
                const docTypeSelect = this.wrapper.querySelector('.doc-type');
                if (docTypeSelect) {
                    docTypeSelect.dispatchEvent(new Event('change'));
                }

                // Narxlarni qayta hisoblaymiz (legallashtirish tozalangani uchun)
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

    // Modal oynasi ochilganda formani tozalash
    modal.addEventListener('show.bs.modal', function() {
        form.reset();
        clearValidationErrors();
    });

    // Saqlash tugmasi handleri
    saveBtn.addEventListener('click', async function() {
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        await createClient();
    });

    // Mijoz yaratish funksiyasi
    async function createClient() {
        // Tugmani o'chirib spinnerni ko'rsatamiz
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        // Forma ma'lumotlarini yig'amiz
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`${apiBase}/clients`, {
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
                // Validatsiya xatolarini qayta ishlash
                if (result.errors) {
                    displayValidationErrors(result.errors);
                } else {
                    throw new Error(result.message || 'Mijoz yaratishda xato');
                }
                return;
            }

            // Muvaffaqiyatli yaratildi
            showSuccessMessage('Mijoz muvaffaqiyatli yaratildi!');

            // Yangi mijozni Select2ga qo'shamiz
            // if (window.$ && $('#client_id').length) {
            //     const newOption = new Option(
            //         `${result.data.name} (${result.data.phone_number})`,
            //         result.data.id,
            //         true,
            //         true
            //     );
            //     $('#client_id').append(newOption).trigger('change');
            // }

            // Modal oynani yopamiz
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            bootstrapModal.hide();

        } catch (error) {
            console.error('Error:', error);
            showErrorMessage(error.message || 'Mijoz yaratishda xatolik yuz berdi');
        } finally {
            // Tugmani yoqib spinnerni yashiramiz
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    // Validatsiya xatolarini ko'rsatish funksiyasi
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

    // Validatsiya xatolarini tozalash funksiyasi
    function clearValidationErrors() {
        form.classList.remove('was-validated');
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }

    // Muvaffaqiyat xabarini ko'rsatish funksiyasi
    function showSuccessMessage(message) {
        // Bootstrap alert yoki toast ishlatamiz
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
                 role="alert" style="z-index: 9999;">
                <i class="bi bi-check-circle-fill me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // 3 soniyadan keyin avtomatik olib tashlaymiz
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }

    // Xato xabarini ko'rsatish funksiyasi
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
