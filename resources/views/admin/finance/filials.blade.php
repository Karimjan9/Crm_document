@extends('template')

@section('style')
<style>
    .finance-filial-page { min-height: calc(100vh - 84px); padding: 24px; background: #f4f7fb; color: #172033; }
    .finance-hero, .finance-panel, .finance-kpi, .branch-summary { border: 1px solid #e0e8f2; border-radius: 16px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); }
    .finance-hero { display: flex; justify-content: space-between; align-items: center; gap: 24px; padding: 22px 24px; margin-bottom: 18px; background: linear-gradient(115deg, #fff 0%, #f4f8ff 100%); }
    .finance-eyebrow { display: inline-flex; align-items: center; gap: 6px; color: #2563eb; font-size: 11px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; }
    .finance-eyebrow i { font-size: 15px; }
    .finance-hero h1 { margin: 5px 0 4px; color: #102a56; font-size: 26px; font-weight: 800; }
    .finance-hero p { margin: 0; color: #64748b; font-size: 13px; }
    .finance-filters { display: grid; grid-template-columns: 150px 150px minmax(180px, 1fr) auto; align-items: end; gap: 10px; min-width: min(100%, 610px); }
    .finance-filter label { display: block; margin-bottom: 5px; color: #64748b; font-size: 11px; font-weight: 800; }
    .finance-filter .form-control, .finance-filter .form-select { min-height: 42px; border-color: #d4deeb; border-radius: 10px; font-size: 13px; }
    .finance-filter .btn { min-height: 42px; border-radius: 10px; font-weight: 700; white-space: nowrap; }
    .branch-summary { display: flex; justify-content: space-between; align-items: center; gap: 18px; padding: 19px 22px; margin-bottom: 18px; }
    .branch-summary__label { color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .45px; text-transform: uppercase; }
    .branch-summary h2 { margin: 5px 0 4px; color: #102a56; font-size: 21px; font-weight: 800; }
    .branch-summary__period { color: #64748b; font-size: 12px; }
    .branch-summary .btn { border-radius: 10px; font-weight: 700; }
    .finance-kpi-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .finance-kpi { position: relative; overflow: hidden; padding: 16px; }
    .finance-kpi::before { position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: var(--kpi-color); content: ''; }
    .finance-kpi__icon { display: grid; width: 31px; height: 31px; margin-bottom: 13px; place-items: center; border-radius: 10px; background: var(--kpi-soft); color: var(--kpi-color); font-size: 17px; }
    .finance-kpi__label { color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .2px; text-transform: uppercase; }
    .finance-kpi__value { display: block; margin-top: 6px; color: #172033; font-size: 19px; font-weight: 800; line-height: 1.15; white-space: nowrap; }
    .finance-panel { height: 100%; overflow: hidden; }
    .finance-panel__head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 15px 18px; border-bottom: 1px solid #e7eef6; }
    .finance-panel__head h2 { margin: 0; color: #17305d; font-size: 15px; font-weight: 800; }
    .finance-panel__body { padding: 18px; }
    .profile-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; }
    .profile-item { min-width: 0; padding: 11px 12px; border-radius: 11px; background: #f8fbff; }
    .profile-item span { display: block; margin-bottom: 4px; color: #6b7b93; font-size: 11px; }
    .profile-item strong { display: block; color: #1f355d; font-size: 13px; overflow-wrap: anywhere; }
    .signal-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #eef3f8; color: #64748b; font-size: 13px; }
    .signal-row strong { color: #172033; text-align: right; }
    .signal-row:last-of-type { border-bottom: 0; }
    .capacity-progress { height: 9px; margin: 15px 0 8px; border-radius: 999px; background: #e3eaf3; overflow: hidden; }
    .capacity-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2563eb, #22c55e); }
    .capacity-progress.is-overloaded span { background: linear-gradient(90deg, #f59e0b, #ef4444); }
    .finance-table { margin: 0; }
    .finance-table thead th { padding: 12px 15px; border-bottom-color: #dfe8f2; background: #f8fbff; color: #5d6d85; font-size: 10px; font-weight: 800; letter-spacing: .28px; text-transform: uppercase; white-space: nowrap; }
    .finance-table tbody td { padding: 13px 15px; border-color: #edf2f7; color: #29384f; font-size: 13px; vertical-align: middle; }
    .finance-table tbody tr:hover { background: #fbfdff; }
    .finance-empty { padding: 30px 16px !important; color: #7b8ba3 !important; text-align: center; }
    .finance-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 800; }
    .finance-badge--good { background: #ecfdf5; color: #047857; }
    .finance-badge--risk { background: #fef2f2; color: #b91c1c; }
    .special-price { color: #1f355d; font-weight: 800; }
    @media (max-width: 1320px) { .finance-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 1050px) { .finance-hero { align-items: flex-start; flex-direction: column; } .finance-filters { width: 100%; } }
    @media (max-width: 760px) { .finance-filial-page { padding: 15px; } .finance-hero { padding: 18px; } .finance-hero h1 { font-size: 22px; } .finance-filters { grid-template-columns: 1fr; } .branch-summary { align-items: flex-start; flex-direction: column; } .finance-kpi-grid, .profile-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 440px) { .finance-kpi-grid, .profile-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('body')
@php
    $selected = $selectedReport;
    $money = fn ($value) => number_format((float) $value, 0, ',', ' ');
    $activeFilialId = (int) ($filters['filial_id'] ?? ($selected['profile']['id'] ?? 0));
    $duration = function ($minutes): string {
        $minutes = (int) $minutes;
        return $minutes >= 60
            ? intdiv($minutes, 60) . ' soat' . ($minutes % 60 ? ' ' . ($minutes % 60) . ' daq.' : '')
            : $minutes . ' daq.';
    };
@endphp

<div class="page-wrapper">
    <main class="finance-filial-page">
        <section class="finance-hero">
            <div>
                <span class="finance-eyebrow"><i class="bx bx-line-chart"></i> Moliya monitoringi</span>
                <h1>Filial moliyaviy hisoboti</h1>
                <p>Daromad, tushgan to‘lov, qarzdorlik, xarajat va filial bandligini bir joyda kuzating.</p>
            </div>
            <form class="finance-filters" method="GET" action="{{ route('finance.filials.index') }}">
                <div class="finance-filter">
                    <label for="dateFrom">Boshlanish sanasi</label>
                    <input id="dateFrom" class="form-control" type="date" name="date_from" value="{{ $filters['date_from']->toDateString() }}">
                </div>
                <div class="finance-filter">
                    <label for="dateTo">Tugash sanasi</label>
                    <input id="dateTo" class="form-control" type="date" name="date_to" value="{{ $filters['date_to']->toDateString() }}">
                </div>
                <div class="finance-filter">
                    <label for="filialId">Filial</label>
                    <select id="filialId" class="form-select" name="filial_id">
                        <option value="">Barcha filiallar</option>
                        @foreach($filials as $filial)
                            <option value="{{ $filial->id }}" @selected($activeFilialId === (int) $filial->id)>{{ $filial->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bx bx-filter-alt"></i> Ko‘rsatish</button>
            </form>
        </section>

        @if($selected)
            <section class="branch-summary">
                <div>
                    <div class="branch-summary__label">Tanlangan filial</div>
                    <h2>{{ $selected['profile']['name'] }} <span class="text-muted fs-6">{{ $selected['profile']['code'] ? '· ' . $selected['profile']['code'] : '' }}</span></h2>
                    <div class="branch-summary__period">Hisobot davri: {{ $selected['period']['from'] }} — {{ $selected['period']['to'] }}</div>
                </div>
                @if(request()->routeIs('finance.filials.show'))
                    <a class="btn btn-outline-primary" href="{{ route('finance.filials.index') }}"><i class="bx bx-arrow-back"></i> Barcha filiallar</a>
                @else
                    <a class="btn btn-outline-primary" href="{{ route('finance.filials.show', $selected['profile']['id']) }}?date_from={{ $selected['period']['from'] }}&date_to={{ $selected['period']['to'] }}"><i class="bx bx-show"></i> Batafsil ko‘rish</a>
                @endif
            </section>

            <section class="finance-kpi-grid" aria-label="Asosiy moliyaviy ko‘rsatkichlar">
                @foreach([
                    ['Daromad', $selected['kpis']['revenue'], '#2563eb', '#eff6ff', 'bx bx-trending-up'],
                    ['Tushgan to‘lov', $selected['kpis']['paid'], '#0891b2', '#ecfeff', 'bx bx-wallet'],
                    ['Qarzdorlik', $selected['kpis']['debt'], '#d97706', '#fffbeb', 'bx bx-time-five'],
                    ['Xarajat', $selected['kpis']['expense'], '#dc2626', '#fef2f2', 'bx bx-receipt'],
                    ['Yalpi foyda', $selected['kpis']['gross_profit'], '#059669', '#ecfdf5', 'bx bx-line-chart'],
                    ['O‘rtacha buyurtma', $selected['kpis']['average_order_value'], '#64748b', '#f8fafc', 'bx bx-bar-chart-alt-2'],
                ] as [$label, $value, $color, $soft, $icon])
                    <article class="finance-kpi" style="--kpi-color: {{ $color }}; --kpi-soft: {{ $soft }}">
                        <span class="finance-kpi__icon"><i class="{{ $icon }}"></i></span>
                        <span class="finance-kpi__label">{{ $label }}</span>
                        <strong class="finance-kpi__value">{{ $money($value) }} so‘m</strong>
                    </article>
                @endforeach
            </section>

            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <section class="finance-panel">
                        <div class="finance-panel__head"><h2>Filial profili va rejalari</h2></div>
                        <div class="finance-panel__body">
                            <div class="profile-grid">
                                <div class="profile-item"><span>Filial rahbari</span><strong>{{ $selected['profile']['manager_name'] ?: 'Biriktirilmagan' }}</strong></div>
                                <div class="profile-item"><span>Telefon</span><strong>{{ $selected['profile']['phone'] ?: 'Ko‘rsatilmagan' }}</strong></div>
                                <div class="profile-item"><span>Manzil</span><strong>{{ $selected['profile']['address'] ?: 'Ko‘rsatilmagan' }}</strong></div>
                                <div class="profile-item"><span>Ish vaqti</span><strong>{{ $selected['profile']['work_start_time'] ?: '—' }} — {{ $selected['profile']['work_end_time'] ?: '—' }}</strong></div>
                                <div class="profile-item"><span>Ish kunlari</span><strong>{{ $selected['profile']['working_days_label'] ?: '—' }}</strong></div>
                                <div class="profile-item"><span>Maxsus dam olish kunlari</span><strong>{{ count($selected['profile']['holiday_dates']) ?: 0 }} ta</strong></div>
                                <div class="profile-item"><span>Oylik rejalashtirilgan xarajat</span><strong>{{ $money($selected['profile']['monthly_expense']) }} so‘m</strong></div>
                                <div class="profile-item"><span>Daromad maqsadi</span><strong>{{ $money($selected['profile']['target_amount']) }} so‘m ({{ $selected['kpis']['target_progress'] }}%)</strong></div>
                                <div class="profile-item"><span>Komissiya / kunlik quvvat</span><strong>{{ $selected['profile']['commission_percent'] }}% / {{ $selected['profile']['daily_capacity'] ?: '—' }} buyurtma</strong></div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-xl-4">
                    <section class="finance-panel">
                        <div class="finance-panel__head"><h2>Operatsion holat</h2></div>
                        <div class="finance-panel__body">
                            <div class="signal-row"><span>Buyurtmalar</span><strong>{{ $selected['kpis']['order_count'] }} ta</strong></div>
                            <div class="signal-row"><span>Muddati o‘tgan</span><strong class="text-{{ $selected['kpis']['deadline_breach_count'] > 0 ? 'danger' : 'success' }}">{{ $selected['kpis']['deadline_breach_count'] }} buyurtma / {{ $selected['kpis']['deadline_breach_documents'] }} hujjat</strong></div>
                            <div class="signal-row"><span>Kechikish ulushi</span><strong>{{ $selected['kpis']['deadline_breach_rate'] }}%</strong></div>
                            <div class="signal-row"><span>Bandlik darajasi</span><strong>{{ $selected['kpis']['capacity_utilization'] }}%</strong></div>
                            <div class="capacity-progress {{ $selected['kpis']['capacity_utilization'] > 100 ? 'is-overloaded' : '' }}"><span style="width: {{ min((float) $selected['kpis']['capacity_utilization'], 100) }}%"></span></div>
                            <small class="text-muted">{{ $selected['profile']['working_days_in_period'] }} ish kuni; rejalashtirilgan quvvat: {{ $selected['profile']['period_capacity'] ?: 'cheklanmagan' }} ta.</small>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-7">
                    <section class="finance-panel">
                        <div class="finance-panel__head"><h2>Xodimlar samaradorligi</h2></div>
                        <div class="table-responsive">
                            <table class="table finance-table align-middle">
                                <thead><tr><th>Xodim</th><th>Buyurtmalar</th><th>Daromad</th><th>Yakunlash</th><th>Kechikish</th><th>Yuklama</th></tr></thead>
                                <tbody>
                                    @forelse($selected['employees'] as $employee)
                                        <tr>
                                            <td class="fw-semibold">{{ $employee['name'] }}</td>
                                            <td>{{ $employee['order_count'] }}</td>
                                            <td>{{ $money($employee['revenue']) }} so‘m</td>
                                            <td>{{ $employee['completion_rate'] }}%</td>
                                            <td><span class="finance-badge {{ $employee['deadline_breaches'] > 0 ? 'finance-badge--risk' : 'finance-badge--good' }}">{{ $employee['deadline_breaches'] }} ta</span></td>
                                            <td>{{ $duration($employee['workload_minutes']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="finance-empty">Xodimlar bo‘yicha ma’lumot yo‘q.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-xl-5">
                    <section class="finance-panel">
                        <div class="finance-panel__head"><h2>Filial maxsus narxlari</h2><span class="finance-badge finance-badge--good">{{ count($selected['special_prices']) }} ta</span></div>
                        <div class="table-responsive">
                            <table class="table finance-table align-middle">
                                <thead><tr><th>Xizmat</th><th>Variant</th><th>Amal qilish muddati</th></tr></thead>
                                <tbody>
                                    @forelse($selected['special_prices'] as $price)
                                        <tr>
                                            <td><span class="special-price">{{ $price['name'] }}</span><br><small class="text-muted">{{ $money($price['price']) }} {{ $price['currency'] }}</small></td>
                                            <td>{{ $price['variant'] === 'express' ? 'Tezkor' : 'Standart' }}</td>
                                            <td class="small">{{ $price['effective_from'] }}<br>{{ $price['effective_to'] ?: 'Muddatsiz' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="finance-empty">Filial uchun maxsus faol narx yo‘q.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        @endif

        @unless(request()->routeIs('finance.filials.show'))
            <section class="finance-panel">
                <div class="finance-panel__head"><h2>Filiallar taqqoslanishi</h2><span class="text-muted small">Tanlangan davr bo‘yicha</span></div>
                <div class="table-responsive">
                    <table class="table finance-table align-middle">
                        <thead><tr><th>Filial</th><th>Daromad</th><th>Tushgan to‘lov</th><th>Qarzdorlik</th><th>Xarajat</th><th>Yalpi foyda</th><th>Buyurtma / o‘rtacha summa</th><th>Kechikish</th><th></th></tr></thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td><strong>{{ $report['profile']['name'] }}</strong><br><small class="text-muted">{{ $report['profile']['code'] ?: '—' }}</small></td>
                                    <td>{{ $money($report['kpis']['revenue']) }}</td>
                                    <td>{{ $money($report['kpis']['paid']) }}</td>
                                    <td>{{ $money($report['kpis']['debt']) }}</td>
                                    <td>{{ $money($report['kpis']['expense']) }}</td>
                                    <td class="fw-bold text-{{ $report['kpis']['gross_profit'] < 0 ? 'danger' : 'success' }}">{{ $money($report['kpis']['gross_profit']) }}</td>
                                    <td>{{ $report['kpis']['order_count'] }} ta / {{ $money($report['kpis']['average_order_value']) }}</td>
                                    <td><span class="finance-badge {{ $report['kpis']['deadline_breach_count'] > 0 ? 'finance-badge--risk' : 'finance-badge--good' }}">{{ $report['kpis']['deadline_breach_count'] }} ta ({{ $report['kpis']['deadline_breach_rate'] }}%)</span></td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.filials.show', $report['profile']['id']) }}?date_from={{ $report['period']['from'] }}&date_to={{ $report['period']['to'] }}">Ko‘rish</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="finance-empty">Filial topilmadi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endunless
    </main>
</div>
@endsection
