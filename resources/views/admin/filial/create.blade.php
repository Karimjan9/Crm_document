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
@php
    $filialRoutePrefix = auth()->user()?->hasRole('super_admin') ? 'superadmin' : 'admin';
    $holidayDates = old('holiday_dates', '');
    $holidayDates = is_array($holidayDates) ? implode(', ', $holidayDates) : $holidayDates;
@endphp
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb mb-3">
            <div class="breadcrumb-title">Yangi Filial Qo‘shish</div>
            <a href="{{ route($filialRoutePrefix . '.filial.index') }}" class="btn btn-outline">← Orqaga</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <form action="{{ route($filialRoutePrefix . '.filial.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="filial_nomi">Filial nomi</label>
                        <input type="text" id="filial_nomi" name="name" class="form-control" placeholder="Masalan: Toshkent markaziy filial" required>
                    </div>

                    <div class="mb-3">
                        <label for="filial_kodi">Filial kodi</label>
                        <input type="text" id="filial_kodi" name="code" class="form-control" placeholder="Masalan: TSH001" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="phone">Telefon</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+998 90 000 00 00">
                        </div>
                        <div class="col-md-6">
                            <label for="manager_id">Filial manageri</label>
                            <select id="manager_id" name="manager_id" class="form-control">
                                <option value="">Biriktirilmagan</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" @selected((int) old('manager_id') === (int) $manager->id)>{{ $manager->name }}{{ $manager->filial_id ? ' — filial #' . $manager->filial_id : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="address">Manzil</label>
                            <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control" placeholder="Filial manzili">
                        </div>
                        <div class="col-md-6">
                            <label for="work_start_time">Ish boshlanishi</label>
                            <input type="time" id="work_start_time" name="work_start_time" value="{{ old('work_start_time', '09:00') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="work_end_time">Ish tugashi</label>
                            <input type="time" id="work_end_time" name="work_end_time" value="{{ old('work_end_time', '18:00') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="monthly_expense">Oylik xarajat rejasi</label>
                            <input type="number" min="0" step="0.01" id="monthly_expense" name="monthly_expense" value="{{ old('monthly_expense', 0) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="target_amount">Target</label>
                            <input type="number" min="0" step="0.01" id="target_amount" name="target_amount" value="{{ old('target_amount', 0) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="commission_percent">Commission (%)</label>
                            <input type="number" min="0" max="100" step="0.01" id="commission_percent" name="commission_percent" value="{{ old('commission_percent', 0) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="daily_capacity">Maksimal kunlik capacity</label>
                            <input type="number" min="0" id="daily_capacity" name="daily_capacity" value="{{ old('daily_capacity') }}" class="form-control" placeholder="Masalan: 30">
                        </div>
                        <div class="col-md-6">
                            <label>Haftalik ish kunlari</label>
                            <div class="d-flex flex-wrap gap-3 pt-2">
                                @foreach([1 => 'Du', 2 => 'Se', 3 => 'Cho', 4 => 'Pa', 5 => 'Ju', 6 => 'Sha', 7 => 'Ya'] as $day => $label)
                                    <label class="d-flex gap-1 align-items-center"><input type="checkbox" name="working_days[]" value="{{ $day }}" @checked(in_array($day, array_map('intval', old('working_days', [1,2,3,4,5,6]))))> {{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="holiday_dates">Filial dam olish sanalari</label>
                            <input type="text" id="holiday_dates" name="holiday_dates" value="{{ $holidayDates }}" class="form-control" placeholder="2026-01-01, 2026-03-21">
                            <small class="text-muted">Sanalarni vergul yoki bo‘sh joy bilan ajrating. Umumiy bayramlar alohida kalendardan olinadi.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description">Filial izoh</label>
                        <textarea id="description" name="description" rows="4" class="form-control" placeholder="Filial haqida qisqacha ma’lumot..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-custom">Saqlash</button>
                        <a href="{{ route($filialRoutePrefix . '.filial.index') }}" class="btn btn-outline">Bekor qilish</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
