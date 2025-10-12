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
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15);
    }

    .card-body {
        padding: 25px;
    }

    label {
        font-weight: 500;
        color: var(--blue-main);
        margin-bottom: 6px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--blue-light);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
    }

    .btn-custom {
        background: var(--blue-light);
        border: none;
        color: white;
        padding: 10px 20px;
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

    .btn-outline {
        background: var(--white);
        color: var(--blue-light);
        border: 1px solid var(--blue-light);
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        transition: 0.3s;
    }

    .btn-outline:hover {
        background: var(--blue-light);
        color: var(--white);
    }
    .alert {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
             
        <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
            <div class="breadcrumb-title pe-3"> Filial tahrirlash formasi</div>
            <a href="{{ route('admin.filial.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <form action="{{ route('admin.filial.update',['filial'=>$filial->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="filial_nomi">Filial nomi</label>
                        <input type="text" id="filial_nomi" value="{{ $filial->name }}" name="name" class="form-control" placeholder="Masalan: Toshkent markaziy filial" required>
                    </div>

                    <div class="mb-3">
                        <label for="filial_kodi">Filial kodi</label>
                        <input type="text" id="filial_kodi" name="code" value="{{ $filial->code }}" class="form-control" placeholder="Masalan: TSH001" required>
                    </div>

                    <div class="mb-3">
                        <label for="description">Filial izoh</label>
                        <textarea id="description"  name="description" rows="4" class="form-control" placeholder="Filial haqida qisqacha ma’lumot...">{{ $filial->description }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-custom">Saqlash</button>
                        <a href="{{ route('admin.filial.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection
