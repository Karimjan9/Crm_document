@extends('template')

@section('style')
<style>
    .finance-dashboard { padding: 26px; color: #1c2f4d; }
    .finance-hero, .finance-panel, .finance-kpi, .finance-profit-card { border: 1px solid #dce7f5; border-radius: 18px; background: #fff; box-shadow: 0 7px 20px rgba(31,63,110,.06); }
    .finance-hero { display: flex; align-items: center; justify-content: space-between; gap: 22px; padding: 27px 29px; background: linear-gradient(115deg, #fff 0%, #f3f8ff 100%); }
    .finance-eyebrow { margin: 0 0 5px; color: #d52e51; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .finance-hero h1 { margin: 0; color: #183962; font-size: 1.5rem; font-weight: 800; }.finance-hero p { margin: 7px 0 0; color: #6d7f9b; font-size: .9rem; }
    .finance-nav { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }.finance-link { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 13px; border: 1px solid #c9d9ec; border-radius: 9px; color: #28568e; background: #fff; font-size: .79rem; font-weight: 800; text-decoration: none; }.finance-link--risk { border-color: #f1c7d2; color: #bd2847; background: #fff9fa; }
    .finance-filter { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) auto; gap: 12px; align-items: end; margin: 18px 0; padding: 17px 20px; border: 1px solid #dce7f5; border-radius: 15px; background: #fff; box-shadow: 0 6px 18px rgba(31,63,110,.04); }
    .finance-field { display: grid; gap: 6px; }.finance-field label { color: #526a8d; font-size: .74rem; font-weight: 800; }.finance-field input, .finance-field select { width: 100%; height: 41px; padding: 0 10px; border: 1px solid #cedbeb; border-radius: 9px; color: #2c4162; background: #fbfdff; outline: none; }.finance-field input:focus, .finance-field select:focus { border-color: #1e7fdd; box-shadow: 0 0 0 3px rgba(30,127,221,.12); background: #fff; }
    .finance-filter-actions { display: flex; gap: 8px; }.finance-filter-button, .finance-clear { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 41px; padding: 0 14px; border: 0; border-radius: 9px; background: #1e73cb; color: #fff; font-size: .8rem; font-weight: 800; white-space: nowrap; }.finance-clear { border: 1px solid #c9d9ec; color: #58708f; background: #fff; text-decoration: none; }
    .finance-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }.finance-kpi { display: flex; align-items: center; gap: 12px; padding: 16px; }.finance-kpi i { display: grid; place-items: center; flex: 0 0 40px; width: 40px; height: 40px; border-radius: 12px; color: #1671c9; background: #eaf4ff; font-size: 1.2rem; }.finance-kpi--paid i { color: #168950; background: #edfcf4; }.finance-kpi--debt i { color: #a56810; background: #fff4df; }.finance-kpi--cost i { color: #bd2847; background: #fff0f3; }.finance-kpi--expense i { color: #8759b1; background: #f4effb; }.finance-kpi--profit i { color: #147a4a; background: #eafbf2; }.finance-kpi--loss i { color: #bd2847; background: #fff0f3; }
    .finance-kpi span { display: block; color: #72819a; font-size: .73rem; font-weight: 700; }.finance-kpi strong { display: block; margin-top: 3px; color: #193963; font-size: 1.03rem; line-height: 1.15; }.finance-kpi small { display: block; margin-top: 3px; color: #8190a5; font-size: .71rem; }
    .finance-overview { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(290px, .75fr); gap: 18px; margin-bottom: 18px; }.finance-panel { overflow: hidden; }.finance-panel-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; padding: 19px 20px 16px; border-bottom: 1px solid #e8eef7; }.finance-panel-head h2 { display: flex; align-items: center; gap: 8px; margin: 0; color: #1a3962; font-size: 1rem; font-weight: 800; }.finance-panel-head h2 i { color: #d52e51; }.finance-panel-head p { margin: 4px 0 0; color: #75839a; font-size: .8rem; }.finance-count { color: #506e98; font-size: .77rem; font-weight: 800; white-space: nowrap; }
    .finance-flow { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; padding: 23px 20px; }.finance-flow-step { position: relative; padding: 4px 14px; border-right: 1px solid #e5edf7; }.finance-flow-step:first-child { padding-left: 0; }.finance-flow-step:last-child { border: 0; }.finance-flow-step span { display: block; color: #71819a; font-size: .71rem; font-weight: 800; }.finance-flow-step strong { display: block; margin-top: 7px; color: #1e426f; font-size: .95rem; line-height: 1.15; }.finance-flow-step--cost strong { color: #bd2847; }.finance-flow-step--profit strong { color: #147a4a; }
    .finance-profit-card { display: flex; flex-direction: column; justify-content: center; padding: 24px; background: linear-gradient(145deg, #163d70, #245f9e); color: #fff; }.finance-profit-card--loss { background: linear-gradient(145deg, #8e2946, #c73a59); }.finance-profit-card p { margin: 0; color: #c9dcf2; font-size: .75rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }.finance-profit-card h2 { margin: 10px 0 4px; color: #fff; font-size: 1.55rem; font-weight: 800; }.finance-profit-card span { display: inline-flex; width: fit-content; margin-top: 5px; padding: 6px 9px; border-radius: 999px; color: #fff; background: rgba(255,255,255,.15); font-size: .77rem; font-weight: 800; }.finance-profit-card small { margin-top: 18px; color: #d6e5f7; font-size: .78rem; }
    .finance-tables { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 18px; }.finance-table-wrap { overflow-x: auto; }.finance-table { width: 100%; min-width: 625px; border-collapse: separate; border-spacing: 0; }.finance-table th { padding: 11px 12px; border-bottom: 1px solid #e4ebf5; background: #f7faff; color: #61728d; font-size: .69rem; font-weight: 800; letter-spacing: .03em; text-align: left; text-transform: uppercase; }.finance-table td { padding: 13px 12px; border-bottom: 1px solid #edf1f7; color: #40536f; font-size: .82rem; vertical-align: middle; }.finance-table tbody tr:hover { background: #fbfdff; }.finance-table tbody tr:last-child td { border-bottom: 0; }.finance-branch { color: #215896; font-weight: 800; text-decoration: none; }.finance-branch small, .finance-service small { display: block; margin-top: 2px; color: #7c8ba1; font-size: .71rem; font-weight: 500; }.finance-money { color: #274a76; font-weight: 700; white-space: nowrap; }.finance-profit { color: #147a4a; font-weight: 800; white-space: nowrap; }.finance-loss { color: #bd2847; font-weight: 800; white-space: nowrap; }.finance-margin { display: inline-flex; padding: 5px 8px; border-radius: 999px; color: #17774b; background: #edfcf4; font-size: .72rem; font-weight: 800; }.finance-margin--low { color: #a56810; background: #fff4df; }.finance-margin--loss { color: #bd2847; background: #fff0f3; }.finance-empty { padding: 42px 18px; color: #73829a; text-align: center; }.finance-empty i { display: block; margin-bottom: 8px; color: #d52e51; font-size: 1.7rem; }.finance-empty strong { display: block; color: #3b5579; }
    @media(max-width: 1250px) { .finance-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }.finance-overview, .finance-tables { grid-template-columns: 1fr; }.finance-flow { grid-template-columns: repeat(2, 1fr); row-gap: 18px; }.finance-flow-step:nth-child(2) { border-right: 0; }.finance-flow-step:nth-child(3) { padding-left: 0; } }
    @media(max-width: 760px) { .finance-dashboard { padding: 16px; }.finance-hero { align-items: flex-start; flex-direction: column; padding: 22px; }.finance-nav { width: 100%; }.finance-link { flex: 1; }.finance-filter, .finance-kpis { grid-template-columns: 1fr; }.finance-filter-actions { width: 100%; }.finance-filter-actions > * { flex: 1; }.finance-flow { grid-template-columns: 1fr; padding: 18px; }.finance-flow-step, .finance-flow-step:nth-child(2) { padding: 10px 0; border-right: 0; border-bottom: 1px solid #e5edf7; }.finance-flow-step:last-child { border-bottom: 0; } }
    .finance-overview { grid-template-columns: minmax(0, 1fr); }
</style>
@endsection

@section('body')
@php
    $kpis = $dashboard['kpis'];
    $period = $dashboard['period'];
    $hasFilters = request('filial_id') || request('date_from') || request('date_to');
    $profitIsPositive = (float) $kpis['gross_profit'] >= 0;
@endphp

<div class="page-wrapper">
    <main class="finance-dashboard">
        <section class="finance-hero">
            <div>
                <p class="finance-eyebrow">Moliyaviy boshqaruv</p>
                <h1>Moliya ko‘rsatkichlari</h1>
                <p>Filiallar va xizmatlar bo‘yicha tushum, xarajat, qarzdorlik hamda sof foydani nazorat qiling.</p>
            </div>
            <nav class="finance-nav">
                <a class="finance-link" href="{{ route('finance.filials.index') }}"><i class='bx bx-buildings'></i> Filial rentabelligi</a>
                <a class="finance-link" href="{{ route('finance.ledger.index') }}"><i class='bx bx-receipt'></i> To‘lovlar reyestri</a>
                <a class="finance-link finance-link--risk" href="{{ route('finance.margin-leaks.index') }}"><i class='bx bx-error-circle'></i> Moliyaviy xavflar</a>
            </nav>
        </section>

        <form class="finance-filter" method="GET" action="{{ route('finance.dashboard') }}">
            <div class="finance-field"><label for="date-from">Davr boshi</label><input id="date-from" type="date" name="date_from" value="{{ $period['from'] }}"></div>
            <div class="finance-field"><label for="date-to">Davr oxiri</label><input id="date-to" type="date" name="date_to" value="{{ $period['to'] }}"></div>
            <div class="finance-field"><label for="branch">Filial</label><select id="branch" name="filial_id"><option value="">Barcha filiallar</option>@foreach($filials as $filial)<option value="{{ $filial->id }}" @selected((string) request('filial_id') === (string) $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>
            <div class="finance-filter-actions"><button class="finance-filter-button" type="submit"><i class='bx bx-filter-alt'></i> Ko‘rsatish</button>@if($hasFilters)<a class="finance-clear" href="{{ route('finance.dashboard') }}">Tozalash</a>@endif</div>
        </form>

        <section class="finance-kpis">
            <article class="finance-kpi"><i class='bx bx-trending-up'></i><span>Buyurtmalar tushumi<strong>{{ number_format($kpis['revenue'], 0, ',', ' ') }} so‘m</strong><small>{{ $kpis['order_count'] }} ta buyurtma</small></span></article>
            <article class="finance-kpi finance-kpi--paid"><i class='bx bx-check-circle'></i><span>Qabul qilingan to‘lov<strong>{{ number_format($kpis['paid'], 0, ',', ' ') }} so‘m</strong><small>Qaytarim: {{ number_format($kpis['refunds'], 0, ',', ' ') }} so‘m</small></span></article>
            <article class="finance-kpi finance-kpi--debt"><i class='bx bx-wallet'></i><span>Qarzdorlik<strong>{{ number_format($kpis['outstanding'], 0, ',', ' ') }} so‘m</strong><small>To‘liq to‘lanmagan buyurtmalar</small></span></article>
            <article class="finance-kpi finance-kpi--cost"><i class='bx bx-purchase-tag-alt'></i><span>Bevosita xarajat<strong>{{ number_format($kpis['direct_cost'], 0, ',', ' ') }} so‘m</strong><small>Xizmat tannarxi va bevosita chiqim</small></span></article>
            <article class="finance-kpi finance-kpi--expense"><i class='bx bx-building-house'></i><span>Filial xarajatlari<strong>{{ number_format($kpis['branch_expense'], 0, ',', ' ') }} so‘m</strong><small>Operatsion va boshqaruv chiqimlari</small></span></article>
            <article class="finance-kpi {{ $profitIsPositive ? 'finance-kpi--profit' : 'finance-kpi--loss' }}"><i class='bx {{ $profitIsPositive ? 'bx-line-chart' : 'bx-trending-down' }}'></i><span>Sof foyda<strong>{{ number_format($kpis['gross_profit'], 0, ',', ' ') }} so‘m</strong><small>Marja: {{ number_format($kpis['gross_margin'], 1, ',', ' ') }}%</small></span></article>
        </section>

        <section class="finance-overview">
            <section class="finance-panel">
                <div class="finance-panel-head"><div><h2><i class='bx bx-git-branch'></i> Foyda shakllanishi</h2><p>Buyurtma tushumidan xarajatlar ayrilgach hosil bo‘ladigan natija.</p></div><span class="finance-count">{{ $period['from'] }} — {{ $period['to'] }}</span></div>
                <div class="finance-flow">
                    <div class="finance-flow-step"><span>Tushum</span><strong>{{ number_format($kpis['revenue'], 0, ',', ' ') }} so‘m</strong></div>
                    <div class="finance-flow-step finance-flow-step--cost"><span>Bevosita xarajat</span><strong>− {{ number_format($kpis['direct_cost'], 0, ',', ' ') }} so‘m</strong></div>
                    <div class="finance-flow-step finance-flow-step--cost"><span>Filial xarajati</span><strong>− {{ number_format($kpis['branch_expense'], 0, ',', ' ') }} so‘m</strong></div>
                    <div class="finance-flow-step finance-flow-step--profit"><span>Sof foyda</span><strong>{{ number_format($kpis['gross_profit'], 0, ',', ' ') }} so‘m</strong></div>
                </div>
            </section>
        </section>

        <section class="finance-tables">
            <section class="finance-panel">
                <div class="finance-panel-head"><div><h2><i class='bx bx-buildings'></i> Filial rentabelligi</h2><p>Har bir filialning davr bo‘yicha P&L ko‘rinishi.</p></div><a class="finance-link" href="{{ route('finance.filials.index', ['date_from' => $period['from'], 'date_to' => $period['to']]) }}">Batafsil</a></div>
                <div class="finance-table-wrap"><table class="finance-table"><thead><tr><th>Filial</th><th>Tushum</th><th>Bevosita</th><th>Filial</th><th>Foyda</th></tr></thead><tbody>
                    @forelse($dashboard['branches'] as $branch)
                        @php($branchProfitClass = (float) $branch['gross_profit'] >= 0 ? 'finance-profit' : 'finance-loss')
                        <tr><td><a class="finance-branch" href="{{ route('finance.filials.show', ['filial' => $branch['id'], 'date_from' => $period['from'], 'date_to' => $period['to']]) }}">{{ $branch['name'] }}<small>{{ $branch['orders'] }} ta buyurtma</small></a></td><td class="finance-money">{{ number_format($branch['revenue'], 0, ',', ' ') }}</td><td>{{ number_format($branch['direct_cost'], 0, ',', ' ') }}</td><td>{{ number_format($branch['branch_expense'], 0, ',', ' ') }}</td><td class="{{ $branchProfitClass }}">{{ number_format($branch['gross_profit'], 0, ',', ' ') }}</td></tr>
                    @empty
                        <tr><td colspan="5"><div class="finance-empty"><i class='bx bx-buildings'></i><strong>Bu davrda filial ma’lumoti yo‘q.</strong></div></td></tr>
                    @endforelse
                </tbody></table></div>
            </section>

            <section class="finance-panel">
                <div class="finance-panel-head"><div><h2><i class='bx bx-layer'></i> Xizmat rentabelligi</h2><p>Filial xarajatlarisiz xizmatlar kesimida foyda.</p></div><span class="finance-count">{{ count($dashboard['services']) }} ta xizmat</span></div>
                <div class="finance-table-wrap"><table class="finance-table"><thead><tr><th>Xizmat</th><th>Tushum</th><th>Foyda</th><th>Marja</th></tr></thead><tbody>
                    @forelse($dashboard['services'] as $service)
                        @php($marginClass = (float) $service['margin'] < 0 ? 'finance-margin--loss' : ((float) $service['margin'] < 20 ? 'finance-margin--low' : ''))
                        <tr><td class="finance-service"><strong>{{ $service['name'] }}</strong><small>{{ $service['orders'] }} ta hujjat</small></td><td class="finance-money">{{ number_format($service['revenue'], 0, ',', ' ') }}</td><td class="{{ (float) $service['gross_profit_before_branch_expense'] >= 0 ? 'finance-profit' : 'finance-loss' }}">{{ number_format($service['gross_profit_before_branch_expense'], 0, ',', ' ') }}</td><td><span class="finance-margin {{ $marginClass }}">{{ number_format($service['margin'], 1, ',', ' ') }}%</span></td></tr>
                    @empty
                        <tr><td colspan="4"><div class="finance-empty"><i class='bx bx-layer'></i><strong>Bu davrda xizmat ma’lumoti yo‘q.</strong></div></td></tr>
                    @endforelse
                </tbody></table></div>
            </section>
        </section>
    </main>
</div>
@endsection
