@extends('template')

@section('style')
<style>
    .expense-page { padding:26px; color:#1b2c49; }
    .expense-hero { display:flex; align-items:center; justify-content:space-between; gap:22px; padding:26px 29px; border:1px solid #dbe6f4; border-radius:19px; background:linear-gradient(120deg,#fff 0%,#f2f7ff 100%); box-shadow:0 8px 24px rgba(31,70,127,.07); }
    .expense-eyebrow { margin:0 0 5px; color:#6e7d97; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }.expense-hero h1 { margin:0; color:#142f5d; font-size:1.5rem; font-weight:800; }.expense-hero p { margin:7px 0 0; color:#667793; }.expense-create { display:inline-flex; align-items:center; gap:7px; flex-shrink:0; padding:12px 17px; border:0; border-radius:11px; background:#e51f4b; color:#fff!important; font-weight:800; text-decoration:none; box-shadow:0 7px 16px rgba(229,31,75,.22); }.expense-create:hover { background:#c9153d; transform:translateY(-1px); }
    .expense-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin:19px 0; }.expense-stat { display:flex; align-items:center; gap:12px; padding:16px; border:1px solid #dfe8f5; border-radius:15px; background:#fff; box-shadow:0 5px 16px rgba(31,63,110,.05); }.expense-stat__icon { display:grid; place-items:center; flex:0 0 39px; width:39px; height:39px; border-radius:11px; color:#176ec5; background:#edf5ff; font-size:1.18rem; }.expense-stat__icon--all { color:#e51f4b; background:#fff0f4; }.expense-stat__icon--approved { color:#148a50; background:#edfcf4; }.expense-stat__icon--pending { color:#b76b0c; background:#fff7e9; }.expense-stat span { display:block; color:#72809a; font-size:.74rem; font-weight:700; }.expense-stat strong { display:block; margin-top:3px; color:#17396e; font-size:1.04rem; line-height:1.15; }
    .expense-filter { padding:19px; border:1px solid #dfe8f5; border-radius:18px; background:#fff; box-shadow:0 6px 18px rgba(31,63,110,.06); }.expense-filter h2 { display:flex; align-items:center; gap:8px; margin:0 0 14px; color:#1a3969; font-size:.94rem; font-weight:800; }.expense-filter h2 i { color:#e51f4b; font-size:1.12rem; }.expense-filter form { display:grid; grid-template-columns:1fr 1fr .82fr .82fr .86fr auto; align-items:end; gap:11px; }.expense-field { display:grid; gap:6px; }.expense-field label { color:#526a8d; font-size:.74rem; font-weight:800; }.expense-field input,.expense-field select { width:100%; height:41px; border:1px solid #cedbeb; border-radius:9px; padding:0 11px; color:#273e60; background:#fbfdff; outline:none; }.expense-field input:focus,.expense-field select:focus { border-color:#1e7fdd; box-shadow:0 0 0 3px rgba(30,127,221,.12); background:#fff; }.expense-filter-actions { display:flex; gap:8px; }.expense-filter-submit,.expense-filter-clear { display:inline-flex; align-items:center; justify-content:center; gap:6px; height:41px; border-radius:9px; padding:0 13px; font-size:.82rem; font-weight:800; text-decoration:none; white-space:nowrap; }.expense-filter-submit { border:0; color:#fff; background:#176fc7; }.expense-filter-clear { border:1px solid #cbd9ea; color:#46638d; background:#fff; }
    .expense-list { margin-top:19px; padding:20px; border:1px solid #dfe8f5; border-radius:18px; background:#fff; box-shadow:0 7px 20px rgba(31,63,110,.06); }.expense-list__head { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; padding:0 2px 16px; border-bottom:1px solid #e8eef7; }.expense-list__head h2 { margin:0; color:#16315e; font-size:1.04rem; font-weight:800; }.expense-list__head p { margin:4px 0 0; color:#748199; font-size:.83rem; }.expense-list__count { color:#53709b; font-size:.8rem; font-weight:700; white-space:nowrap; }.expense-table-wrap { overflow-x:auto; }.expense-table { width:100%; min-width:1030px; margin-top:15px; border-collapse:separate; border-spacing:0; }.expense-table th { padding:11px 12px; border-top:1px solid #e2eaf5; border-bottom:1px solid #e2eaf5; color:#60718c; background:#f7faff; font-size:.72rem; font-weight:800; letter-spacing:.03em; text-align:left; text-transform:uppercase; }.expense-table th:first-child { border-left:1px solid #e2eaf5; border-radius:9px 0 0 9px; }.expense-table th:last-child { border-right:1px solid #e2eaf5; border-radius:0 9px 9px 0; text-align:right; }.expense-table td { padding:13px 12px; border-bottom:1px solid #edf1f7; color:#344863; font-size:.85rem; vertical-align:middle; }.expense-table tbody tr:hover { background:#fbfdff; }.expense-date strong,.expense-person strong { display:block; color:#193762; font-size:.85rem; }.expense-date small,.expense-person small,.expense-description { display:block; margin-top:2px; color:#7e8ca2; font-size:.76rem; }.expense-category { display:inline-block; padding:4px 7px; border-radius:7px; color:#44618a; background:#f0f5fc; font-size:.75rem; font-weight:700; }.expense-amount { color:#173d74; font-weight:800; white-space:nowrap; }.expense-payment { color:#536d91; font-size:.8rem; }.expense-status { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; border-radius:999px; font-size:.73rem; font-weight:800; white-space:nowrap; }.expense-status--approved { color:#167648; background:#edfcf4; }.expense-status--pending { color:#a6640b; background:#fff5df; }.expense-status--rejected { color:#bf2547; background:#fff0f3; }.expense-actions { display:flex; justify-content:flex-end; gap:7px; }.expense-action { display:inline-flex; align-items:center; gap:5px; min-height:34px; padding:7px 9px; border:1px solid #c9d9ec; border-radius:8px; color:#24528b; background:#fff; font-size:.76rem; font-weight:800; text-decoration:none; }.expense-action:hover { border-color:#1d7cdc; background:#f3f8ff; }.expense-action--delete { border-color:#ffd0d9; color:#c61d43; background:#fff8fa; }.expense-pagination { padding-top:17px; }.expense-empty { padding:50px 20px; color:#75839a; text-align:center; }.expense-empty i { display:block; margin-bottom:8px; color:#e51f4b; font-size:2rem; }
    @media(max-width:1190px) { .expense-filter form { grid-template-columns:1fr 1fr 1fr; }.expense-filter-actions { justify-content:flex-end; } } @media(max-width:680px) { .expense-page { padding:16px; }.expense-hero { align-items:flex-start; flex-direction:column; padding:22px; }.expense-create { width:100%; justify-content:center; }.expense-stats { grid-template-columns:1fr 1fr; }.expense-filter form { grid-template-columns:1fr; }.expense-filter-actions > * { flex:1; }.expense-list { padding:15px; }.expense-list__head { align-items:flex-start; flex-direction:column; } }
</style>
@endsection

@section('body')
@php
    $hasFilters = $filters['filial_id'] || $filters['user_id'] || $filters['date_from'] || $filters['date_to'] || $filters['approval_status'];
    $paymentLabels = ['cash' => 'Naqd', 'card' => 'Karta', 'bank_transfer' => 'Bank o‘tkazmasi', 'online' => 'Onlayn', 'other' => 'Boshqa'];
    $statusLabels = ['approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan', 'pending' => 'Kutilmoqda'];
@endphp
<div class="page-wrapper">
    <main class="expense-page">
        @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger mb-3">{{ session('error') }}</div>@endif

        <section class="expense-hero">
            <div><p class="expense-eyebrow">Moliyaviy boshqaruv</p><h1>Xarajatlar</h1><p>Filiallar xarajatlarini ko‘ring, davr va mas’ul xodim bo‘yicha filtrlang hamda tasdiqlash holatini kuzating.</p></div>
            <a class="expense-create" href="{{ route($routePrefix . '.expense.create') }}"><i class='bx bx-plus'></i> Xarajat qo‘shish</a>
        </section>

        <section class="expense-stats" aria-label="Xarajatlar ko‘rsatkichlari">
            <article class="expense-stat"><span class="expense-stat__icon expense-stat__icon--all"><i class='bx bx-receipt'></i></span><span>Jami xarajat<strong>{{ number_format($summary['total'], 0, ',', ' ') }} so‘m</strong></span></article>
            <article class="expense-stat"><span class="expense-stat__icon"><i class='bx bx-list-ul'></i></span><span>Xarajatlar soni<strong>{{ $summary['count'] }} ta</strong></span></article>
            <article class="expense-stat"><span class="expense-stat__icon expense-stat__icon--approved"><i class='bx bx-check-circle'></i></span><span>Tasdiqlangan summa<strong>{{ number_format($summary['approved'], 0, ',', ' ') }} so‘m</strong></span></article>
            <article class="expense-stat"><span class="expense-stat__icon expense-stat__icon--pending"><i class='bx bx-time-five'></i></span><span>Kutilayotgan tasdiq<strong>{{ $summary['pending'] }} ta</strong></span></article>
        </section>

        <section class="expense-filter">
            <h2><i class='bx bx-filter-alt'></i> Xarajatlarni filtrlash</h2>
            <form method="GET" action="{{ route($routePrefix . '.expense.index') }}">
                <div class="expense-field"><label for="expense_filial">Filial</label><select id="expense_filial" name="filial_id"><option value="">Barcha filiallar</option>@foreach($filials as $filial)<option value="{{ $filial->id }}" @selected((int) $filters['filial_id'] === $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>
                <div class="expense-field"><label for="expense_user">Mas’ul foydalanuvchi</label><select id="expense_user" name="user_id"><option value="">Barcha foydalanuvchilar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) $filters['user_id'] === $user->id)>{{ $user->name }} · {{ $user->login }}</option>@endforeach</select></div>
                <div class="expense-field"><label for="expense_from">Dan</label><input id="expense_from" type="date" name="date_from" value="{{ $filters['date_from'] }}"></div>
                <div class="expense-field"><label for="expense_to">Gacha</label><input id="expense_to" type="date" name="date_to" value="{{ $filters['date_to'] }}"></div>
                <div class="expense-field"><label for="expense_status">Tasdiqlash holati</label><select id="expense_status" name="approval_status"><option value="">Barchasi</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($filters['approval_status'] === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="expense-filter-actions"><button class="expense-filter-submit" type="submit"><i class='bx bx-search'></i> Filtrlash</button>@if($hasFilters)<a class="expense-filter-clear" href="{{ route($routePrefix . '.expense.index') }}">Tozalash</a>@endif</div>
            </form>
        </section>

        <section class="expense-list">
            <div class="expense-list__head"><div><h2>Xarajatlar ro‘yxati</h2><p>{{ $hasFilters ? 'Tanlangan filtrlar bo‘yicha natijalar.' : 'Barcha filiallar bo‘yicha kiritilgan xarajatlar.' }}</p></div><span class="expense-list__count">{{ $expenses->total() }} ta natija</span></div>
            <div class="expense-table-wrap"><table class="expense-table"><thead><tr><th>Sana</th><th>Filial / mas’ul</th><th>Kategoriya</th><th>To‘lov</th><th>Summa</th><th>Tasdiq</th><th>Izoh</th><th>Amallar</th></tr></thead><tbody>
                @forelse($expenses as $expense)
                    @php($status = in_array($expense->approval_status, ['approved', 'pending', 'rejected'], true) ? $expense->approval_status : 'pending')
                    <tr>
                        <td><div class="expense-date"><strong>{{ optional($expense->expense_date ?: $expense->created_at)->format('d.m.Y') }}</strong><small>{{ optional($expense->expense_date ?: $expense->created_at)->format('H:i') }}</small></div></td>
                        <td><div class="expense-person"><strong>{{ $expense->filial?->name ?? 'Filial ko‘rsatilmagan' }}</strong><small>{{ $expense->user?->name ?? 'Foydalanuvchi ko‘rsatilmagan' }}</small></div></td>
                        <td><span class="expense-category">{{ $expense->category?->name ?? 'Umumiy xarajat' }}</span>@if($expense->vendor)<small class="expense-description">{{ $expense->vendor->name }}</small>@endif</td>
                        <td><span class="expense-payment">{{ $paymentLabels[$expense->payment_method] ?? 'Naqd' }}</span></td>
                        <td><span class="expense-amount">{{ number_format((float) $expense->amount, 0, ',', ' ') }} {{ $expense->currency ?: 'UZS' }}</span></td>
                        <td><span class="expense-status expense-status--{{ $status }}"><i class='bx {{ $status === 'approved' ? 'bx-check-circle' : ($status === 'rejected' ? 'bx-x-circle' : 'bx-time-five') }}'></i>{{ $statusLabels[$status] }}</span></td>
                        <td><span class="expense-description">{{ \Illuminate\Support\Str::limit($expense->description ?: 'Izoh qoldirilmagan.', 55) }}</span></td>
                        <td><div class="expense-actions"><a class="expense-action" href="{{ route($routePrefix . '.expense.show', $expense) }}"><i class='bx bx-show'></i> Ko‘rish</a><a class="expense-action" href="{{ route($routePrefix . '.expense.edit', $expense) }}"><i class='bx bx-pencil'></i> Tahrirlash</a><form method="POST" action="{{ route($routePrefix . '.expense.destroy', $expense) }}" onsubmit="return confirm('Xarajatni o‘chirasizmi?')">@csrf @method('DELETE')<button class="expense-action expense-action--delete" type="submit"><i class='bx bx-trash'></i> O‘chirish</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="expense-empty"><i class='bx bx-receipt'></i><strong>Xarajat topilmadi.</strong><br><span>Filtrlarni o‘zgartiring yoki yangi xarajat kiriting.</span></div></td></tr>
                @endforelse
            </tbody></table></div>
            @if($expenses->hasPages())<div class="expense-pagination">{{ $expenses->links() }}</div>@endif
        </section>
    </main>
</div>
@endsection
