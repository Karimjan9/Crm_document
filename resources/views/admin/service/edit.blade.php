@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    :root {
        --text-color: #1e293b;
        --blue-main: #1d4ed8;
        --blue-hover: #1e40af;
        --blue-gradient: linear-gradient(135deg, #2563eb, #244b8a);
        --blue-bg: #f3f6fc;
        --white: #ffffff;
        --border-color: #e2e8f0;
    }

    body {
        font-family: "Poppins", sans-serif;
        background: var(--blue-bg);
        color: var(--text-color);
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }

    .page-wrapper {
        padding: 40px 20px;
    }

    .page-breadcrumb {
        background: var(--white);
        border-radius: 14px;
        padding: 16px 24px;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .breadcrumb-title {
        font-weight: 600;
        font-size: 20px;
        color: var(--blue-main);
        letter-spacing: 0.5px;
    }

    .card {
        background: var(--white);
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(37, 99, 235, 0.08);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
        transform: translateY(-3px);
    }

    .card-body {
        padding: 35px;
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
        padding: 14px 16px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        font-size: 14px;
        background-color: #f9fafb;
        transition: all 0.25s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--blue-main);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        background-color: var(--white);
    }

    .btn-custom {
        background: var(--blue-gradient);
        border: none;
        color: var(--white);
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 15px;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
    }

    .btn-custom:hover {
        background: linear-gradient(135deg, #0a2366, #1e40af);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
    }

    .btn-outline {
        background: var(--white);
        color: var(--blue-main);
        border: 1px solid var(--blue-main);
        padding: 12px 26px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background: var(--blue-main);
        color: var(--white);
        box-shadow: 0 5px 14px rgba(37, 99, 235, 0.25);
    }

    textarea {
        resize: vertical;
        min-height: 120px;
    }

    .alert {
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        font-weight: 500;
        font-size: 14px;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #22c55e;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #ef4444;
    }

    ::placeholder {
        color: #9ca3af;
        opacity: 1;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb">
            <div class="breadcrumb-title">Service o'zgartirish</div>
            <a href="{{ route('superadmin.service.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('superadmin.service.update',['service'=>$service->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="filial_nomi">Service nomi</label>
                        <input type="text" id="filial_nomi" name="name" value="{{ $service->name }}" class="form-control" placeholder="Tarjima qilish" required>
                    </div>

                    <div class="mb-3">
                        <label for="filial_kodi">Service narxi</label>
                        <input type="number" id="filial_kodi" name="price" value="{{ $service->price }}" class="form-control" placeholder="000000" required>
                    </div>

                    <div class="mb-3">
                        <label for="filial_deadline">Service deadline (kun)</label>
                        <input type="number" id="filial_deadline" name="deadline" value="{{ $service->deadline }}" class="form-control" placeholder="000000" required>
                    </div>

                    <div class="mb-3">
                        <label for="description">Service izoh</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Servise haqida qisqacha ma’lumot...">{{ $service->description }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="submit" class="btn btn-custom">Saqlash</button>
                        <a href="{{ route('superadmin.service.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
