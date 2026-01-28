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

    .page-wrapper {
        padding: 40px;
    }

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
        font-weight: 700;
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

    .card-body {
        padding: 30px;
    }

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

    /* 🌫 Shafof effekt faqat "Rolni tanlang" uchun */
    #role:focus {
        background: rgba(37, 99, 235, 0.08) !important;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
        transition: all 0.3s ease;
    }

    #role option {
        background-color: rgba(255, 255, 255, 0.95);
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

    .alert {
        border-radius: 10px;
        padding: 14px 18px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .alert-danger {
        background-color: var(--danger-bg);
        color: var(--danger);
        border: 1px solid #ef4444;
    }

</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb mb-3">
            <div class="breadcrumb-title">Yangi Foydalanuvchi Qo‘shish</div>
            <a href="{{ route('superadmin.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">

                {{-- Umumiy xato chiqarsa --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        Ma’lumotlarni tekshirib, qaytadan kiriting.
                    </div>
                @endif

                <form action="{{ route('superadmin.store') }}" method="POST">
                    @csrf

                    {{-- FIO --}}
                    <div class="mb-3">
                        <label for="fio">F.I.O</label>
                        <input type="text" id="fio" name="name" class="form-control" 
                               placeholder="Masalan: Alisher Xamidov" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone number</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">+998</span>
                            <input type="text" 
                                class="form-control" 
                                id="phone" 
                                name="phone" 
                                maxlength="9"
                                pattern="[0-9]{9}" 
                                placeholder="901234567"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role">Rolni tanlang</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">-- Rolni tanlang --</option>
                            @foreach($rols as $rol)
                                <option value="{{ $rol->name }}" {{ old('role') == $rol->name ? 'selected' : '' }}>
                                    {{ ucfirst($rol->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FILIAL --}}
                    <div class="mb-3" id="filial_box" style="display:none;">
                        <label for="filial">Filialni tanlang</label>
                        <select name="filial_id" id="filial" class="form-control">
                            <option value="">-- Filialni tanlang --</option>
                            @foreach($filials as $filial)
                                <option value="{{ $filial->id }}" {{ old('filial_id') == $filial->id ? 'selected' : '' }}>
                                    {{ $filial->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('filial_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- LOGIN --}}
                    <div class="mb-3">
                        <label for="login">Login</label>    
                        <input type="text" id="login" name="login" class="form-control" 
                               placeholder="Masalan: alisher01"
                               value="{{ old('login') }}" required>
                        @error('login')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- PAROL 1 --}}
                    <div class="mb-3">
                        <label for="password">Parol</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Parol kiriting..." required>
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- PAROL 2 --}}
                    <div class="mb-3">
                        <label for="password_confirmation">Parolni tasdiqlang</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                               class="form-control" placeholder="Parolni qaytadan kiriting..." required>
                    </div>

                    {{-- TUGMALAR --}}
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-custom">Saqlash</button>
                        <a href="{{ route('superadmin.filial.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const filialBox = document.getElementById('filial_box');

        function toggleFilial() {
            if (roleSelect.value.trim().toLowerCase() === 'employee' || roleSelect.value.trim().toLowerCase() === 'admin_filial') {
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
