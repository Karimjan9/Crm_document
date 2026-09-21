@extends('template')

@section('style')
<style>
    .catalogue-form-page { max-width:980px; padding:28px; color:#182a47; }
    .catalogue-form-head { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; padding:25px 28px; border:1px solid #dce7f5; border-radius:19px; background:linear-gradient(120deg,#fff,#f4f8ff); box-shadow:0 7px 22px rgba(26,69,127,.07); }
    .catalogue-form-head__eyebrow { margin:0 0 5px; color:#6d7f9a; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .catalogue-form-head h1 { margin:0; color:#142f5c; font-size:1.45rem; font-weight:800; }
    .catalogue-form-head p { margin:7px 0 0; color:#667793; }
    .catalogue-back { display:inline-flex; align-items:center; gap:6px; border:1px solid #b9cae4; border-radius:10px; padding:10px 13px; background:#fff; color:#24508b; text-decoration:none; font-weight:700; white-space:nowrap; }
    .catalogue-card { margin-top:19px; padding:27px; border:1px solid #dce7f5; border-radius:19px; background:#fff; box-shadow:0 7px 22px rgba(26,69,127,.06); }
    .catalogue-card__intro { display:flex; gap:10px; margin-bottom:23px; padding:12px 14px; border:1px solid #d7e6fb; border-radius:11px; background:#f5f9ff; color:#526a8e; font-size:.87rem; }
    .catalogue-card__intro i { color:#e51f4b; font-size:1.15rem; }
    .catalogue-grid { display:grid; grid-template-columns:1.25fr .75fr; gap:18px; }
    .catalogue-field { display:grid; gap:7px; }
    .catalogue-field--wide { grid-column:1 / -1; }
    .catalogue-field label { color:#29466f; font-size:.84rem; font-weight:800; }
    .catalogue-field label span { color:#8491a6; font-weight:500; }
    .catalogue-field input, .catalogue-field textarea { width:100%; border:1px solid #cedbeb; border-radius:10px; padding:11px 12px; background:#fbfdff; color:#203554; outline:none; }
    .catalogue-field textarea { min-height:116px; resize:vertical; }
    .catalogue-field input:focus, .catalogue-field textarea:focus { border-color:#1b7dda; box-shadow:0 0 0 3px rgba(27,125,218,.12); background:#fff; }
    .catalogue-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:25px; padding-top:19px; border-top:1px solid #e8eef7; }
    .catalogue-save { border:0; border-radius:10px; padding:11px 17px; background:#e51f4b; color:#fff; font-weight:800; box-shadow:0 6px 14px rgba(229,31,75,.2); }
    .catalogue-cancel { border:1px solid #c7d5e8; border-radius:10px; padding:10px 16px; color:#40608d; font-weight:700; text-decoration:none; }
    @media (max-width:650px) { .catalogue-form-page { padding:16px; } .catalogue-form-head { padding:21px; flex-direction:column; } .catalogue-grid { grid-template-columns:1fr; } .catalogue-field--wide { grid-column:auto; } .catalogue-actions { flex-direction:column-reverse; } .catalogue-actions > * { text-align:center; width:100%; } }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <main class="catalogue-form-page">
        <header class="catalogue-form-head">
            <div><p class="catalogue-form-head__eyebrow">Xizmatlar katalogi</p><h1>Yangi xizmat qo‘shish</h1><p>Mijozga taklif qilinadigan asosiy xizmatning nomi, narxi va muddatini kiriting.</p></div>
            <a class="catalogue-back" href="{{ route('superadmin.service.index') }}"><i class='bx bx-left-arrow-alt'></i> Katalogga qaytish</a>
        </header>

        <form action="{{ route('superadmin.service.store') }}" method="POST" class="catalogue-card">@csrf
            @if ($errors->any())<div class="alert alert-danger mb-3">{{ $errors->first() }}</div>@endif
            <div class="catalogue-card__intro"><i class='bx bx-info-circle'></i><span>Bu yerda faqat xizmatning tijoriy parametrlari saqlanadi. Fayl yoki majburiy hujjatlar ushbu xizmatga avtomatik biriktirilmaydi.</span></div>
            <div class="catalogue-grid">
                <div class="catalogue-field"><label for="service_name">Xizmat nomi</label><input id="service_name" name="name" value="{{ old('name') }}" placeholder="Masalan: Hujjat tarjimasi" required></div>
                <div class="catalogue-field"><label for="service_price">Asosiy narx <span>(so‘m)</span></label><input id="service_price" type="number" min="0" step="0.01" name="price" value="{{ old('price') }}" placeholder="150000" required></div>
                <div class="catalogue-field"><label for="service_deadline">Bajarilish muddati <span>(kun)</span></label><input id="service_deadline" type="number" min="0" step="1" name="deadline" value="{{ old('deadline') }}" placeholder="3" required></div>
                <div class="catalogue-field catalogue-field--wide"><label for="service_description">Qisqacha izoh <span>(ixtiyoriy)</span></label><textarea id="service_description" name="description" placeholder="Xizmat tarkibi yoki mijoz uchun muhim ma’lumotni yozing.">{{ old('description') }}</textarea></div>
            </div>
            <div class="catalogue-actions"><a href="{{ route('superadmin.service.index') }}" class="catalogue-cancel">Bekor qilish</a><button type="submit" class="catalogue-save"><i class='bx bx-check'></i> Xizmatni saqlash</button></div>
        </form>
    </main>
</div>
@endsection
