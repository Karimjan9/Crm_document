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

        .page-wrapper { padding: 24px; }

        .page-breadcrumb {
            background: var(--white);
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
        }

        .breadcrumb-title { font-weight: 600; color: var(--text-color); }

        .card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .card:hover { box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15); }

        .card-body { padding: 25px; }

        h6 { color: var(--blue-main); font-weight: 600; letter-spacing: 0.5px; }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            font-size: 14px;
        }

        thead { background: var(--blue-main); color: var(--white); }

        th, td { text-align: center; vertical-align: middle; padding: 12px 8px; }

        tbody tr { background-color: var(--white); transition: background 0.25s ease; }
        tbody tr:hover { background-color: #e0edff; }

        .fixed_header2 { position: sticky; top: 0; background: var(--blue-main); color: white; z-index: 10; }

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

        .btn-danger:hover { background: var(--danger-hover); transform: translateY(-1px); }

        /* Modal styles */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-backdrop.active { display: flex; }

        .modal-content-custom {
            background: var(--white);
            padding: 20px 25px;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 6px 18px rgba(30,58,138,0.25);
            text-align: center;
            animation: fadeInScale 0.3s ease forwards;
        }

        .modal-content-custom h5 { margin-bottom: 15px; font-weight: 600; color: var(--text-color); }
        .modal-content-custom p { font-size: 14px; color: #555; margin-bottom: 20px; }

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

        .modal-actions .btn-cancel { background: #6c757d; color: white; }
        .modal-actions .btn-cancel:hover { background: #5a6268; transform: translateY(-1px); }

        .modal-actions .btn-confirm { background: var(--danger-color); color: white; }
        .modal-actions .btn-confirm:hover { background: var(--danger-hover); transform: translateY(-1px); }

        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1); }
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

            <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
                <div class="breadcrumb-title pe-3">Filiallar</div>
                <a href="{{ route('superadmin.sms_message_text.create') }}" class="btn btn-custom">+ Yangi Filial</a>
            </div>

            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 text-uppercase">Filiallar bazasi</h6>
            </div>
            <hr>

            <div class="card radius-10">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="mytable" class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="fixed_header2 align-middle">#</th>
                                    <th class="fixed_header2 align-middle">SMS nomi</th>
                                    <th class="fixed_header2 align-middle">SMS text 1</th>
                            
                                    <th class="fixed_header2 align-middle">SMS text 2</th>
                                    <th class="fixed_header2 align-middle">SMS text 3</th>
                                    <th class="fixed_header2 align-middle">SMS type</th>
                                    <th class="fixed_header2 align-middle">Ko'rish</th>
                                    
                                    <th class="fixed_header2 align-middle">Harakatlar</th>
                                </tr>
                            </thead>
                            <tbody id="data_list">
                                @foreach ($smsMessages as $key=>$smsMessage)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $smsMessage->name }}</td>
                                    <td>{{ $smsMessage->message_text1 }}</td>
                                    <td>{{ $smsMessage->message_text2 }}</td>
                                    <td>{{ $smsMessage->message_text3 }}</td>
                                    <td>{{ $smsMessage->type }}</td>
                                <td>
                               <button type="button"
                                    class="btn btn-info btn-view"
                                    data-text1="{{ $smsMessage->message_text1 }}"
                                    data-text2="{{ $smsMessage->message_text2 }}"
                                    data-text3="{{ $smsMessage->message_text3 }}">
                                Ko'rish
                            </button>

                            </td>
                                    <td>
                                        <a class="btn btn-warning" href="{{ route('superadmin.sms_message_text.edit',['sms_message_text'=>$smsMessage->id]) }}">O'zgartirish</a>

                                        <!-- Delete Form -->
                                        <form action="{{ route('superadmin.sms_message_text.destroy',['sms_message_text'=>$smsMessage->id]) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-delete" data-name="{{ $smsMessage->name }}">O'chirish</button>
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
<div class="modal-backdrop" id="viewModal">
    <div class="modal-content-custom">
        <h5>SMS ma'lumotlari</h5>
        <p id="view-modal-text"></p>
        <div class="modal-actions">
            <button class="btn-cancel" id="viewCloseBtn">Yopish</button>
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

    @endsection

    @section('script_include_end_body')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Document ready');
        const deleteModal = document.getElementById('deleteModal');
        const modalText = document.getElementById('modal-text');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmBtn = document.getElementById('confirmBtn');

        let currentForm = null;

        // DELETE TUGMA BOSILSA
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function () {
                const name = this.getAttribute('data-name') || 'item';
                const form = this.closest('form.delete-form');
                currentForm = form;
                modalText.textContent = `“${name}” ni o‘chirishni tasdiqlaysizmi? Bu amalni qaytarib bo‘lmaydi.`;
                deleteModal.classList.add('active');
            });
        });

        // MODALNI BEKOR QILISH
        cancelBtn.addEventListener('click', () => {
            deleteModal.classList.remove('active');
            currentForm = null;
        });

        // BACKDROP BOSILSA YOPILSIN
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('active');
                currentForm = null;
            }
        });

        // CONFIRM BOSILSA FORM SUBMIT
        confirmBtn.addEventListener('click', () => {
            if (currentForm) {
                currentForm.submit();
            }
        });

        // ESC TUGMASI BOSILSA YOPILSIN
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && deleteModal.classList.contains('active')) {
                deleteModal.classList.remove('active');
                currentForm = null;
            }
        });

    });
    </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
        const viewModal = document.getElementById('viewModal');
        const viewModalText = document.getElementById('view-modal-text');
        const viewCloseBtn = document.getElementById('viewCloseBtn');

        // Ko‘rish tugmasi bosilganda modal ochish
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', () => {
                // data attributesdan olamiz
                const text1 = btn.getAttribute('data-text1') || '';
                const text2 = btn.getAttribute('data-text2') || '';
                const text3 = btn.getAttribute('data-text3') || '';
                const combinedText = `${text1} Aliyev Ali Aliyevich ${text2} BUX01-1000001 ${text3}`;
                viewModalText.textContent = combinedText;
                viewModal.classList.add('active');
            });
        });

        // Modalni yopish tugmasi
        viewCloseBtn.addEventListener('click', () => {
            viewModal.classList.remove('active');
        });

        // Backdrop bosilganda modalni yopish
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal) {
                viewModal.classList.remove('active');
            }
        });

        // ESC tugmasi bosilganda modalni yopish
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && viewModal.classList.contains('active')) {
                viewModal.classList.remove('active');
            }
        });
    });
    </script>
    @endsection
