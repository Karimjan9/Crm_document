@extends('template')

@section('style')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        :root {
            --text-color: #15172a;
            --blue-main: #1e3a8a;
            --blue-light: #2563eb;
            --blue-bg: #f0f6ff;
            --white: #ffffff;
            --border-color: #e5e7eb;
            --danger-color: #dc3545;
            --danger-hover: #b52b3a;
        }

        body {
            font-family: "Inter", "ui-sans-serif", "system-ui", "-apple-system", "Segoe UI", "Roboto", "Helvetica Neue", "Arial", "Noto Sans", "sans-serif";
            background: var(--blue-bg);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .page-wrapper {
            padding: 24px;
        }

        .page-breadcrumb {
            background: var(--white);
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
        }

        .breadcrumb-title {
            font-weight: 600;
            color: var(--text-color);
        }

        .card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .card:hover {
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15);
        }

        .card-body {
            padding: 25px;
        }

        h6 {
            color: var(--blue-main);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            font-size: 14px;
        }

        thead {
            background: var(--blue-main);
            color: var(--white);
        }

        th,
        td {
            text-align: center;
            vertical-align: middle;
            padding: 12px 8px;
        }

        tbody tr {
            background-color: var(--white);
            transition: background 0.25s ease;
        }

        tbody tr:hover {
            background-color: #e0edff;
        }

        .fixed_header2 {
            position: sticky;
            top: 0;
            background: var(--blue-main);
            color: white;
            z-index: 10;
        }

        /* Custom button */
        .btn-custom {
            background: var(--blue-light);
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background: var(--blue-main);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.25);
        }

        .btn-danger {
            background: var(--danger-color);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            transition: 0.2s;
        }

        .btn-danger:hover {
            background: var(--danger-hover);
            transform: translateY(-1px);
        }

        /* Modal styles */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal-content-custom {
            background: var(--white);
            padding: 20px 25px;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 6px 18px rgba(30, 58, 138, 0.25);
            text-align: center;
            animation: fadeInScale 0.3s ease forwards;
        }

        .modal-content-custom h5 {
            margin-bottom: 15px;
            font-weight: 600;
            color: var(--text-color);
        }

        .modal-content-custom p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .modal-actions button {
            min-width: 100px;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            margin: 0 5px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-actions .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .modal-actions .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .modal-actions .btn-confirm {
            background: var(--danger-color);
            color: white;
        }

        .modal-actions .btn-confirm:hover {
            background: var(--danger-hover);
            transform: translateY(-1px);
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
@endsection

@section('body')
    <div class="page-wrapper">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                    <strong><i class="lni lni-checkmark-circle"></i></strong>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('danger') || session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    <strong><i class="lni lni-warning"></i></strong>
                    {{ session('danger') ?? session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
                <div id="ajax-success" class="alert alert-success alert-dismissible fade" role="alert" style="display:none;">
                <strong><i class="lni lni-checkmark-circle"></i></strong>
                <span id="ajax-success-text"></span>
                <button type="button" class="btn-close"></button>
            </div>
            <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
                <div class="breadcrumb-title pe-3">Konsullik</div>

                <div class="d-flex gap-2">
                    <a href="{{ route('superadmin.consulation.static_main') }}" class="btn btn-custom">
                        ⚙️ Consullik sozlama
                    </a>

                    <a href="{{ route('superadmin.consulation.create') }}" class="btn btn-custom">
                        + Yangi Konsullik+
                    </a>
                </div>
            </div>
            
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 text-uppercase">Konsullik bazasi</h6>
            </div>
            <hr>

            <div class="card radius-10">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="mytable" class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="fixed_header2 align-middle">#</th>
                                    <th class="fixed_header2 align-middle">Konsullik nomi</th>
                                    <th class="fixed_header2 align-middle">Konsullik narxi</th>
                                    <th class="fixed_header2 align-middle">Konsullik deadline</th>
                                    <th class="fixed_header2 align-middle">Konsullik izoh</th>
                                    <th class="fixed_header2 align-middle">Harakatlar</th>
                                </tr>
                            </thead>
                            <tbody id="data_list">
                                @foreach ($consulationTypes as $key => $consulationType)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $consulationType->name }}</td>
                                        <td>{{ $consulationType->amount }}</td>
                                        <td>{{ $consulationType->day }} kun</td>
                                        <td>{{ $consulationType->description }}</td>

                                        <td>
                                            <a class="btn btn-warning"
                                                href="{{ route('superadmin.consulation.edit', ['consulation' => $consulationType->id]) }}">O'zgartirish</a>

                                            <!-- Delete Form -->
                                            <form
                                                action="{{ route('superadmin.consulation.destroy', ['consulation' => $consulationType->id]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-delete"
                                                    data-name="{{ $consulationType->name }}">O'chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-backdrop" id="deleteModal">
        <div class="modal-content-custom">
            <h5>Haqiqatan ham o'chirmoqchimisiz?</h5>
            <p id="modal-text">Bu amalni qaytarib bo'lmaydi.</p>
            <div class="modal-actions">
                <button class="btn-cancel" id="cancelBtn">Bekor qilish</button>
                <button class="btn-confirm" id="confirmBtn">O'chirish</button>
            </div>
        </div>
    </div>
    <!-- Main Consul Modal -->
    <div class="modal-backdrop" id="mainConsulModal">
        <div class="modal-content-custom" style="max-width: 520px; text-align:left;">
            <h5 style="text-align:center;">Consullik sozlama (Main)</h5>

            <div id="mainConsulError"
                style="display:none; background:#ffe5e7; color:#7a1c23; padding:10px; border-radius:8px; margin-bottom:12px; font-size:14px;">
            </div>

            <div style="display:flex; flex-direction:column; gap:10px;">
                <div>
                    <label style="font-size:13px; font-weight:600;">Nomi</label>
                    <input type="text" id="main_name" class="form-control" placeholder="Konsullik nomi">
                    <small class="text-danger" id="err_name"></small>
                </div>

                <div>
                    <label style="font-size:13px; font-weight:600;">Narxi</label>
                    <input type="number" id="main_amount" class="form-control" placeholder="Narxi">
                    <small class="text-danger" id="err_amount"></small>
                </div>

                <div>
                    <label style="font-size:13px; font-weight:600;">Deadline (kun)</label>
                    <input type="number" id="main_day" class="form-control" placeholder="Kun">
                    <small class="text-danger" id="err_day"></small>
                </div>
            </div>

            <div class="modal-actions" style="justify-content:center; margin-top:18px; display:flex;">
                <button class="btn-cancel" id="mainCancelBtn">Bekor qilish</button>
                <button class="btn-custom" id="mainSaveBtn" style="min-width:120px;">Saqlash</button>
            </div>
        </div>
    </div>
@endsection

@section('script_include_end_body')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('deleteModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');
            let currentForm = null;

            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const name = this.getAttribute('data-name');
                    document.getElementById('modal-text').textContent =
                        `"${name}" filialini o'chirishni tasdiqlaysizmi?`;
                    modal.classList.add('active');
                    currentForm = this.closest('form');
                });
            });

            cancelBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                currentForm = null;
            });

            confirmBtn.addEventListener('click', function() {
                if (currentForm) currentForm.submit();
            });

            // ESC tugmasi bilan yopish
            window.addEventListener('keydown', function(e) {
                if (e.key === "Escape") modal.classList.remove('active');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openMainBtn = document.getElementById('openMainConsulBtn');
            const mainModal = document.getElementById('mainConsulModal');
            const mainCancel = document.getElementById('mainCancelBtn');
            const mainSave = document.getElementById('mainSaveBtn');

            const errBox = document.getElementById('mainConsulError');
            const errName = document.getElementById('err_name');
            const errAmt = document.getElementById('err_amount');
            const errDay = document.getElementById('err_day');

            const inputName = document.getElementById('main_name');
            const inputAmount = document.getElementById('main_amount');
            const inputDay = document.getElementById('main_day');

            const getUrl = @json(route('superadmin.consulation.get_main_type'));
            const putUrl = @json(route('superadmin.consulation.update_main_type'));

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function clearErrors() {
                errBox.style.display = 'none';
                errBox.innerText = '';
                errName.innerText = '';
                errAmt.innerText = '';
                errDay.innerText = '';
            }

            function showBoxError(text) {
                errBox.style.display = 'block';
                errBox.innerText = text;
            }

            function openModal() {
                mainModal.classList.add('active');
            }

            function closeModal() {
                mainModal.classList.remove('active');
                clearErrors();
            }

            async function loadMainConsul() {
                clearErrors();
                inputName.value = '';
                inputAmount.value = '';
                inputDay.value = '';

                try {
                    const res = await fetch(getUrl, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('GET error: ' + res.status);

                    const data = await res.json();

                    if (!data) {
                        showBoxError("Main consullik topilmadi (bazada birinchi yozuv yo'q).");
                        return;
                    }

                    inputName.value = data.name ?? '';
                    inputAmount.value = data.amount ?? '';
                    inputDay.value = data.day ?? '';

                } catch (e) {
                    showBoxError("Ma'lumotni olishda xatolik: " + e.message);
                }
            }

            async function saveMainConsul() {
                clearErrors();

                if (!csrf) {
                    showBoxError(
                        "CSRF token topilmadi. template/layout ichida <meta name='csrf-token' ...> borligini tekshir."
                        );
                    return;
                }

                mainSave.disabled = true;

                try {
                    const payload = {
                        name: inputName.value.trim(),
                        amount: inputAmount.value,
                        day: inputDay.value
                    };

                    const res = await fetch(putUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(payload)
                    });

                    if (res.status === 422) {
                        const err = await res.json();
                        const errors = err.errors || {};

                        if (errors.name) errName.innerText = errors.name[0];
                        if (errors.amount) errAmt.innerText = errors.amount[0];
                        if (errors.day) errDay.innerText = errors.day[0];

                        return;
                    }

                    if (!res.ok) {
                        const txt = await res.text();
                        throw new Error(res.status + ' ' + txt);
                    }

                    // success
                    const result = await res.json();

                    closeModal();

                    const alertBox = document.getElementById('ajax-success');
                    const alertText = document.getElementById('ajax-success-text');

                    alertText.innerText = result.message;
                    alertBox.style.display = 'block';
                    alertBox.classList.add('show');

                    // 5 sekunddan keyin yopilsin
                    setTimeout(() => {
                        alertBox.classList.remove('show');
                        alertBox.style.display = 'none';
                    }, 5000);

                } catch (e) {
                    showBoxError("Saqlashda xatolik: " + e.message);
                } finally {
                    mainSave.disabled = false;
                }
            }

            // events
            openMainBtn?.addEventListener('click', async () => {
                openModal();
                await loadMainConsul();
            });

            mainCancel?.addEventListener('click', closeModal);
            mainSave?.addEventListener('click', saveMainConsul);

            // ESC bilan yopish
            window.addEventListener('keydown', function(e) {
                if (e.key === "Escape") {
                    if (mainModal.classList.contains('active')) closeModal();
                }
            });
        });
    </script>
@endsection
