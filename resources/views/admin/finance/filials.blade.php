@extends('template')

@section('body')
@php
    $weekdayLabels = [1 => 'Du', 2 => 'Se', 3 => 'Cho', 4 => 'Pa', 5 => 'Ju', 6 => 'Sha', 7 => 'Ya'];
    $selected = $selectedReport;
    $money = fn ($value) => number_format((float) $value, 0, ',', ' ');
@endphp

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h3 class="mb-1">Filial P&amp;L va boshqaruv</h3>
            <p class="text-muted mb-0">Daromad, real to‘lov, qarzdorlik, xarajat va xodim samaradorligi bir joyda.</p>
        </div>
        <form class="d-flex flex-wrap gap-2" method="GET" action="{{ route('finance.filials.index') }}">
            <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from']->toDateString() }}" aria-label="Boshlanish sanasi">
            <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to']->toDateString() }}" aria-label="Tugash sanasi">
            <select class="form-select" name="filial_id" aria-label="Filial">
                <option value="">Barcha filiallar</option>
                @foreach($filials as $filial)
                    <option value="{{ $filial->id }}" @selected((int) ($filters['filial_id'] ?? 0) === (int) $filial->id)>{{ $filial->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Ko‘rsatish</button>
        </form>
    </div>

    @if($selected)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-muted small">Tanlangan filial</div>
                    <h4 class="mb-1">{{ $selected['profile']['name'] }} <span class="text-muted fs-6">({{ $selected['profile']['code'] ?: 'kod yo‘q' }})</span></h4>
                    <div class="small text-muted">Hisobot davri: {{ $selected['period']['from'] }} — {{ $selected['period']['to'] }}</div>
                </div>
                <a class="btn btn-outline-primary" href="{{ route('finance.filials.show', $selected['profile']['id']) }}?date_from={{ $selected['period']['from'] }}&date_to={{ $selected['period']['to'] }}">Filial tafsilotlari</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach([
                ['Revenue', $selected['kpis']['revenue'], 'primary'],
                ['Paid', $selected['kpis']['paid'], 'info'],
                ['Debt', $selected['kpis']['debt'], 'warning'],
                ['Expense', $selected['kpis']['expense'], 'danger'],
                ['Gross profit', $selected['kpis']['gross_profit'], 'success'],
                ['Average order', $selected['kpis']['average_order_value'], 'secondary'],
            ] as [$label, $value, $color])
                <div class="col-6 col-xl-2">
                    <div class="card border-{{ $color }} h-100 shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">{{ $label }}</small>
                            <h5 class="mt-2 mb-0 text-{{ $color }}">{{ $money($value) }} UZS</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Filial profili va reja</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><span class="text-muted small d-block">Manager</span><strong>{{ $selected['profile']['manager_name'] ?: 'Biriktirilmagan' }}</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Telefon</span><strong>{{ $selected['profile']['phone'] ?: 'Ko‘rsatilmagan' }}</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Manzil</span><strong>{{ $selected['profile']['address'] ?: 'Ko‘rsatilmagan' }}</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Ish vaqti</span><strong>{{ $selected['profile']['work_start_time'] ?: '—' }} — {{ $selected['profile']['work_end_time'] ?: '—' }}</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Ish kunlari</span><strong>{{ $selected['profile']['working_days_label'] ?: '—' }}</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Maxsus dam olish</span><strong>{{ count($selected['profile']['holiday_dates']) ?: 0 }} ta</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Oylik rejalashtirilgan xarajat</span><strong>{{ $money($selected['profile']['monthly_expense']) }} UZS</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Target</span><strong>{{ $money($selected['profile']['target_amount']) }} UZS ({{ $selected['kpis']['target_progress'] }}%)</strong></div>
                            <div class="col-md-4"><span class="text-muted small d-block">Commission / capacity</span><strong>{{ $selected['profile']['commission_percent'] }}% / {{ $selected['profile']['daily_capacity'] ?: '—' }} order/kun</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Operatsion signal</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3"><span>Orderlar</span><strong>{{ $selected['kpis']['order_count'] }}</strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Deadline breach</span><strong class="text-{{ $selected['kpis']['deadline_breach_count'] > 0 ? 'danger' : 'success' }}">{{ $selected['kpis']['deadline_breach_count'] }} order / {{ $selected['kpis']['deadline_breach_documents'] }} hujjat</strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Breach rate</span><strong>{{ $selected['kpis']['deadline_breach_rate'] }}%</strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Capacity</span><strong>{{ $selected['kpis']['capacity_utilization'] }}%</strong></div>
                        <div class="progress mb-2" style="height: 8px"><div class="progress-bar bg-{{ $selected['kpis']['capacity_utilization'] > 100 ? 'danger' : 'primary' }}" style="width: {{ min((float) $selected['kpis']['capacity_utilization'], 100) }}%"></div></div>
                        <small class="text-muted">{{ $selected['profile']['working_days_in_period'] }} ish kuni, {{ $selected['profile']['period_capacity'] ?: 'cheklanmagan' }} ta rejalashtirilgan capacity.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Xodimlar samaradorligi</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Xodim</th><th>Order</th><th>Revenue</th><th>Yakun</th><th>Deadline</th><th>Workload</th></tr></thead>
                            <tbody>
                            @forelse($selected['employees'] as $employee)
                                <tr>
                                    <td class="fw-semibold">{{ $employee['name'] }}</td>
                                    <td>{{ $employee['order_count'] }}</td>
                                    <td>{{ $money($employee['revenue']) }}</td>
                                    <td>{{ $employee['completion_rate'] }}%</td>
                                    <td><span class="badge bg-{{ $employee['deadline_breaches'] > 0 ? 'danger' : 'success' }}">{{ $employee['deadline_breaches'] }}</span></td>
                                    <td>{{ $employee['workload_minutes'] }} min</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Xodimlar bo‘yicha ma’lumot yo‘q.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center"><span>Filial maxsus tariflari</span><span>@if(auth()->user()?->hasAnyRole(['admin_manager', 'super_admin']))<a class="small me-2" href="{{ route('admin.pricing.index', ['filial_id' => $selected['profile']['id']]) }}">Boshqarish</a>@endif<span class="badge bg-primary">{{ count($selected['special_prices']) }}</span></span></div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Narx</th><th>Variant</th><th>Amal qilish</th></tr></thead>
                            <tbody>
                            @forelse($selected['special_prices'] as $price)
                                <tr><td><strong>{{ $price['name'] }}</strong><br><span class="text-muted">{{ $money($price['price']) }} {{ $price['currency'] }}</span></td><td>{{ ucfirst($price['variant']) }}</td><td class="small">{{ $price['effective_from'] }}<br>{{ $price['effective_to'] ?: 'ochiq' }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Filialga maxsus aktiv tarif yo‘q.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Filiallar kesimida P&amp;L</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Filial</th><th>Revenue</th><th>Paid</th><th>Debt</th><th>Expense</th><th>Gross profit</th><th>Order / AOV</th><th>Deadline breach</th><th></th></tr></thead>
                <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td><strong>{{ $report['profile']['name'] }}</strong><br><small class="text-muted">{{ $report['profile']['code'] ?: '—' }}</small></td>
                        <td>{{ $money($report['kpis']['revenue']) }}</td>
                        <td>{{ $money($report['kpis']['paid']) }}</td>
                        <td>{{ $money($report['kpis']['debt']) }}</td>
                        <td>{{ $money($report['kpis']['expense']) }}</td>
                        <td class="fw-bold text-{{ $report['kpis']['gross_profit'] < 0 ? 'danger' : 'success' }}">{{ $money($report['kpis']['gross_profit']) }}</td>
                        <td>{{ $report['kpis']['order_count'] }} / {{ $money($report['kpis']['average_order_value']) }}</td>
                        <td><span class="badge bg-{{ $report['kpis']['deadline_breach_count'] > 0 ? 'danger' : 'success' }}">{{ $report['kpis']['deadline_breach_count'] }} ({{ $report['kpis']['deadline_breach_rate'] }}%)</span></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.filials.show', $report['profile']['id']) }}?date_from={{ $report['period']['from'] }}&date_to={{ $report['period']['to'] }}">Ko‘rish</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">Filial topilmadi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
