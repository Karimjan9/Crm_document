@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    :root {
        --text-color: #1e293b;
        --blue-main: #1d4ed8;
        --blue-hover: #1e40af;
        --blue-gradient: linear-gradient(135deg, #2563eb, #1d4ed8);
        --blue-bg: #f3f6fc;
        --white: #ffffff;
        --border-color: #e2e8f0;
        --danger: #dc2626;
        --danger-bg: #fee2e2;
    }

    body {
        font-family: "Poppins", "Inter", sans-serif;
        background: var(--blue-bg);
        color: var(--text-color);
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }

    .page-wrapper { padding: 40px; }

    .page-breadcrumb {
        background: var(--white);
        border-radius: 14px;
        padding: 16px 24px;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .breadcrumb-title {
        font-weight: 600;
        font-size: 19px;
        color: var(--blue-main);
        letter-spacing: 0.9px;
    }

    .card {
        background: var(--white);
        border-radius: 18px;
        box-shadow: 0 5px 15px rgba(37, 99, 235, 0.1);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.18);
        transform: translateY(-2px);
    }

    .card-body { padding: 30px; }

    label {
        font-weight: 500;
        color: var(--blue-main);
        margin-bottom: 6px;
        display: block;
        font-size: 15px;
    }

    .form-control {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: 14px;
        background-color: #f9fafb;
        transition: all 0.25s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--blue-main);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        background-color: var(--white);
    }

    .btn-custom {
        background: var(--blue-gradient);
        border: none;
        color: var(--white);
        padding: 11px 26px;
        border-radius: 10px;
        font-weight: 500;
        font-size: 15px;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
    }

    .btn-custom:hover {
        background: linear-gradient(135deg, #0a2366, #1e40af);
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
    }

    .btn-outline {
        background: var(--white);
        color: var(--blue-main);
        border: 1px solid var(--blue-main);
        padding: 11px 24px;
        border-radius: 10px;
        font-weight: 500;
        font-size: 15px;
        transition: 0.3s;
    }

    .btn-outline:hover {
        background: var(--blue-main);
        color: var(--white);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
    }

    .text-danger {
        color: var(--danger);
        font-size: 13px;
        margin-top: 4px;
        font-weight: 500;
    }

    .alert-danger {
        background-color: var(--danger-bg);
        color: var(--danger);
        border: 1px solid #ef4444;
        padding: 14px 18px;
        border-radius: 10px;
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 16px;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb mb-3">
            <div class="breadcrumb-title">Foydalanuvchini Tahrirlash</div>
            <a href="{{ route('superadmin.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        Ma’lumotlarni tekshirib, qaytadan kiriting.
                    </div>
                @endif

                <form action="{{ route('admin.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- F.I.O --}}
                    <div class="mb-3">
                        <label for="fio">F.I.O</label>
                        <input type="text" id="fio" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefon raqam</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">+998</span>
                            <input type="text"
                                   class="form-control"
                                   id="phone"
                                   name="phone"
                                   maxlength="9"
                                   pattern="[0-9]{9}"
                                   value="{{ old('phone', $user->phone) }}"
                                   required>
                        </div>
                        @error('phone') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- Rol --}}
                    <div class="mb-3">
                        <label for="role">Rolni tanlang</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">-- Rolni tanlang --</option>
                            @foreach($rols as $rol)
                                <option value="{{ $rol->name }}" {{ $user->hasRole($rol->name) ? 'selected' : '' }}>
                                    {{ ucfirst($rol->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- Filial --}}
                    <div class="mb-3" id="filial_box" style="display: none;">
                        <label for="filial">Filialni tanlang</label>
                        <select name="filial_id" id="filial" class="form-control">
                            <option value="">-- Filialni tanlang --</option>
                            @foreach($filials as $filial)
                                <option value="{{ $filial->id }}" {{ $user->filial_id == $filial->id ? 'selected' : '' }}>
                                    {{ $filial->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('filial_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- Login --}}
                    <div class="mb-3">
                        <label for="login">Login</label>
                        <input type="text" id="login" name="login" class="form-control"
                               value="{{ old('login', $user->login) }}" required>
                        @error('login') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- Parol --}}
                    <div class="mb-3">
                        <label for="password">Yangi parol (ixtiyoriy)</label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Agar o‘zgartirmoqchi bo‘lsangiz, kiriting">
                        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-custom">Yangilash</button>
                        <a href="{{ route('superadmin.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const filialBox = document.getElementById('filial_box');

        function toggleFilial() {
            if (roleSelect.value.trim().toLowerCase() === 'employee') {
                filialBox.style.display = 'block';
            } else {
                filialBox.style.display = 'none';
                document.getElementById('filial').value = '';
            }
        }

        toggleFilial();
        roleSelect.addEventListener('change', toggleFilial);
    });
</script>
@endsection
