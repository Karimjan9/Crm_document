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

        .modal-addition {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            /* yashirin */
            z-index: 1100;
            /* delete modaldan yuqori */
        }

        .modal-addition.active {
            display: block;
        }

        .modal-addition {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            z-index: 1100;
            background: rgba(0, 0, 0, 0.0);
            /* fon qorayishi yo‘q */
        }

        .modal-addition.active {
            display: block;
        }

        .modal-content-custom {
            background: #fff;
            padding: 30px 40px;
            border-radius: 14px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.25);
            text-align: left;
            animation: fadeInScale 0.3s ease forwards;
        }

        .modal-content-custom h5 {
            margin-bottom: 20px;
            font-weight: 600px;
            color: var(--blue-main);
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--blue-light);
            outline: none;
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.2);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .modal-actions button {
            min-width: 100px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-left: 10px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-actions .btn-cancel {
            background: #6c757d;
            color: #fff;
        }

        .modal-actions .btn-cancel:hover {
            background: #5a6268;
        }

        .modal-actions .btn-confirm {
            background: var(--blue-main);
            color: #fff;
        }

        .modal-actions .btn-confirm:hover {
            background: var(--blue-light);
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

            <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
                <div class="breadcrumb-title pe-3">Hujjat turi</div>
                <a href="{{ route('superadmin.document_type.create') }}" class="btn btn-custom">+ Yangi Hujjat turi</a>
            </div>

            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 text-uppercase">Hujjat turi bazasi</h6>
            </div>
            <hr>

            <div class="card radius-10">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="mytable" class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="fixed_header2 align-middle">#</th>
                                    <th class="fixed_header2 align-middle">Hujjat turi nomi</th>

                                    <th class="fixed_header2 align-middle">Hujjat turi izoh</th>
                                    <th class="fixed_header2 align-middle">Qo‘shimcha</th>
                                    <th class="fixed_header2 align-middle">Harakatlar</th>
                                </tr>
                            </thead>
                            <tbody id="data_list">
                                @foreach ($documentTypes as $key => $documentType)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $documentType->name }}</td>
                                        <td>{{ $documentType->description }}</td>
                                        <td>
                                            <button class="btn btn-info btn-addition" data-id="{{ $documentType->id }}"
                                                data-name="{{ $documentType->name }}">
                                                Qo‘shish
                                            </button>
                                        </td>
                                        <td>
                                            <a class="btn btn-warning"
                                                href="{{ route('superadmin.document_type.edit', ['document_type' => $documentType->id]) }}">O'zgartirish</a>

                                            <!-- Delete Form -->
                                            <form
                                                action="{{ route('superadmin.document_type.destroy', ['document_type' => $documentType->id]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-delete"
                                                    data-name="{{ $documentType->name }}">O'chirish</button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- Additions --}}
                                    @if($documentType->additions->count() > 0)
                                        @foreach($documentType->additions as $addition)
                                            <tr class="addition-row" style="background-color: #f9f9f9;">
                                                <td></td>
                                                <td colspan="2">
                                                    <strong>{{ $addition->name }}</strong><br>
                                                    <small>{{ $addition->description }}</small>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);" class="btn btn-warning btn-sm btn-edit-addition"
                                                        data-id="{{ $addition->id }}" data-name="{{ $addition->name }}"
                                                        data-description="{{ $addition->description }}">
                                                        O'zgartirish
                                                    </a>
                                                    <form
                                                        action="{{ route('superadmin.addition.destroy', ['addition' => $addition->id]) }}"
                                                        method="POST" class="d-inline delete-addition-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm btn-delete-addition"
                                                            data-name="{{ $addition->name }}">O'chirish</button>
                                                    </form>
                                                </td>
                                                <td></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="addition-row" style="background-color: #f9f9f9;">
                                            <td></td>
                                            <td colspan="4" class="text-center">Qo‘shimchalar mavjud emas</td>
                                        </tr>
                                    @endif
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

    <div class="modal-addition" id="additionModal">
        <div class="modal-content-custom">
            <h5>Qo‘shimcha qo‘shish</h5>
            <form id="additionForm" method="POST" action="{{ route('superadmin.store_addition') }}">
                @csrf
                <input type="hidden" name="document_type_id" id="documentTypeId">

                <div class="form-group">
                    <label for="additionName">Qo‘shimcha nomi</label>
                    <input type="text" name="name" id="additionName" required placeholder="Nomini kiriting">
                </div>

                <div class="form-group">
                    <label for="additionDescription">Izoh</label>
                    <textarea name="description" id="additionDescription" rows="3" required
                        placeholder="Izoh yozing"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelAdditionBtn">Bekor qilish</button>
                    <button type="submit" class="btn-confirm">Saqlash</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Edit Addition Modal -->
    <div class="modal-addition" id="editAdditionModal">
        <div class="modal-content-custom">
            <h5>Qo‘shimchani o'zgartirish</h5>
            <form id="editAdditionForm" method="POST" action="">
                @csrf
                @method('PUT') <!-- update uchun -->

                <input type="hidden" name="addition_id" id="editAdditionId">

                <div class="form-group">
                    <label for="editAdditionName">Qo‘shimcha nomi</label>
                    <input type="text" name="name" id="editAdditionName" required>
                </div>

                <div class="form-group">
                    <label for="editAdditionDescription">Izoh</label>
                    <textarea name="description" id="editAdditionDescription" rows="3" required></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelEditAdditionBtn">Bekor qilish</button>
                    <button type="submit" class="btn-confirm">Saqlash</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script_include_end_body')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Delete modal
            const deleteModal = document.getElementById('deleteModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');
            let currentForm = null;

            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function () {
                    const name = this.getAttribute('data-name');
                    document.getElementById('modal-text').textContent = `"${name}" filialini o'chirishni tasdiqlaysizmi?`;
                    deleteModal.classList.add('active');
                    currentForm = this.closest('form');
                });
            });

            cancelBtn.addEventListener('click', function () {
                deleteModal.classList.remove('active');
                currentForm = null;
            });

            confirmBtn.addEventListener('click', function () {
                if (currentForm) currentForm.submit();
            });

            // Addition modal
            const additionModal = document.getElementById('additionModal');
            const cancelAdditionBtn = document.getElementById('cancelAdditionBtn');

            document.querySelectorAll('.btn-addition').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    document.getElementById('documentTypeId').value = id;
                    document.getElementById('additionName').value = '';
                    document.getElementById('additionDescription').value = '';
                    additionModal.classList.add('active');
                });
            });

            cancelAdditionBtn.addEventListener('click', function () {
                additionModal.classList.remove('active');
            });

            // ESC tugmasi bilan yopish
            window.addEventListener('keydown', function (e) {
                if (e.key === "Escape") {
                    deleteModal.classList.remove('active');
                    additionModal.classList.remove('active');
                }
            });
        });

        document.querySelectorAll('.btn-delete-addition').forEach(button => {
            button.addEventListener('click', function () {
                const name = this.getAttribute('data-name');
                document.getElementById('modal-text').textContent = `"${name}" qo‘shimchani o'chirishni tasdiqlaysizmi?`;
                deleteModal.classList.add('active');
                currentForm = this.closest('form');
            });
        });
        const editAdditionModal = document.getElementById('editAdditionModal');
        const cancelEditAdditionBtn = document.getElementById('cancelEditAdditionBtn');
        const editAdditionForm = document.getElementById('editAdditionForm');

        document.querySelectorAll('.btn-edit-addition').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');

                // Form maydonlarini to'ldirish
                document.getElementById('editAdditionId').value = id;
                document.getElementById('editAdditionName').value = name;
                document.getElementById('editAdditionDescription').value = description;

                // Form actionni dinamik o'rnatish (Laravel route)
                editAdditionForm.action = `/superadmin/addition/${id}`; // update route

                editAdditionModal.classList.add('active');
            });
        });

        cancelEditAdditionBtn.addEventListener('click', function () {
            editAdditionModal.classList.remove('active');
        });

        // ESC tugmasi bilan yopish
        window.addEventListener('keydown', function (e) {
            if (e.key === "Escape") {
                editAdditionModal.classList.remove('active');
            }
        });

    </script>
@endsection