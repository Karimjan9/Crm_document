@extends('template')

@section('style')
<style>
    .risk-page { padding: 26px; color: #1c2f4d; }
    .risk-hero, .risk-panel, .risk-stat { border: 1px solid #dce7f5; border-radius: 18px; background: #fff; box-shadow: 0 7px 20px rgba(31, 63, 110, .06); }
    .risk-hero { display: flex; align-items: center; justify-content: space-between; gap: 22px; padding: 27px 29px; background: linear-gradient(115deg, #fff 0%, #f4f8ff 100%); }
    .risk-eyebrow { margin: 0 0 5px; color: #d52e51; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .risk-hero h1 { margin: 0; color: #183962; font-size: 1.5rem; font-weight: 800; }
    .risk-hero p { max-width: 660px; margin: 7px 0 0; color: #6d7f9b; font-size: .9rem; }
    .risk-hero-links { display: flex; flex-wrap: wrap; gap: 9px; justify-content: flex-end; }
    .risk-link, .risk-button, .risk-clear { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 14px; border: 1px solid #c9d9ec; border-radius: 9px; background: #fff; color: #29568c; font-size: .8rem; font-weight: 800; text-decoration: none; }
    .risk-link--primary, .risk-button { border-color: #1e73c9; background: #1d73cb; color: #fff; }
    .risk-explainer { display: flex; align-items: flex-start; gap: 11px; margin: 18px 0; padding: 14px 17px; border: 1px solid #f4d7dd; border-radius: 13px; background: #fff8f9; color: #6a4b58; font-size: .83rem; line-height: 1.5; }
    .risk-explainer i { display: grid; place-items: center; flex: 0 0 31px; width: 31px; height: 31px; border-radius: 50%; color: #d52e51; background: #ffe5eb; font-size: 1rem; }
    .risk-explainer strong { color: #9d2441; }
    .risk-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .risk-stat { display: flex; align-items: center; gap: 12px; padding: 16px; }
    .risk-stat i { display: grid; place-items: center; flex: 0 0 39px; width: 39px; height: 39px; border-radius: 11px; color: #ca274a; background: #fff0f3; font-size: 1.15rem; }
    .risk-stat--high i { color: #bc1f3f; background: #ffe7ed; }.risk-stat--medium i { color: #a56810; background: #fff4dc; }.risk-stat--branches i { color: #1873c9; background: #eaf4ff; }
    .risk-stat span { display: block; color: #72819a; font-size: .73rem; font-weight: 700; }.risk-stat strong { display: block; margin-top: 3px; color: #193963; font-size: 1.1rem; line-height: 1.1; }
    .risk-panel { overflow: hidden; }.risk-panel-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; padding: 19px 20px 16px; border-bottom: 1px solid #e8eef7; }
    .risk-panel-head h2 { display: flex; align-items: center; gap: 8px; margin: 0; color: #1a3962; font-size: 1rem; font-weight: 800; }.risk-panel-head h2 i { color: #d52e51; }
    .risk-panel-head p { margin: 4px 0 0; color: #75839a; font-size: .8rem; }.risk-count { color: #506e98; font-size: .78rem; font-weight: 800; white-space: nowrap; }
    .risk-filter, .risk-scan { display: grid; grid-template-columns: minmax(220px, 1fr) auto; gap: 12px; align-items: end; padding: 17px 20px; }
    .risk-scan { grid-template-columns: repeat(3, minmax(0, 1fr)) auto; border-bottom: 1px solid #e8eef7; background: #fbfdff; }
    .risk-field { display: grid; gap: 6px; }.risk-field label { color: #526a8d; font-size: .74rem; font-weight: 800; }
    .risk-field input, .risk-field select { width: 100%; height: 41px; border: 1px solid #cedbeb; border-radius: 9px; padding: 0 10px; background: #fff; color: #2c4162; outline: none; }
    .risk-field input:focus, .risk-field select:focus { border-color: #1e7fdd; box-shadow: 0 0 0 3px rgba(30,127,221,.12); }
    .risk-filter-actions { display: flex; gap: 8px; }.risk-clear { color: #58708f; }
    .risk-table-wrap { overflow-x: auto; }.risk-table { width: 100%; min-width: 890px; border-collapse: separate; border-spacing: 0; }
    .risk-table th { padding: 11px 13px; border-bottom: 1px solid #e4ebf5; background: #f7faff; color: #61728d; font-size: .7rem; font-weight: 800; letter-spacing: .03em; text-align: left; text-transform: uppercase; }
    .risk-table td { padding: 14px 13px; border-bottom: 1px solid #edf1f7; color: #40536f; font-size: .83rem; vertical-align: middle; }.risk-table tbody tr:hover { background: #fbfdff; }.risk-table tbody tr:last-child td { border-bottom: 0; }
    .risk-signal { max-width: 420px; color: #28476e; font-weight: 700; line-height: 1.45; }.risk-type { display: inline-flex; margin-top: 5px; color: #667c9d; font-size: .72rem; font-weight: 700; }
    .risk-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 999px; font-size: .71rem; font-weight: 800; white-space: nowrap; }.risk-badge--high { color: #bd2041; background: #ffecef; }.risk-badge--medium { color: #9c6210; background: #fff4df; }.risk-badge--low { color: #4e668a; background: #edf3fa; }
    .risk-money { color: #1e4679; font-weight: 800; white-space: nowrap; }.risk-date { color: #718099; font-size: .78rem; white-space: nowrap; }.risk-action { min-height: 33px; border: 1px solid #bde2ce; border-radius: 8px; padding: 6px 9px; color: #157047; background: #f4fcf7; font-size: .73rem; font-weight: 800; }.risk-readonly { color: #7a899e; font-size: .75rem; font-weight: 700; }
    .risk-empty { padding: 46px 18px; color: #74829a; text-align: center; }.risk-empty i { display: block; margin-bottom: 8px; color: #d52e51; font-size: 1.75rem; }.risk-empty strong { display: block; color: #3c5579; }
    @media (max-width: 1120px) { .risk-scan { grid-template-columns: repeat(2, minmax(0, 1fr)); }.risk-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .risk-page { padding: 16px; }.risk-hero { align-items: flex-start; flex-direction: column; padding: 22px; }.risk-hero-links { width: 100%; }.risk-link { flex: 1; }.risk-stats, .risk-scan, .risk-filter { grid-template-columns: 1fr; }.risk-filter-actions { width: 100%; }.risk-filter-actions > * { flex: 1; }.risk-panel-head { align-items: flex-start; flex-direction: column; } }
</style>
@endsection

@section('body')
@php
    $canManageSignals = auth()->user()->hasRole('admin_manager');
    $typeLabels = [
        'large_discount' => 'Katta chegirma',
        'free_addon' => 'Bepul qo‘shimcha xizmat',
        'addon_price_mismatch' => 'Narx nomuvofiqligi',
        'unpaid_completion' => 'To‘lanmagan yakun',
        'negative_margin' => 'Manfiy marja',
        'branch_expense_spike' => 'Xarajat oshishi',
        'worker_rework' => 'Qayta ishlash ko‘pligi',
        'cash_variance' => 'Kassa farqi',
    ];
    $severityLabels = ['high' => 'Yuqori xavf', 'medium' => 'O‘rta xavf', 'low' => 'Past xavf'];
    $highCount = $leaks->where('severity', 'high')->count();
    $mediumCount = $leaks->where('severity', 'medium')->count();
    $branchCount = $leaks->pluck('filial_id')->filter()->unique()->count();
@endphp

<div class="page-wrapper">
    <main class="risk-page">
        @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger mb-3">{{ $errors->first() }}</div>@endif

        <section class="risk-hero">
            <div>
                <p class="risk-eyebrow">Moliyaviy boshqaruv</p>
                <h1>Moliyaviy xavflar monitori</h1>
                <p>Chegirma, narx, qarzdorlik, xarajat va kassa ma’lumotlaridagi shubhali holatlarni bir joydan kuzating.</p>
            </div>
            <div class="risk-hero-links">
                <a class="risk-link" href="{{ route('finance.ledger.index') }}"><i class='bx bx-receipt'></i> To‘lovlar reyestri</a>
                <a class="risk-link risk-link--primary" href="{{ route('finance.dashboard') }}"><i class='bx bx-line-chart'></i> Moliya boshqaruvi</a>
            </div>
        </section>

        <section class="risk-explainer">
            <i class='bx bx-info-circle'></i>
            <div><strong>Bu signal, hukm emas.</strong> Har bir qayd tekshiruv talab qiladigan holatni bildiradi. Sabab tasdiqlanib bartaraf etilgach, admin manager signalni yopadi.</div>
        </section>

        <section class="risk-stats">
            <article class="risk-stat"><i class='bx bx-radar'></i><span>Ochiq signallar<strong>{{ $leaks->count() }} ta</strong></span></article>
            <article class="risk-stat risk-stat--high"><i class='bx bx-error-circle'></i><span>Yuqori xavf<strong>{{ $highCount }} ta</strong></span></article>
            <article class="risk-stat risk-stat--medium"><i class='bx bx-error'></i><span>O‘rta xavf<strong>{{ $mediumCount }} ta</strong></span></article>
            <article class="risk-stat risk-stat--branches"><i class='bx bx-buildings'></i><span>Ta’sirlangan filiallar<strong>{{ $branchCount }} ta</strong></span></article>
        </section>

        <section class="risk-panel">
            @if($canManageSignals)
                <form class="risk-scan" method="POST" action="{{ route('finance.margin-leaks.scan') }}">
                    @csrf
                    <div class="risk-field"><label for="scan-from">Tekshiruv boshi</label><input id="scan-from" type="date" name="date_from" value="{{ today()->startOfMonth()->toDateString() }}"></div>
                    <div class="risk-field"><label for="scan-to">Tekshiruv oxiri</label><input id="scan-to" type="date" name="date_to" value="{{ today()->toDateString() }}"></div>
                    <div class="risk-field"><label for="scan-branch">Filial</label><select id="scan-branch" name="filial_id"><option value="">Barcha filiallar</option>@foreach($filials as $filial)<option value="{{ $filial->id }}">{{ $filial->name }}</option>@endforeach</select></div>
                    <button class="risk-button" type="submit"><i class='bx bx-radar'></i> Tekshirish</button>
                </form>
            @endif

            <div class="risk-panel-head">
                <div><h2><i class='bx bx-shield-quarter'></i> Ochiq signallar</h2><p>Tanlangan filial bo‘yicha hali yopilmagan tekshiruvlar.</p></div>
                <span class="risk-count">{{ $leaks->count() }} ta signal</span>
            </div>

            <form class="risk-filter" method="GET" action="{{ route('finance.margin-leaks.index') }}">
                <div class="risk-field"><label for="filter-branch">Filial bo‘yicha ko‘rish</label><select id="filter-branch" name="filial_id"><option value="">Barcha filiallar</option>@foreach($filials as $filial)<option value="{{ $filial->id }}" @selected((string) request('filial_id') === (string) $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>
                <div class="risk-filter-actions"><button class="risk-button" type="submit"><i class='bx bx-filter-alt'></i> Filtrlash</button>@if(request('filial_id'))<a class="risk-clear" href="{{ route('finance.margin-leaks.index') }}">Tozalash</a>@endif</div>
            </form>

            <div class="risk-table-wrap">
                <table class="risk-table">
                    <thead><tr><th>Xavf darajasi</th><th>Signal</th><th>Filial</th><th>Buyurtma</th><th>Qiymat</th><th>Aniqlangan vaqt</th><th>Holat</th></tr></thead>
                    <tbody>
                        @forelse($leaks as $leak)
                            @php($severityClass = in_array($leak->severity, ['high', 'medium'], true) ? $leak->severity : 'low')
                            <tr>
                                <td><span class="risk-badge risk-badge--{{ $severityClass }}"><i class='bx {{ $severityClass === 'high' ? 'bx-error-circle' : ($severityClass === 'medium' ? 'bx-error' : 'bx-info-circle') }}'></i>{{ $severityLabels[$leak->severity] ?? $leak->severity }}</span></td>
                                <td><div class="risk-signal">{{ $leak->message }}</div><span class="risk-type">{{ $typeLabels[$leak->leak_type] ?? $leak->leak_type }}</span></td>
                                <td>{{ $leak->filial?->name ?: '—' }}</td>
                                <td>{{ $leak->order?->order_code ?: '—' }}</td>
                                <td class="risk-money">@if($leak->leak_type === 'worker_rework'){{ number_format($leak->amount, 0, ',', ' ') }} marta@elseif($leak->amount !== null){{ number_format($leak->amount, 0, ',', ' ') }} so‘m@else—@endif</td>
                                <td class="risk-date">{{ optional($leak->detected_at)->format('d.m.Y H:i') }}</td>
                                <td>@if($canManageSignals)<form method="POST" action="{{ route('finance.margin-leaks.resolve', $leak) }}">@csrf<button class="risk-action" type="submit">Tekshirildi, yopish</button></form>@else<span class="risk-readonly">Kuzatuv rejimi</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="risk-empty"><i class='bx bx-check-shield'></i><strong>Ochiq moliyaviy xavf topilmadi.</strong><span>Tekshiruv davrini tanlab, “Tekshirish” tugmasini bosing.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
