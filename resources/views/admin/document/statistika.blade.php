@extends('template')

@section('style')
<style>
 @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* ── Animatsiyalar ── */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes countUp {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
}
@keyframes shimmer {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}

/* ── Umumiy ── */
.stats-shell {
    background: #f0f4fb;
    min-height: calc(100vh - 64px);
    font-family: 'Inter', sans-serif;
    color: #172033;
}
.stats-shell .page-content { padding: 22px; }

/* ── Header ── */
.stats-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
    animation: fadeInUp .45s ease both;
}
.stats-title h4 {
    margin: 0;
    font-weight: 700;
    font-size: 26px;
    background: linear-gradient(90deg, #1d4ed8, #0f766e, #7c3aed);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 4s linear infinite;
}
.stats-title p { margin: 6px 0 0; color: #64748b; font-size: 14px; }

.stats-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 10px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(37,99,235,.3);
    transition: transform .2s, box-shadow .2s, background .2s;
}
.stats-link:hover {
    background: linear-gradient(135deg, #1e40af, #1d4ed8);
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(37,99,235,.4);
    color: #fff;
}
.stats-link i { transition: transform .3s; }
.stats-link:hover i { transform: rotate(15deg) scale(1.2); }

/* ── Panel ── */
.filter-panel, .stat-panel, .table-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(15,23,42,.06);
}
.filter-panel { padding: 18px; margin-bottom: 20px; animation: fadeInUp .5s .1s ease both; }

/* ── Filter ── */
.filter-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(150px,1fr));
    gap: 14px;
    align-items: end;
}
.filter-field label {
    display: block;
    margin-bottom: 6px;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
}
.filter-field input,
.filter-field select {
    width: 100%;
    height: 40px;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    padding: 0 12px;
    background: #f8fafc;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #1e293b;
    outline: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.filter-field input:focus,
.filter-field select:focus {
    border-color: #3b82f6;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.filter-field input:hover,
.filter-field select:hover { border-color: #94a3b8; }

.filter-actions { display: flex; gap: 8px; }
.filter-actions .btn {
    height: 40px;
    border-radius: 9px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    transition: transform .15s, box-shadow .15s;
}
.filter-actions .btn:hover { transform: translateY(-1px); }
.filter-actions .btn-primary { box-shadow: 0 3px 10px rgba(37,99,235,.25); }

/* ── Summary tiles ── */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(160px,1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.summary-tile {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 16px;
    box-shadow: 0 4px 16px rgba(15,23,42,.05);
    position: relative;
    overflow: hidden;
    transition: transform .22s, box-shadow .22s, border-color .22s;
    animation: fadeInUp .5s ease both;
}
.summary-tile::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #6366f1);
    border-radius: 12px 12px 0 0;
}
.summary-tile:nth-child(2)::before { background: linear-gradient(90deg, #0f766e, #14b8a6); }
.summary-tile:nth-child(3)::before { background: linear-gradient(90deg, #16a34a, #22c55e); }
.summary-tile:nth-child(4)::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.summary-tile:nth-child(5)::before { background: linear-gradient(90deg, #7c3aed, #a855f7); }
.summary-tile:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(15,23,42,.1);
    border-color: #c7d2fe;
}
.summary-tile span {
    display: block;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.summary-tile strong {
    display: block;
    margin-top: 10px;
    color: #0f172a;
    font-size: 22px;
    line-height: 1.2;
    font-weight: 700;
    animation: countUp .5s .2s ease both;
}

/* ── Charts ── */
.chart-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 14px;
    margin-bottom: 18px;
}
.stat-panel { overflow: hidden; transition: box-shadow .22s; }
.stat-panel:hover { box-shadow: 0 10px 32px rgba(15,23,42,.1); }

.panel-header {
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.panel-header h5 { margin: 0; font-size: 15px; font-weight: 700; color: #102a56; }
.panel-header span { color: #94a3b8; font-size: 12px; font-weight: 500; }
.chart-box { height: 320px; padding: 16px; }

/* ── Tables ── */
.table-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(280px,1fr));
    gap: 14px;
}
.table-panel { overflow: hidden; transition: box-shadow .22s; }
.table-panel:hover { box-shadow: 0 10px 32px rgba(15,23,42,.1); }

.stats-table { width: 100%; margin: 0; font-family: 'Inter', sans-serif; }
.stats-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .5px;
    font-weight: 700;
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 14px;
    white-space: nowrap;
}
.stats-table td {
    padding: 11px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 13px;
}
.stats-table tbody tr { transition: background .15s; }
.stats-table tbody tr:hover { background: #f8faff; }

.money { white-space: nowrap; font-weight: 700; color: #0f172a; font-size: 13px; }
.muted { color: #94a3b8; font-size: 12px; }

/* ── Responsive ── */
@media (max-width: 1200px) {
    .filter-grid { grid-template-columns: repeat(3, minmax(150px,1fr)); }
    .summary-grid { grid-template-columns: repeat(2, minmax(160px,1fr)); }
    .chart-grid, .table-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .stats-shell .page-content { padding: 14px; }
    .stats-header { flex-direction: column; }
    .stats-link { width: 100%; justify-content: center; }
    .filter-grid, .summary-grid { grid-template-columns: 1fr; }
    .chart-box { height: 260px; }
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

    Chart.defaults.global.defaultFontFamily = "'Roboto', 'Arial', sans-serif";
    Chart.defaults.global.defaultFontColor = '#475569';

    const moneyFormatter = function (value) {
        return Number(value || 0).toLocaleString('uz-UZ') + " so'm";
    };

    const sharedGrid = {
        color: 'rgba(148, 163, 184, 0.18)',
        zeroLineColor: 'rgba(148, 163, 184, 0.32)',
        drawBorder: false
    };

    function verticalGradient(canvas, from, to) {
        const ctx = canvas.getContext('2d');
        const fill = ctx.createLinearGradient(0, 0, 0, canvas.height || 320);
        fill.addColorStop(0, from);
        fill.addColorStop(1, to);
        return fill;
    }

    const centerTextPlugin = {
        afterDraw: function (chart) {
            const center = chart.config.options.centerText;
            if (!center) return;

            const ctx = chart.chart.ctx;
            const area = chart.chartArea;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#0f172a';
            ctx.font = '700 24px Roboto, Arial, sans-serif';
            ctx.fillText(center.title, (area.left + area.right) / 2, (area.top + area.bottom) / 2 - 4);
            ctx.fillStyle = '#64748b';
            ctx.font = '600 12px Roboto, Arial, sans-serif';
            ctx.fillText(center.subtitle, (area.left + area.right) / 2, (area.top + area.bottom) / 2 + 20);
            ctx.restore();
        }
    };
    Chart.plugins.register(centerTextPlugin);

    const monthlyCanvas = document.getElementById('monthlyChart');
    const yearlyCanvas = document.getElementById('yearlyChart');
    const paymentTotal = @json($summary['paid_documents'] + $summary['partial_documents'] + $summary['debt_documents']);

    new Chart(monthlyCanvas, {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                {
                    label: 'Hujjatlar',
                    data: @json($monthlyDocuments),
                    backgroundColor: verticalGradient(monthlyCanvas, 'rgba(37, 99, 235, 0.92)', 'rgba(37, 99, 235, 0.24)'),
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Jami summa',
                    data: @json($monthlyFinal),
                    type: 'line',
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.12)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0f766e',
                    pointRadius: 4,
                    lineTension: 0.35,
                    fill: true,
                    yAxisID: 'amount'
                },
                {
                    label: "To'langan",
                    data: @json($monthlyPaid),
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.12)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#f59e0b',
                    pointRadius: 4,
                    lineTension: 0.35,
                    fill: true,
                    yAxisID: 'amount'
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, fontColor: '#475569' } },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const value = tooltipItem.yLabel;
                        return dataset.yAxisID === 'amount'
                            ? dataset.label + ': ' + moneyFormatter(value)
                            : dataset.label + ': ' + value;
                    }
                }
            },
            scales: {
                xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#64748b' } }],
                yAxes: [
                    {
                        id: 'y',
                        position: 'left',
                        gridLines: sharedGrid,
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#64748b' }
                    },
                    {
                        id: 'amount',
                        position: 'right',
                        gridLines: { drawOnChartArea: false, drawBorder: false },
                        ticks: { beginAtZero: true, callback: moneyFormatter, fontColor: '#64748b' }
                    }
                ]
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
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            cutoutPercentage: 68,
            centerText: {
                title: paymentTotal,
                subtitle: 'hujjat'
            },
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, fontColor: '#475569' } },
            tooltips: {
                callbacks: {
                    label: function (tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        return data.labels[tooltipItem.index] + ': ' + dataset.data[tooltipItem.index];
                    }
                }
            }
        }
    });

    new Chart(yearlyCanvas, {
        type: 'bar',
        data: {
            labels: @json($yearlyLabels),
            datasets: [
                {
                    label: 'Hujjatlar',
                    data: @json($yearlyDocuments),
                    backgroundColor: verticalGradient(yearlyCanvas, 'rgba(29, 78, 216, 0.9)', 'rgba(29, 78, 216, 0.22)'),
                    borderColor: '#1d4ed8',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Jami summa',
                    data: @json($yearlyFinal),
                    type: 'line',
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.12)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#7c3aed',
                    pointRadius: 4,
                    lineTension: 0.35,
                    fill: true,
                    yAxisID: 'amount'
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, fontColor: '#475569' } },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const value = tooltipItem.yLabel;
                        return dataset.yAxisID === 'amount'
                            ? dataset.label + ': ' + moneyFormatter(value)
                            : dataset.label + ': ' + value;
                    }
                }
            },
            scales: {
                xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#64748b' } }],
                yAxes: [
                    {
                        id: 'y',
                        position: 'left',
                        gridLines: sharedGrid,
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#64748b' }
                    },
                    {
                        id: 'amount',
                        position: 'right',
                        gridLines: { drawOnChartArea: false, drawBorder: false },
                        ticks: { beginAtZero: true, callback: moneyFormatter, fontColor: '#64748b' }
                    }
                ]
            }
        }
    });
});
</script>
@endsection
