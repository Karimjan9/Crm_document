@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

:root {
    --text-color: #15172a;
    --blue-main: #1e3a8a;
    --blue-light: #2563eb;
    --white: #ffffff;
    --border-color: #e5e7eb;
}

/* RESET */
html, body {
    height: 100%;
}

/* BODY */
body {
    font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    color: var(--text-color);
    margin: 0;
}

/* PAGE */
.page-wrapper {
    padding: 24px;
}

.page-content {
    max-width: 100%;
}

/* BREADCRUMB */
.page-breadcrumb {
    background: #ffffff;
    border-radius: 12px;
    padding: 12px 20px;
    border: 1px solid var(--border-color);
    box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
}

.breadcrumb-title {
    font-weight: 600;
    color: var(--blue-main);
}

/* CARD */
.card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.1);
}

.card-body {
    padding: 24px;
}

/* FORM */
label {
    font-weight: 500;
    color: var(--blue-main);
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    font-size: 14px;
    background: #ffffff;
}

.form-control:focus {
    border-color: var(--blue-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
    outline: none;
}

/* BUTTONS */
.btn-custom {
    background: var(--blue-light);
    color: #ffffff;
    border: none;
    padding: 10px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: 0.25s;
}

.btn-custom:hover {
    background: var(--blue-main);
}

.btn-outline {
    background: #ffffff;
    color: var(--blue-light);
    border: 1px solid var(--blue-light);
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: 0.25s;
}

.btn-outline:hover {
    background: var(--blue-light);
    color: #ffffff;
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-flex align-items-center justify-content-between mb-4">
            <div class="breadcrumb-title">
                Hujjat yo'nalish tahrirlash formasiii
            </div>
            <a href="{{ route('superadmin.document_type.index') }}" class="btn btn-outline">
                ← Orqaga
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('superadmin.document_type.update',['document_type'=>$documentType->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label>Hujjat yo'nalish nomi</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ $documentType->name }}"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label>Hujjat turi izoh</label>
                        <textarea
                            name="description"
                            rows="4"
                            class="form-control"
                        >{{ $documentType->description }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button class="btn btn-custom">Saqlash</button>
                        <a href="{{ route('superadmin.document_type.index') }}" class="btn btn-outline">
                            Bekor qilish
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
