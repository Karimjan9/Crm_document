@extends('template')

@section('style')
<style>
    .user-directory { padding:26px; color:#1b2c49; }
    .user-directory__hero { display:flex; justify-content:space-between; align-items:center; gap:22px; padding:26px 29px; border:1px solid #dbe6f4; border-radius:19px; background:linear-gradient(120deg,#fff 0%,#f2f7ff 100%); box-shadow:0 8px 24px rgba(31,70,127,.07); }
    .user-directory__eyebrow { margin:0 0 5px; color:#6f7e97; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .user-directory__hero h1 { margin:0; color:#142f5d; font-size:1.5rem; font-weight:800; }
    .user-directory__hero p { margin:7px 0 0; color:#667793; max-width:720px; }
    .user-directory__create { display:inline-flex; align-items:center; gap:7px; flex-shrink:0; padding:12px 17px; border:0; border-radius:11px; background:#e51f4b; color:#fff!important; font-weight:800; text-decoration:none; box-shadow:0 7px 16px rgba(229,31,75,.22); }
    .user-directory__create:hover { background:#c9153d; transform:translateY(-1px); }
    .user-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin:19px 0; }
    .user-stat { display:flex; align-items:center; gap:12px; padding:16px; border:1px solid #dfe8f5; border-radius:15px; background:#fff; color:inherit; text-decoration:none; box-shadow:0 5px 16px rgba(31,63,110,.05); transition:.18s ease; }
    .user-stat:hover,.user-stat--active { border-color:#a8c9f5; box-shadow:0 8px 20px rgba(39,88,155,.09); transform:translateY(-1px); }
    .user-stat__icon { display:grid; place-items:center; flex:0 0 39px; width:39px; height:39px; border-radius:11px; background:#eef5ff; color:#1d73cb; font-size:1.2rem; }.user-stat__icon--courier { background:#eefcf7; color:#138c54; }.user-stat__icon--admin { background:#fff3ed; color:#d46722; }.user-stat__icon--all { background:#fff0f4; color:#e51f4b; }
    .user-stat span { display:block; color:#72809a; font-size:.75rem; font-weight:700; }.user-stat strong { display:block; margin-top:2px; color:#17396e; font-size:1.3rem; line-height:1.1; }
    .user-filters { padding:19px; border:1px solid #dfe8f5; border-radius:18px; background:#fff; box-shadow:0 6px 18px rgba(31,63,110,.06); }
    .user-filters__title { display:flex; align-items:center; gap:8px; margin:0 0 14px; color:#1a3969; font-size:.94rem; font-weight:800; }.user-filters__title i { color:#e51f4b; font-size:1.12rem; }
    .user-filter-form { display:grid; grid-template-columns:minmax(210px,1.7fr) minmax(150px,.8fr) minmax(170px,.9fr) auto; align-items:end; gap:12px; }.user-filter-field { display:grid; gap:6px; }.user-filter-field label { color:#526a8d; font-size:.74rem; font-weight:800; }.user-filter-field input,.user-filter-field select { height:41px; width:100%; border:1px solid #cedbeb; border-radius:9px; padding:0 11px; background:#fbfdff; color:#273e60; outline:none; }.user-filter-field input:focus,.user-filter-field select:focus { border-color:#1e7fdd; box-shadow:0 0 0 3px rgba(30,127,221,.12); background:#fff; }
    .user-filter-actions { display:flex; gap:8px; }.user-filter-submit,.user-filter-reset { display:inline-flex; align-items:center; justify-content:center; gap:6px; height:41px; border-radius:9px; padding:0 13px; font-weight:800; text-decoration:none; white-space:nowrap; }.user-filter-submit { border:0; background:#176fc7; color:#fff; }.user-filter-reset { border:1px solid #cbd9ea; color:#46638d; background:#fff; }
    .user-list-panel { margin-top:19px; padding:20px; border:1px solid #dfe8f5; border-radius:18px; background:#fff; box-shadow:0 7px 20px rgba(31,63,110,.06); }.user-list-panel__head { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; padding:0 2px 16px; border-bottom:1px solid #e8eef7; }.user-list-panel__head h2 { margin:0; color:#16315e; font-size:1.04rem; font-weight:800; }.user-list-panel__head p { margin:4px 0 0; color:#748199; font-size:.83rem; }.user-result-count { color:#53709b; font-size:.8rem; font-weight:700; white-space:nowrap; }
    .user-table-wrap { overflow-x:auto; }.user-table { width:100%; min-width:850px; border-collapse:separate; border-spacing:0; margin-top:15px; }.user-table th { padding:11px 12px; border-top:1px solid #e2eaf5; border-bottom:1px solid #e2eaf5; color:#60718c; background:#f7faff; font-size:.73rem; font-weight:800; letter-spacing:.03em; text-align:left; text-transform:uppercase; }.user-table th:first-child { border-left:1px solid #e2eaf5; border-radius:9px 0 0 9px; }.user-table th:last-child { border-right:1px solid #e2eaf5; border-radius:0 9px 9px 0; text-align:right; }.user-table td { padding:13px 12px; border-bottom:1px solid #edf1f7; color:#344863; font-size:.86rem; vertical-align:middle; }.user-table tbody tr:hover { background:#fbfdff; }.user-person { display:flex; align-items:center; gap:10px; min-width:185px; }.user-avatar { display:grid; place-items:center; flex:0 0 35px; width:35px; height:35px; border-radius:50%; color:#fff; background:linear-gradient(145deg,#196fc8,#54a0f0); font-size:.8rem; font-weight:800; }.user-person strong { display:block; color:#193762; font-size:.88rem; }.user-person small { display:block; margin-top:2px; color:#8290a7; }.user-login { color:#315b95; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.8rem; }.user-role { display:inline-flex; align-items:center; gap:5px; border-radius:999px; padding:5px 8px; font-size:.74rem; font-weight:800; white-space:nowrap; }.user-role--employee { background:#ebf4ff; color:#1a65b7; }.user-role--courier { background:#edfcf4; color:#167447; }.user-role--admin { background:#fff3ec; color:#bd5b1c; }.user-branch { color:#526e95; }.user-branch--none { color:#929eae; }.user-actions { display:flex; justify-content:flex-end; gap:7px; }.user-action { display:inline-flex; align-items:center; gap:5px; min-height:34px; padding:7px 9px; border:1px solid #c9d9ec; border-radius:8px; background:#fff; color:#24528b; font-size:.77rem; font-weight:800; text-decoration:none; }.user-action:hover { background:#f3f8ff; border-color:#1d7cdc; }.user-action--delete { border-color:#ffd0d9; background:#fff8fa; color:#c61d43; }.user-empty { padding:50px 20px; color:#75839a; text-align:center; }.user-empty i { display:block; margin-bottom:8px; color:#e51f4b; font-size:2rem; }
    @media(max-width:1050px) { .user-filter-form { grid-template-columns:1fr 1fr; }.user-filter-actions { justify-content:flex-end; } }
    @media(max-width:680px) { .user-directory { padding:16px; }.user-directory__hero { align-items:flex-start; flex-direction:column; padding:22px; }.user-directory__create { width:100%; justify-content:center; }.user-stats { grid-template-columns:1fr 1fr; }.user-filter-form { grid-template-columns:1fr; }.user-filter-actions { justify-content:stretch; }.user-filter-actions > * { flex:1; }.user-list-panel { padding:15px; }.user-list-panel__head { align-items:flex-start; flex-direction:column; } }
</style>
@endsection

@section('body')
@php
    $selectedRole = $filters['role'];
    $hasFilters = $filters['search'] !== '' || $selectedRole || $filters['filial_id'];
    $createParameters = $selectedRole ? ['role' => $selectedRole] : [];
@endphp
<div class="page-wrapper">
    <main class="user-directory">
        @if (session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
        @if (session('error') || session('danger'))<div class="alert alert-danger mb-3">{{ session('error') ?? session('danger') }}</div>@endif

        <section class="user-directory__hero">
            <div><p class="user-directory__eyebrow">Tizim boshqaruvi</p><h1>Foydalanuvchilar</h1><p>Xodimlar, kuryerlar va filial administratorlarini qidiring, rol yoki filial bo‘yicha filtrlang va boshqaring.</p></div>
            <a class="user-directory__create" href="{{ route('superadmin.create', $createParameters) }}"><i class='bx bx-plus'></i> Yangi foydalanuvchi</a>
        </section>

        <section class="user-stats" aria-label="Foydalanuvchilar statistikasi">
            <a class="user-stat {{ !$selectedRole ? 'user-stat--active' : '' }}" href="{{ route('superadmin.index') }}"><span class="user-stat__icon user-stat__icon--all"><i class='bx bx-group'></i></span><span>Jami foydalanuvchilar<strong>{{ $stats['total'] }}</strong></span></a>
            <a class="user-stat {{ $selectedRole === 'employee' ? 'user-stat--active' : '' }}" href="{{ route('superadmin.index', ['role' => 'employee']) }}"><span class="user-stat__icon"><i class='bx bx-id-card'></i></span><span>Xodimlar<strong>{{ $stats['employee'] }}</strong></span></a>
            <a class="user-stat {{ $selectedRole === 'courier' ? 'user-stat--active' : '' }}" href="{{ route('superadmin.index', ['role' => 'courier']) }}"><span class="user-stat__icon user-stat__icon--courier"><i class='bx bx-cycling'></i></span><span>Kuryerlar<strong>{{ $stats['courier'] }}</strong></span></a>
            <a class="user-stat {{ $selectedRole === 'admin_filial' ? 'user-stat--active' : '' }}" href="{{ route('superadmin.index', ['role' => 'admin_filial']) }}"><span class="user-stat__icon user-stat__icon--admin"><i class='bx bx-buildings'></i></span><span>Filial adminlari<strong>{{ $stats['admin_filial'] }}</strong></span></a>
        </section>

        <section class="user-filters">
            <h2 class="user-filters__title"><i class='bx bx-filter-alt'></i> Foydalanuvchilarni filtrlash</h2>
            <form method="GET" action="{{ route('superadmin.index') }}" class="user-filter-form">
                <div class="user-filter-field"><label for="user_search">Qidiruv</label><input id="user_search" name="search" value="{{ $filters['search'] }}" placeholder="Ism, login yoki telefon"></div>
                <div class="user-filter-field"><label for="user_role">Rol</label><select id="user_role" name="role"><option value="">Barcha rollar</option>@foreach ($roleLabels as $value => $label)<option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="user-filter-field"><label for="user_filial">Filial</label><select id="user_filial" name="filial_id"><option value="">Barcha filiallar</option>@foreach ($filials as $filial)<option value="{{ $filial->id }}" @selected((int) $filters['filial_id'] === $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>
                <div class="user-filter-actions"><button type="submit" class="user-filter-submit"><i class='bx bx-search'></i> Filtrlash</button>@if ($hasFilters)<a class="user-filter-reset" href="{{ route('superadmin.index') }}">Tozalash</a>@endif</div>
            </form>
        </section>

        <section class="user-list-panel">
            <div class="user-list-panel__head"><div><h2>Foydalanuvchilar ro‘yxati</h2><p>{{ $hasFilters ? 'Tanlangan filtrlar bo‘yicha natijalar.' : 'Tizimdagi boshqariladigan barcha foydalanuvchilar.' }}</p></div><span class="user-result-count">{{ $users->count() }} ta natija</span></div>
            <div class="user-table-wrap">
                <table class="user-table"><thead><tr><th>Foydalanuvchi</th><th>Rol</th><th>Login</th><th>Telefon</th><th>Filial</th><th>Qo‘shilgan</th><th>Harakat</th></tr></thead><tbody>
                    @forelse ($users as $user)
                        @php
                            $role = $user->roles->first()?->name;
                            $roleLabel = $roleLabels[$role] ?? $role;
                            $roleClass = $role === 'courier' ? 'courier' : ($role === 'admin_filial' ? 'admin' : 'employee');
                            $phone = preg_replace('/\D/', '', (string) $user->phone);
                            $formattedPhone = preg_replace('/(\d{2})(\d{3})(\d{2})(\d{2})/', '$1 $2 $3 $4', $phone);
                        @endphp
                        <tr>
                            <td><div class="user-person"><span class="user-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><span><strong>{{ $user->name }}</strong><small>ID: {{ $user->id }}</small></span></div></td>
                            <td><span class="user-role user-role--{{ $roleClass }}"><i class='bx bx-shield-quarter'></i>{{ $roleLabel }}</span></td>
                            <td><span class="user-login">{{ $user->login }}</span></td>
                            <td>+998 {{ $formattedPhone }}</td>
                            <td class="{{ $user->filial ? 'user-branch' : 'user-branch--none' }}">{{ $user->filial?->name ?? 'Biriktirilmagan' }}</td>
                            <td>{{ $user->created_at?->format('d.m.Y') ?? '—' }}</td>
                            <td><div class="user-actions"><a class="user-action" href="{{ route('superadmin.edit', $user->id) }}"><i class='bx bx-pencil'></i> Tahrirlash</a><form method="POST" action="{{ route('superadmin.destroy', $user->id) }}" onsubmit="return confirm('Foydalanuvchini o‘chirasizmi?')">@csrf @method('DELETE')<button class="user-action user-action--delete" type="submit"><i class='bx bx-trash'></i> O‘chirish</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="user-empty"><i class='bx bx-search-alt'></i><strong>Foydalanuvchi topilmadi.</strong><br><span>Qidiruv yoki filtr parametrlarini o‘zgartirib ko‘ring.</span></div></td></tr>
                    @endforelse
                </tbody></table>
            </div>
        </section>
    </main>
</div>
@endsection
