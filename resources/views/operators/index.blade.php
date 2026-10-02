@extends('template')

@section('style')
<style>
    .operators-shell { --op-bg: #f3f6fb; --op-card: #fff; --op-ink: #182b49; --op-muted: #697990; --op-line: #e4eaf3; --op-soft: #f8fafd; background: var(--op-bg); min-height: calc(100vh - 84px); color: var(--op-ink); }
    .operators-shell .page-content { padding: 28px; max-width: 1800px; margin: auto; }
    .op-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
    .op-heading h1 { color: var(--op-ink); font-size: 26px; font-weight: 700; margin: 0 0 8px; }
    .op-muted { color: var(--op-muted); }
    .op-source { display: inline-flex; align-items: center; gap: 8px; color: #087ea4; background: #e6f6fc; padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 600; white-space: nowrap; }
    .op-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .op-stat { display: flex; align-items: center; gap: 14px; padding: 20px; border: 1px solid var(--op-line); border-radius: 16px; background: var(--op-card); color: var(--op-ink); box-shadow: 0 4px 18px rgba(24, 43, 73, .03); }
    .op-stat:hover, .op-stat.is-selected { color: var(--op-ink); border-color: #719ae6; box-shadow: 0 4px 18px rgba(50, 96, 175, .08); }
    .op-stat-icon { display: grid; place-items: center; height: 48px; width: 48px; flex-shrink: 0; background: #edf2fc; color: #4568bd; font-size: 24px; border-radius: 14px; }
    .op-stat.new .op-stat-icon { background: #fff4de; color: #b87605; }
    .op-stat.contacted .op-stat-icon { background: #e9f1ff; color: #326acf; }
    .op-stat.resolved .op-stat-icon { background: #e2f6ef; color: #168060; }
    .op-stat-label { color: var(--op-muted); font-size: 13px; display: block; margin-bottom: 4px; }
    .op-stat strong { font-size: 26px; line-height: 1.1; }
    .op-panel { border: 1px solid var(--op-line); border-radius: 18px; background: var(--op-card); box-shadow: 0 8px 28px rgba(24, 43, 73, .04); overflow: hidden; }
    .op-toolbar { display: flex; align-items: end; gap: 12px; padding: 20px; flex-wrap: wrap; border-bottom: 1px solid var(--op-line); }
    .op-search { flex: 1; min-width: 220px; }
    .op-toolbar label { font-size: 12px; font-weight: 600; color: var(--op-muted); display: block; margin-bottom: 7px; }
    .op-toolbar .form-control, .op-toolbar .form-select, .op-actions .form-select { background-color: var(--op-card); color: var(--op-ink); border-color: var(--op-line); border-radius: 10px; }
    .op-toolbar .form-control, .op-toolbar .form-select, .op-toolbar .btn { min-height: 42px; }
    .op-toolbar .btn { border-radius: 10px; }
    .operators-shell .table-responsive { white-space: normal; }
    .op-table { color: var(--op-ink); margin: 0; width: 100%; min-width: 1060px; table-layout: fixed; }
    .op-table th:nth-child(1) { width: 18%; }
    .op-table th:nth-child(2) { width: 15%; }
    .op-table th:nth-child(3) { width: 25%; }
    .op-table th:nth-child(4) { width: 11%; }
    .op-table th:nth-child(5) { width: 12%; }
    .op-table th:nth-child(6) { width: 19%; }
    .op-table thead th { background: var(--op-soft); color: var(--op-muted); font-size: 11px; letter-spacing: .4px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid var(--op-line); padding: 15px 20px; white-space: nowrap; }
    .op-table tbody td { padding: 20px 16px; border-color: var(--op-line); vertical-align: top; }
    .op-table tbody tr:hover { background: var(--op-soft); }
    .op-person { display: flex; align-items: start; gap: 11px; min-width: 0; overflow-wrap: anywhere; }
    .op-avatar { height: 40px; width: 40px; display: grid; place-items: center; flex-shrink: 0; border-radius: 12px; background: #eaf0fc; color: #466bb1; font-weight: 700; }
    .op-person strong { display: block; font-size: 14px; margin-bottom: 5px; }
    .op-person small { color: var(--op-muted); font-size: 12px; display: block; }
    .op-contact { min-width: 150px; }
    .op-phone { font-weight: 600; color: var(--op-ink); white-space: nowrap; }
    .op-verified { color: #168060; font-size: 11px; margin-top: 7px; display: block; }
    .op-message { max-width: 430px; }
    .op-message-preview { line-height: 1.6; font-size: 13px; overflow-wrap: anywhere; }
    .op-message details { margin-top: 8px; font-size: 12px; }
    .op-message summary { cursor: pointer; color: #326acf; font-weight: 600; }
    .op-full-text { white-space: pre-wrap; overflow-wrap: anywhere; background: var(--op-soft); border: 1px solid var(--op-line); border-radius: 10px; padding: 12px; margin: 10px 0 0; line-height: 1.6; }
    .op-date { min-width: 120px; font-size: 13px; white-space: nowrap; }
    .op-date small { display: block; margin-top: 5px; color: var(--op-muted); }
    .op-badge { display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .op-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .op-badge.new { color: #9a6506; background: #fff2d5; }
    .op-badge.contacted { color: #326acf; background: #e9f1ff; }
    .op-badge.resolved { color: #168060; background: #e2f6ef; }
    .op-actions { min-width: 170px; }
    .op-actions form { display: flex; gap: 6px; }
    .op-actions .form-select { font-size: 12px; min-width: 112px; }
    .op-actions .btn { border-radius: 9px; }
    .op-handler { color: var(--op-muted); font-size: 11px; margin-top: 9px; }
    .op-empty { text-align: center; padding: 64px 20px !important; }
    .op-empty i { color: #8195b5; font-size: 48px; display: block; margin-bottom: 14px; }
    .op-empty h2 { font-size: 18px; color: var(--op-ink); }
    .op-footer { padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; border-top: 1px solid var(--op-line); color: var(--op-muted); font-size: 12px; }
    .op-footer .pagination { margin-bottom: 0; }
    html.dark-theme .operators-shell { --op-bg: #171d2b; --op-card: #222b3c; --op-ink: #e0e8f6; --op-muted: #a2b1c9; --op-line: #34415a; --op-soft: #283347; }
    html.dark-theme .op-table { --bs-table-bg: transparent; --bs-table-color: var(--op-ink); }
    html.dark-theme .op-source { background: #223c50; color: #89d8f3; }
    html.dark-theme .op-phone { color: var(--op-ink); }
    html.dark-theme .op-verified { color: #75d4b3; }
    @media (max-width: 1000px) { .op-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 768px) { body:has(.operators-shell) .topbar .navbar { height: auto !important; } }
    @media (max-width: 576px) {
        .operators-shell .page-content { padding: 18px 12px; }
        .op-heading { align-items: start; flex-direction: column; }
        .op-heading h1 { font-size: 23px; }
        .op-stats { gap: 10px; }
        .op-stat { padding: 14px 10px; gap: 9px; }
        .op-stat-icon { width: 35px; height: 35px; font-size: 19px; border-radius: 10px; }
        .op-stat strong { font-size: 23px; }
        .op-toolbar { padding: 16px; }
        .op-search { min-width: 100%; }
    }
</style>
@endsection

@section('body')
<div class="page-wrapper operators-shell">
    <div class="page-content">
        <div class="op-heading">
            <div>
                <h1>Operatorlar</h1>
                <p class="op-muted mb-0">Telegram orqali kelgan murojaatlar va mijoz bilan bog‘lanish holati.</p>
            </div>
            <span class="op-source"><i class="bx bxl-telegram" aria-hidden="true"></i> Telegram murojaatlari</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="op-stats">
            <a href="{{ route('operators.index') }}" class="op-stat {{ $selectedStatus === '' ? 'is-selected' : '' }}">
                <span class="op-stat-icon"><i class="bx bx-conversation" aria-hidden="true"></i></span>
                <div><span class="op-stat-label">Jami murojaatlar</span><strong>{{ $counts->sum() }}</strong></div>
            </a>
            @foreach(['new' => 'bx-message-rounded-add', 'contacted' => 'bx-phone-call', 'resolved' => 'bx-check-circle'] as $status => $icon)
                <a href="{{ route('operators.index', ['status' => $status]) }}" class="op-stat {{ $status }} {{ $selectedStatus === $status ? 'is-selected' : '' }}">
                    <span class="op-stat-icon"><i class="bx {{ $icon }}" aria-hidden="true"></i></span>
                    <div><span class="op-stat-label">{{ $statuses[$status] }}</span><strong>{{ $counts[$status] ?? 0 }}</strong></div>
                </a>
            @endforeach
        </div>

        <div class="op-panel">
            <form method="GET" action="{{ route('operators.index') }}" class="op-toolbar">
                <div class="op-search">
                    <label for="operator-search">Murojaatni qidirish</label>
                    <input id="operator-search" type="search" name="q" value="{{ $search }}" maxlength="120" class="form-control" placeholder="Ism, telefon, Telegram yoki murojaat matni">
                </div>
                <div>
                    <label for="operator-status">Holat</label>
                    <select id="operator-status" name="status" class="form-select">
                        <option value="">Barcha holatlar</option>
                        @foreach($statuses as $status => $label)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary"><i class="bx bx-search me-1" aria-hidden="true"></i> Qidirish</button>
                @if($search !== '' || $selectedStatus !== '')
                    <a href="{{ route('operators.index') }}" class="btn btn-outline-secondary">Tozalash</a>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table op-table">
                    <caption class="visually-hidden">Operatorga yuborilgan mijoz murojaatlari</caption>
                    <thead><tr>
                        <th scope="col">Mijoz</th><th scope="col">Kontakt</th><th scope="col">Murojaat</th>
                        <th scope="col">Kelgan vaqti</th><th scope="col">Holat</th><th scope="col">Boshqarish</th>
                    </tr></thead>
                    <tbody>
                    @forelse($requests as $item)
                        <tr>
                            <td>
                                <div class="op-person">
                                    <span class="op-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->telegram_username && preg_match('/^[A-Za-z0-9_]{5,32}$/', $item->telegram_username))
                                            <a href="https://t.me/{{ $item->telegram_username }}" target="_blank" rel="noopener noreferrer">{{ '@'.$item->telegram_username }}</a>
                                        @else
                                            <small>Telegram orqali</small>
                                        @endif
                                        <small class="mt-1">Murojaat #{{ $item->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="op-contact">
                                @if($item->phone)
                                    <a class="op-phone" href="tel:{{ preg_replace('/[^+0-9]/', '', $item->phone) }}">{{ $item->phone }}</a>
                                    @if($item->phone_verified)
                                        <span class="op-verified"><i class="bx bx-check-shield" aria-hidden="true"></i> Kontakt tasdiqlangan</span>
                                    @endif
                                @else
                                    <span class="op-muted">Kontakt yuborilmagan</span>
                                @endif
                            </td>
                            <td class="op-message">
                                <div class="op-message-preview">{{ \Illuminate\Support\Str::limit($item->message, 140) }}</div>
                                @if(mb_strlen($item->message) > 140)
                                    <details><summary>To‘liq murojaatni o‘qish</summary><p class="op-full-text">{{ $item->message }}</p></details>
                                @endif
                            </td>
                            <td class="op-date">
                                {{ $item->created_at->timezone('Asia/Tashkent')->format('d.m.Y') }}
                                <small>{{ $item->created_at->timezone('Asia/Tashkent')->format('H:i') }}</small>
                            </td>
                            <td><span class="op-badge {{ $item->status }}">{{ $statuses[$item->status] ?? $item->status }}</span></td>
                            <td class="op-actions">
                                <form method="POST" action="{{ route('operators.update', $item) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" class="form-select form-select-sm" aria-label="Murojaat #{{ $item->id }} holati">
                                        @foreach($statuses as $status => $label)
                                            <option value="{{ $status }}" @selected($item->status === $status)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" aria-label="Murojaat #{{ $item->id }} holatini saqlash" title="Saqlash"><i class="bx bx-check" aria-hidden="true"></i></button>
                                </form>
                                @if($item->handledBy)
                                    <div class="op-handler">{{ $item->handledBy->name }}<br>{{ $item->handled_at?->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="op-empty">
                            <i class="bx bx-conversation" aria-hidden="true"></i>
                            <h2>{{ $search !== '' || $selectedStatus !== '' ? 'Mos murojaat topilmadi' : 'Hozircha murojaatlar yo‘q' }}</h2>
                            <p class="op-muted mb-0">{{ $search !== '' || $selectedStatus !== '' ? 'Qidiruv yoki holat filtrini o‘zgartiring.' : 'Mijoz botdagi «Operator» tugmasi orqali yuborgan murojaatlar shu yerda ko‘rinadi.' }}</p>
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="op-footer">
                <span>{{ $requests->total() ? $requests->firstItem().'–'.$requests->lastItem() : '0' }} / {{ $requests->total() }} murojaat</span>
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
