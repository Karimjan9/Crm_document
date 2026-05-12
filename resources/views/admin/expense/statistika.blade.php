@extends('template')

@section('style')
<style>
    .expense-dashboard {
        background: #f5f7fb;
        min-height: calc(100vh - 64px);
        color: #172033;
    }

    .expense-dashboard .page-content {
        padding: 22px;
    }

    .expense-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .expense-title h4 {
        margin: 0;
        color: #102a56;
        font-size: 24px;
        font-weight: 700;
    }

    .expense-title p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .expense-link,
    .expense-filter-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        border-radius: 8px;
        font-weight: 700;
        white-space: nowrap;
    }

    .expense-link {
        padding: 10px 14px;
        color: #fff;
        background: #1d4ed8;
        text-decoration: none;
    }

    .expense-link:hover {
        color: #fff;
        background: #1e40af;
    }

    .expense-panel,
    .expense-card,
    .expense-table-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .expense-filter {
        padding: 16px;
        margin-bottom: 18px;
    }

    .expense-filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(140px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .expense-field label {
        display: block;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .expense-field input,
    .expense-field select {
        width: 100%;
        height: 40px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 10px;
        color: #172033;
        background: #fff;
    }

    .expense-filter-actions {
        display: flex;
        gap: 8px;
    }

    .expense-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .expense-card {
        position: relative;
        overflow: hidden;
        padding: 16px;
    }

    .expense-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: var(--accent, #1d4ed8);
    }

    .expense-card span {
        display: block;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .expense-card strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 21px;
        line-height: 1.2;
    }

    .expense-chart-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 14px;
        margin-bottom: 18px;
    }

    .expense-panel {
        overflow: hidden;
    }

    .expense-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .expense-panel-header h5 {
        margin: 0;
        color: #102a56;
        font-size: 16px;
        font-weight: 700;
    }

    .expense-panel-header span {
        color: #64748b;
        font-size: 13px;
    }

    .expense-chart-box {
        height: 320px;
        padding: 16px;
    }

    .expense-table-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 14px;
    }

    .expense-table-panel {
        overflow: hidden;
    }

    .expense-table {
        width: 100%;
        margin: 0;
    }

    .expense-table thead th {
        padding: 12px;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .expense-table td {
        padding: 12px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .expense-money {
        color: #0f172a;
        font-weight: 800;
        white-space: nowrap;
    }

    .expense-muted {
        color: #64748b;
        font-size: 12px;
    }

    @media (max-width: 1280px) {
        .expense-filter-grid { grid-template-columns: repeat(3, minmax(150px, 1fr)); }
        .expense-summary-grid { grid-template-columns: repeat(2, minmax(150px, 1fr)); }
        .expense-chart-grid,
        .expense-table-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .expense-dashboard .page-content { padding: 14px; }
        .expense-header { flex-direction: column; }
        .expense-link { width: 100%; }
        .expense-filter-grid,
        .expense-summary-grid { grid-template-columns: 1fr; }
        .expense-filter-actions { flex-direction: column; }
        .expense-chart-box { height: 280px; }
    }
</style>
@endsection

@section('body')
@php
    $money = fn ($amount) => number_format((float) $amount, 0, '.', ' ');
    $monthlyLabels = collect($monthlyStats)->pluck('label')->all();
    $monthlyAmounts = collect($monthlyStats)->pluck('amount')->all();
    $monthlyCounts = collect($monthlyStats)->pluck('expenses')->all();
    $filialLabels = collect($filialStats)->pluck('label')->all();
    $filialAmounts = collect($filialStats)->pluck('amount')->all();
    $userLabels = collect($userStats)->pluck('label')->all();
    $userAmounts = collect($userStats)->pluck('amount')->all();
@endphp

<div class="page-wrapper expense-dashboard">
    <div class="page-content">
        <div class="expense-header">
            <div class="expense-title">
                <h4>Xarajatlar statistikasi</h4>
                <p>Filial, xodim va davr bo'yicha xarajatlar nazorati.</p>
            </div>
            <a href="{{ route($routePrefix . '.expense.index') }}" class="expense-link">
                <i class="bx bx-receipt"></i>
                Xarajatlar
            </a>
        </div>

        <form method="GET" action="{{ route($routePrefix . '.statistika') }}" class="expense-panel expense-filter">
            <div class="expense-filter-grid">
                <div class="expense-field">
                    <label>Filial</label>
                    <select name="filial_id">
                        <option value="">Barchasi</option>
                        @foreach($filials as $filial)
                            <option value="{{ $filial->id }}" @selected((string) request('filial_id') === (string) $filial->id)>{{ $filial->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="expense-field">
                    <label>Foydalanuvchi</label>
                    <select name="user_id">
                        <option value="">Barchasi</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="expense-field">
                    <label>Yil</label>
                    <select name="year">
                        @foreach($yearOptions as $year)
                            <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="expense-field">
                    <label>Oy</label>
                    <select name="month">
                        <option value="">Barcha oylar</option>
                        @foreach($monthNames as $value => $label)
                            <option value="{{ $value }}" @selected((int) $selectedMonth === (int) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="expense-field">
                    <label>Sana dan</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="expense-field">
                    <label>Sana gacha</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="expense-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-filter-alt"></i>
                        Ko'rish
                    </button>
                    <a href="{{ route($routePrefix . '.statistika') }}" class="btn btn-light">Tozalash</a>
                </div>
            </div>
        </form>

        <div class="expense-summary-grid">
            <div class="expense-card" style="--accent:#1d4ed8">
                <span>Jami xarajat</span>
                <strong>{{ $money($summary['total_amount']) }} so'm</strong>
            </div>
            <div class="expense-card" style="--accent:#0f766e">
                <span>Operatsiyalar</span>
                <strong>{{ $summary['expense_count'] }}</strong>
            </div>
            <div class="expense-card" style="--accent:#7c3aed">
                <span>Filiallar</span>
                <strong>{{ $summary['filial_count'] }}</strong>
            </div>
            <div class="expense-card" style="--accent:#f59e0b">
                <span>Xodimlar</span>
                <strong>{{ $summary['user_count'] }}</strong>
            </div>
            <div class="expense-card" style="--accent:#dc2626">
                <span>O'rtacha xarajat</span>
                <strong>{{ $money($summary['average_amount']) }} so'm</strong>
            </div>
        </div>

        <div class="expense-chart-grid">
            <div class="expense-panel">
                <div class="expense-panel-header">
                    <h5>{{ $selectedYear }} yil oylar kesimi</h5>
                    <span>Summa va operatsiya soni</span>
                </div>
                <div class="expense-chart-box">
                    <canvas id="expenseMonthlyChart"></canvas>
                </div>
            </div>
            <div class="expense-panel">
                <div class="expense-panel-header">
                    <h5>Xodimlar ulushi</h5>
                    <span>Top 12</span>
                </div>
                <div class="expense-chart-box">
                    <canvas id="expenseUserChart"></canvas>
                </div>
            </div>
        </div>

        <div class="expense-panel mb-3">
            <div class="expense-panel-header">
                <h5>Filiallar bo'yicha xarajat</h5>
                <span>Eng yuqori summalar</span>
            </div>
            <div class="expense-chart-box">
                <canvas id="expenseFilialChart"></canvas>
            </div>
        </div>

        <div class="expense-table-grid">
            <div class="expense-table-panel">
                <div class="expense-panel-header">
                    <h5>Filiallar reytingi</h5>
                    <span>Top 12</span>
                </div>
                <div class="table-responsive">
                    <table class="table expense-table">
                        <thead>
                            <tr>
                                <th>Filial</th>
                                <th>Soni</th>
                                <th>Jami</th>
                                <th>O'rtacha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filialStats as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['expenses'] }}</td>
                                    <td><span class="expense-money">{{ $money($row['amount']) }}</span></td>
                                    <td><span class="expense-money">{{ $money($row['average']) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Ma'lumot yo'q.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="expense-table-panel">
                <div class="expense-panel-header">
                    <h5>So'nggi xarajatlar</h5>
                    <span>Oxirgi 60 yozuv</span>
                </div>
                <div class="table-responsive">
                    <table class="table expense-table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Xodim</th>
                                <th>Filial</th>
                                <th>Summa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>
                                        {{ optional($expense->created_at)->format('d.m.Y') }}
                                        <div class="expense-muted">{{ optional($expense->created_at)->format('H:i') }}</div>
                                    </td>
                                    <td>{{ $expense->user?->name ?? 'Noma\'lum' }}</td>
                                    <td>{{ $expense->filial?->name ?? 'Noma\'lum' }}</td>
                                    <td><span class="expense-money">{{ $money($expense->amount) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Ma'lumot yo'q.</td></tr>
                            @endforelse
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

    const palette = ['#1d4ed8', '#0f766e', '#7c3aed', '#f59e0b', '#dc2626', '#0891b2', '#4f46e5', '#16a34a', '#be123c', '#9333ea', '#ca8a04', '#334155'];

    function gradient(canvas, from, to) {
        const ctx = canvas.getContext('2d');
        const fill = ctx.createLinearGradient(0, 0, 0, canvas.height || 320);
        fill.addColorStop(0, from);
        fill.addColorStop(1, to);
        return fill;
    }

    const sharedGrid = {
        color: 'rgba(148, 163, 184, 0.18)',
        zeroLineColor: 'rgba(148, 163, 184, 0.32)',
        drawBorder: false
    };

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
            ctx.font = '700 22px Roboto, Arial, sans-serif';
            ctx.fillText(center.title, (area.left + area.right) / 2, (area.top + area.bottom) / 2 - 4);
            ctx.fillStyle = '#64748b';
            ctx.font = '600 12px Roboto, Arial, sans-serif';
            ctx.fillText(center.subtitle, (area.left + area.right) / 2, (area.top + area.bottom) / 2 + 18);
            ctx.restore();
        }
    };
    Chart.plugins.register(centerTextPlugin);

    const monthlyCanvas = document.getElementById('expenseMonthlyChart');
    new Chart(monthlyCanvas, {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                {
                    label: 'Xarajat summasi',
                    data: @json($monthlyAmounts),
                    backgroundColor: gradient(monthlyCanvas, 'rgba(29, 78, 216, 0.88)', 'rgba(29, 78, 216, 0.22)'),
                    borderColor: '#1d4ed8',
                    borderWidth: 1,
                    yAxisID: 'amount'
                },
                {
                    label: 'Operatsiyalar',
                    data: @json($monthlyCounts),
                    type: 'line',
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.12)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0f766e',
                    pointRadius: 4,
                    lineTension: 0.35,
                    fill: true,
                    yAxisID: 'count'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                        id: 'amount',
                        position: 'left',
                        gridLines: sharedGrid,
                        ticks: { beginAtZero: true, callback: moneyFormatter, fontColor: '#64748b' }
                    },
                    {
                        id: 'count',
                        position: 'right',
                        gridLines: { drawOnChartArea: false, drawBorder: false },
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#64748b' }
                    }
                ]
            }
        }
    });

    const userTotal = @json(array_sum($userAmounts));
    new Chart(document.getElementById('expenseUserChart'), {
        type: 'doughnut',
        data: {
            labels: @json($userLabels),
            datasets: [{
                data: @json($userAmounts),
                backgroundColor: palette,
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 68,
            centerText: {
                title: moneyFormatter(userTotal),
                subtitle: 'jami'
            },
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, fontColor: '#475569' } },
            tooltips: {
                callbacks: {
                    label: function (tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        return data.labels[tooltipItem.index] + ': ' + moneyFormatter(dataset.data[tooltipItem.index]);
                    }
                }
            }
        }
    });

    const filialCanvas = document.getElementById('expenseFilialChart');
    new Chart(filialCanvas, {
        type: 'horizontalBar',
        data: {
            labels: @json($filialLabels),
            datasets: [{
                label: 'Xarajat summasi',
                data: @json($filialAmounts),
                backgroundColor: gradient(filialCanvas, 'rgba(124, 58, 237, 0.86)', 'rgba(15, 118, 110, 0.2)'),
                borderColor: '#7c3aed',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            tooltips: {
                callbacks: {
                    label: function (tooltipItem) {
                        return moneyFormatter(tooltipItem.xLabel);
                    }
                }
            },
            scales: {
                xAxes: [{ gridLines: sharedGrid, ticks: { beginAtZero: true, callback: moneyFormatter, fontColor: '#64748b' } }],
                yAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#475569' } }]
            }
        }
    });
});
</script>
@endsection
