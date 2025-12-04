@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    :root {
        --text-color: #15172a;
        --blue-main: #1e3a8a;
        --blue-light: #2563eb;
        --white: rgba(255, 255, 255, 0.7);
        --border-color: rgba(229, 231, 235, 0.4);
    }

    body {
        font-family: "Poppins", "Inter", "system-ui", "Segoe UI", "Roboto", sans-serif;
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        backdrop-filter: blur(8px);
        color: var(--text-color);
        margin: 0;
        padding: 0;
    }

    .page-wrapper {
        padding: 24px;
    }

    .page-breadcrumb {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 12px 20px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(37, 99, 235, 0.08);
    }

    .breadcrumb-title {
        font-weight: 600;
        color: var(--blue-main);
    }

    .card {
        background: rgba(255, 255, 255, 0.4);
        border-radius: 16px;
        backdrop-filter: blur(10px);
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 14px rgba(30, 58, 138, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.2);
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
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(6px);
        transition: all 0.25s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--blue-light);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.25);
        background: rgba(255, 255, 255, 0.8);
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
        backdrop-filter: blur(4px);
    }

    .btn-custom:hover {
        background: var(--blue-main);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        backdrop-filter: blur(10px);
    }

    .btn-outline {
        background: rgba(255, 255, 255, 0.4);
        color: var(--blue-light);
        border: 1px solid var(--blue-light);
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        transition: 0.3s;
        backdrop-filter: blur(6px);
    }

    .btn-outline:hover {
        background: var(--blue-light);
        color: var(--white);
        backdrop-filter: blur(10px);
    }

    .alert {
        border-radius: 10px;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.15);
        color: #065f46;
        border: 1px solid #10b981;
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.15);
        color: #991b1b;
        border: 1px solid #ef4444;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
            <div class="breadcrumb-title pe-3">Hujjat yo'nalish tahrirlash formasi</div>
            <a href="{{ route('superadmin.document_type.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <form action="{{ route('superadmin.document_type.update',['document_type'=>$documentType->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="filial_nomi">Hujjat yo'nalish nomi</label>
                        <input type="text" id="filial_nomi" value="{{ $documentType->name }}" name="name" class="form-control" placeholder="Hujjat turi" required>
                    </div>

                  

                    <div class="mb-3">
                        <label for="description">Hujjat turi izoh</label>
                        <textarea id="description" name="description" rows="4" class="form-control" placeholder="Filial haqida qisqacha ma’lumot...">{{ $documentType->description }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-custom">Saqlash</button>
                        <a href="{{ route('superadmin.document_type.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection
