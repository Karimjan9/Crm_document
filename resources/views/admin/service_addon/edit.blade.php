@extends('template')

@section('style')
<style>
    .addon-form-page { max-width:980px; padding:28px; color:#182a47; }.addon-form-head { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; padding:25px 28px; border:1px solid #dce7f5; border-radius:19px; background:linear-gradient(120deg,#fff,#f4f8ff); box-shadow:0 7px 22px rgba(26,69,127,.07); }.addon-eyebrow { margin:0 0 5px; color:#6d7f9a; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }.addon-form-head h1 { margin:0; color:#142f5c; font-size:1.45rem; font-weight:800; }.addon-form-head p { margin:7px 0 0; color:#667793; }.addon-back { display:inline-flex; align-items:center; gap:6px; border:1px solid #b9cae4; border-radius:10px; padding:10px 13px; background:#fff; color:#24508b; text-decoration:none; font-weight:700; white-space:nowrap; }
    .addon-card { margin-top:19px; padding:27px; border:1px solid #dce7f5; border-radius:19px; background:#fff; box-shadow:0 7px 22px rgba(26,69,127,.06); }.addon-parent { display:flex; align-items:center; gap:10px; margin-bottom:22px; padding:12px 14px; border:1px solid #d7e6fb; border-radius:11px; background:#f5f9ff; color:#526a8e; font-size:.87rem; }.addon-parent i { color:#e51f4b; font-size:1.15rem; }.addon-parent strong { color:#254978; }.addon-grid { display:grid; grid-template-columns:1.25fr .75fr; gap:18px; }.addon-field { display:grid; gap:7px; }.addon-field--wide { grid-column:1 / -1; }.addon-field label { color:#29466f; font-size:.84rem; font-weight:800; }.addon-field label span { color:#8491a6; font-weight:500; }.addon-field input,.addon-field textarea { width:100%; border:1px solid #cedbeb; border-radius:10px; padding:11px 12px; background:#fbfdff; color:#203554; outline:none; }.addon-field textarea { min-height:116px; resize:vertical; }.addon-field input:focus,.addon-field textarea:focus { border-color:#1b7dda; box-shadow:0 0 0 3px rgba(27,125,218,.12); background:#fff; }.addon-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:25px; padding-top:19px; border-top:1px solid #e8eef7; }.addon-save { border:0; border-radius:10px; padding:11px 17px; background:#e51f4b; color:#fff; font-weight:800; box-shadow:0 6px 14px rgba(229,31,75,.2); }.addon-cancel { border:1px solid #c7d5e8; border-radius:10px; padding:10px 16px; color:#40608d; font-weight:700; text-decoration:none; }
    @media (max-width:650px) { .addon-form-page { padding:16px; }.addon-form-head { padding:21px; flex-direction:column; }.addon-grid { grid-template-columns:1fr; }.addon-field--wide { grid-column:auto; }.addon-actions { flex-direction:column-reverse; }.addon-actions > * { text-align:center; width:100%; } }
</style>
@endsection

@section('body')
<div class="page-wrapper"><main class="addon-form-page">
    <header class="addon-form-head"><div><p class="addon-eyebrow">Qo‘shimcha xizmat</p><h1>Qo‘shimcha xizmatni tahrirlash</h1><p>“{{ $addon->name }}” qo‘shimcha xizmatining narxi va muddatini yangilang.</p></div><a class="addon-back" href="{{ route('superadmin.service.index') }}"><i class='bx bx-left-arrow-alt'></i> Katalogga qaytish</a></header>
    <form action="{{ route('superadmin.addon.update', [$service_id, $addon->id]) }}" method="POST" class="addon-card">@csrf @method('PUT')
        @if ($errors->any())<div class="alert alert-danger mb-3">{{ $errors->first() }}</div>@endif
        <div class="addon-parent"><i class='bx bx-briefcase-alt-2'></i><span>Asosiy xizmat: <strong>{{ $service->name }}</strong></span></div>
        <div class="addon-grid">
            <div class="addon-field"><label for="addon_name">Qo‘shimcha xizmat nomi</label><input id="addon_name" name="name" value="{{ old('name', $addon->name) }}" required></div>
            <div class="addon-field"><label for="addon_price">Qo‘shimcha narx <span>(so‘m)</span></label><input id="addon_price" type="number" min="0" step="0.01" name="price" value="{{ old('price', $addon->price) }}" required></div>
            <div class="addon-field"><label for="addon_deadline">Qo‘shimcha muddat <span>(kun)</span></label><input id="addon_deadline" type="number" min="0" step="1" name="deadline" value="{{ old('deadline', $addon->deadline) }}" required></div>
            <div class="addon-field addon-field--wide"><label for="addon_description">Qisqacha izoh <span>(ixtiyoriy)</span></label><textarea id="addon_description" name="description" placeholder="Qo‘shimcha xizmat nimani o‘z ichiga olishini yozing.">{{ old('description', $addon->description) }}</textarea></div>
        </div>
        <div class="addon-actions"><a href="{{ route('superadmin.service.index') }}" class="addon-cancel">Bekor qilish</a><button type="submit" class="addon-save"><i class='bx bx-check'></i> O‘zgarishlarni saqlash</button></div>
    </form>
</main></div>
@endsection
