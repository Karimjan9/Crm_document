@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    :root {
        --text-color: #1e293b;
        --blue-main: #1d4ed8; /* biroz to'yinganroq */
        --blue-hover: #1e40af;
        --blue-gradient: linear-gradient(135deg, #2563eb, #1d4ed8);
        --blue-bg: #f3f6fc;
        --white: #ffffff;
        --border-color: #e2e8f0;
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

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    .alert {
        border-radius: 10px;
        padding: 14px 18px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb mb-3">
            <div class="breadcrumb-title">Yangi Filial Qo‘shish</div>
            <a href="{{ route('superadmin.consulation.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <form action="{{ route('superadmin.consulation.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="filial_nomi">Konsullik</label>
                        <input type="text" id="filial_nomi" name="name" class="form-control" placeholder="BAA" required>
                    </div>

                    <div class="mb-3">
                        <label for="amount">Konsullik narxi </label>
                        <input type="number" id="amount" name="amount" class="form-control" placeholder="1000" required>
                    </div>
                    <div class="mb-3">
                        <label for="day">Konsullik deadline (kunlarda)</label>
                        <input type="number" id="day" name="day" class="form-control" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="description">Konsullik izoh</label>
                        <textarea id="description" name="description" rows="4" class="form-control" placeholder="Konsullik haqida qisqacha ma’lumot..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-custom">Saqlash</button>
                        <a href="{{ route('superadmin.consulation.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
