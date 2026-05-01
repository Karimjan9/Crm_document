@extends('template')

@section('style')
<style>
    .stats-shell {
        background: #f5f7fb;
        min-height: calc(100vh - 64px);
        color: #172033;
    }

    .stats-shell .page-content {
        padding: 22px;
    }

    .stats-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .stats-title h4 {
        margin: 0;
        color: #102a56;
        font-weight: 700;
        font-size: 24px;
    }

    .stats-title p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .stats-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 8px;
        padding: 10px 14px;
        background: #1d4ed8;
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .stats-link:hover {
        background: #1e40af;
        color: #fff;
    }

    .filter-panel,
    .stat-panel,
    .table-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .filter-panel {
        padding: 16px;
        margin-bottom: 18px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .filter-field label {
        display: block;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .filter-field input,
    .filter-field select {
        width: 100%;
        height: 40px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 10px;
        background: #fff;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
    }

    .filter-actions .btn {
        height: 40px;
        border-radius: 8px;
        font-weight: 600;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .summary-tile {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .summary-tile span {
        display: block;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .summary-tile strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.2;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 14px;
        margin-bottom: 18px;
    }

    .stat-panel {
        overflow: hidden;
    }

    .panel-header {
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .panel-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #102a56;
    }

    .panel-header span {
        color: #64748b;
        font-size: 13px;
    }

    .chart-box {
        height: 320px;
        padding: 16px;
    }

    .table-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 14px;
    }

    .table-panel {
        overflow: hidden;
    }

    .stats-table {
        width: 100%;
        margin: 0;
    }

    .stats-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px;
        white-space: nowrap;
    }

    .stats-table td {
        padding: 12px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .money {
        white-space: nowrap;
        font-weight: 700;
        color: #0f172a;
    }

    .muted {
        color: #64748b;
        font-size: 12px;
    }

    @media (max-width: 1200px) {
        .filter-grid { grid-template-columns: repeat(3, minmax(150px, 1fr)); }
        .summary-grid { grid-template-columns: repeat(2, minmax(160px, 1fr)); }
        .chart-grid,
        .table-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .stats-shell .page-content { padding: 14px; }
        .stats-header { flex-direction: column; }
        .stats-link { width: 100%; justify-content: center; }
        .filter-grid,
        .summary-grid { grid-template-columns: 1fr; }
        .chart-box { height: 280px; }
    }
</style>
@endsection

@section('body')
@php
    $money = fn ($amount) => number_format((float) $amount, 0, '.', ' ');
    $paidRate = $summary['final_price'] > 0 ? min(100, round(($summary['paid_amount'] / $summary['final_price']) * 100)) : 0;
    $monthlyLabels = collect($monthlyStats)->pluck('label')->all();
    $monthlyDocuments = collect($monthlyStats)->pluck('documents')->all();
    $monthlyFinal = collect($monthlyStats)->pluck('final_price')->all();
    $monthlyPaid = collect($monthlyStats)->pluck('paid_amount')->all();
    $yearlyLabels = collect($yearlyStats)->pluck('year')->all();
    $yearlyDocuments = collect($yearlyStats)->pluck('documents')->all();
    $yearlyFinal = collect($yearlyStats)->pluck('final_price')->all();
@endphp

<div class="page-wrapper stats-shell">
    <div class="page-content">
        <div class="stats-header">
            <div class="stats-title">
                <h4>Hujjatlar statistikasi</h4>
                <p>Oy, yil, filial va xodim bo'yicha real monitoring ko'rsatkichlari.</p>
            </div>
            <a href="{{ route($routePrefix . '.document.index') }}" class="stats-link">
                <i class="bx bx-table"></i>
                Monitoring
            </a>
        </div>

        <form method="GET" action="{{ route($routePrefix . '.document.statistika') }}" class="filter-panel">
            <div class="filter-grid">
                <div class="filter-field">
                    <label>Filial</label>
                    <select name="filial_id">
                        <option value="">Barchasi</option>
                        @foreach($filials as $filial)
                            <option value="{{ $filial->id }}" @selected((string) request('filial_id') === (string) $filial->id)>{{ $filial->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Foydalanuvchi</label>
                    <select name="user_id">
                        <option value="">Barchasi</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Jarayon</label>
                    <select name="process_mode">
                        <option value="">Barchasi</option>
                        @foreach($processLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('process_mode') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Holat</label>
                    <select name="status_doc">
                        <option value="">Barchasi</option>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status_doc') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>To'lov</label>
                    <select name="payment_status">
                        <option value="">Barchasi</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>To'langan</option>
                        <option value="partial" @selected(request('payment_status') === 'partial')>Qisman</option>
                        <option value="debt" @selected(request('payment_status') === 'debt')>Qarzdor</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label>Yil</label>
                    <select name="year">
                        @foreach($yearOptions as $year)
                            <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Oy</label>
                    <select name="month">
                        <option value="">Barcha oylar</option>
                        @foreach($monthNames as $value => $label)
                            <option value="{{ $value }}" @selected((int) $selectedMonth === (int) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Sana dan</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="filter-field">
                    <label>Sana gacha</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Ko'rish</button>
                    <a href="{{ route($routePrefix . '.document.statistika') }}" class="btn btn-light">Tozalash</a>
                </div>
            </div>
        </form>

        <div class="summary-grid">
            <div class="summary-tile">
                <span>Hujjatlar</span>
                <strong>{{ $summary['documents'] }}</strong>
            </div>
            <div class="summary-tile">
                <span>Jami summa</span>
                <strong>{{ $money($summary['final_price']) }}</strong>
            </div>
            <div class="summary-tile">
                <span>To'langan</span>
                <strong>{{ $money($summary['paid_amount']) }}</strong>
            </div>
            <div class="summary-tile">
                <span>Qoldiq</span>
                <strong>{{ $money($summary['balance']) }}</strong>
            </div>
            <div class="summary-tile">
                <span>Yopilish foizi</span>
                <strong>{{ $paidRate }}%</strong>
            </div>
        </div>

        <div class="chart-grid">
            <div class="stat-panel">
                <div class="panel-header">
                    <h5>{{ $selectedYear }} yil oylar kesimi</h5>
                    <span>Hujjat soni va tushum dinamikasi</span>
                </div>
                <div class="chart-box">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            <div class="stat-panel">
                <div class="panel-header">
                    <h5>To'lov holati</h5>
                    <span>Tanlangan davr</span>
                </div>
                <div class="chart-box">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="stat-panel mb-3">
            <div class="panel-header">
                <h5>Yillar kesimi</h5>
                <span>Filtrlangan ma'lumotlarning yillik taqqoslanishi</span>
            </div>
            <div class="chart-box">
                <canvas id="yearlyChart"></canvas>
            </div>
        </div>

        <div class="table-grid">
            <div class="table-panel">
                <div class="panel-header">
                    <h5>Filiallar bo'yicha</h5>
                    <span>Top 12</span>
                </div>
                <div class="table-responsive">
                    <table class="table stats-table">
                        <thead>
                            <tr>
                                <th>Filial</th>
                                <th>Hujjat</th>
                                <th>Tugallangan</th>
                                <th>Qoldiq</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filialStats as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['documents'] }}</td>
                                    <td>{{ $row['finished'] }}</td>
                                    <td><span class="money">{{ $money($row['balance']) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Ma'lumot yo'q.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-panel">
                <div class="panel-header">
                    <h5>Xodimlar bo'yicha</h5>
                    <span>Top 12</span>
                </div>
                <div class="table-responsive">
                    <table class="table stats-table">
                        <thead>
                            <tr>
                                <th>Xodim</th>
                                <th>Hujjat</th>
                                <th>To'langan</th>
                                <th>Qoldiq</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userStats as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['documents'] }}</td>
                                    <td><span class="money">{{ $money($row['paid_amount']) }}</span></td>
                                    <td><span class="money">{{ $money($row['balance']) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Ma'lumot yo'q.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-panel">
                <div class="panel-header">
                    <h5>Hujjat turlari</h5>
                    <span>Top 12</span>
                </div>
                <div class="table-responsive">
                    <table class="table stats-table">
                        <thead>
                            <tr>
                                <th>Tur</th>
                                <th>Hujjat</th>
                                <th>Jami</th>
                                <th>To'langan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($typeStats as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['documents'] }}</td>
                                    <td><span class="money">{{ $money($row['final_price']) }}</span></td>
                                    <td><span class="money">{{ $money($row['paid_amount']) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Ma'lumot yo'q.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-panel">
                <div class="panel-header">
                    <h5>To'lov xulosasi</h5>
                    <span>Tanlangan davr</span>
                </div>
                <div class="table-responsive">
                    <table class="table stats-table">
                        <thead>
                            <tr>
                                <th>Holat</th>
                                <th>Soni</th>
                                <th>Izoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>To'langan</td>
                                <td>{{ $summary['paid_documents'] }}</td>
                                <td class="muted">Qoldiq yopilgan hujjatlar</td>
                            </tr>
                            <tr>
                                <td>Qisman</td>
                                <td>{{ $summary['partial_documents'] }}</td>
                                <td class="muted">To'lov boshlangan, lekin yopilmagan</td>
                            </tr>
                            <tr>
                                <td>Qarzdor</td>
                                <td>{{ $summary['debt_documents'] }}</td>
                                <td class="muted">Hali to'lov kiritilmagan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script_include_end_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const moneyFormatter = (value) => Number(value || 0).toLocaleString('uz-UZ') + " so'm";

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                {
                    label: 'Hujjatlar',
                    data: @json($monthlyDocuments),
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Jami summa',
                    data: @json($monthlyFinal),
                    type: 'line',
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.12)',
                    tension: 0.35,
                    yAxisID: 'amount'
                },
                {
                    label: "To'langan",
                    data: @json($monthlyPaid),
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.12)',
                    tension: 0.35,
                    yAxisID: 'amount'
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            interaction: {mode: 'index', intersect: false},
            scales: {
                y: {beginAtZero: true, ticks: {precision: 0}},
                amount: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {drawOnChartArea: false},
                    ticks: {callback: moneyFormatter}
                }
            }
        }
    });

    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: ["To'langan", 'Qisman', 'Qarzdor'],
            datasets: [{
                data: @json([$summary['paid_documents'], $summary['partial_documents'], $summary['debt_documents']]),
                backgroundColor: ['#16a34a', '#f59e0b', '#dc2626'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: {position: 'bottom'}
            }
        }
    });

    new Chart(document.getElementById('yearlyChart'), {
        type: 'bar',
        data: {
            labels: @json($yearlyLabels),
            datasets: [
                {
                    label: 'Hujjatlar',
                    data: @json($yearlyDocuments),
                    backgroundColor: '#1d4ed8',
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Jami summa',
                    data: @json($yearlyFinal),
                    type: 'line',
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.12)',
                    tension: 0.35,
                    yAxisID: 'amount'
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            interaction: {mode: 'index', intersect: false},
            scales: {
                y: {beginAtZero: true, ticks: {precision: 0}},
                amount: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {drawOnChartArea: false},
                    ticks: {callback: moneyFormatter}
                }
            }
        }
    });
});
</script>
@endsection
