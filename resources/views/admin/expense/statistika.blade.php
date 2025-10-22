@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

    :root {
        --text-color: #15172a;
        --blue-main: #1e3a8a;
        --blue-light: #2563eb;
        --blue-bg: #f0f6ff;
        --white: #ffffff;
        --border-color: #e5e7eb;
        --green: #16a34a;
        --red: #dc2626;
    }

    body {
        font-family: "Inter", sans-serif;
        background: var(--blue-bg);
        color: var(--text-color);
    }

    .page-wrapper { padding: 24px; }

    .card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
        border: 1px solid var(--border-color);
        transition: 0.3s;
    }

    .card:hover { box-shadow: 0 6px 16px rgba(37,99,235,0.15); }

    .card-header {
        font-weight: 600;
        color: var(--blue-main);
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        font-size: 16px;
    }

    .card-body {
        padding: 20px;
    }

    .summary-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--white);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: 0.3s;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .summary-card h5 { margin-bottom: 5px; font-size: 18px; color: var(--blue-main); }
    .summary-card span { font-size: 14px; color: #555; }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }

    canvas {
        width: 100% !important;
        height: 320px !important;
    }

    h6 {
        color: var(--blue-main);
        font-weight: 600;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
            <div class="breadcrumb-title pe-3">📊 Xarajatlar statistikasi</div>
            <button class="btn btn-custom">+ Yangi xarajat</button>
        </div>

        <!-- Summary section -->
        <div class="stat-grid mb-4">
            <div class="summary-card">
                <div>
                    <h5>Joriy oy xarajatlari</h5>
                    <span>O‘tgan oyga nisbatan +12%</span>
                </div>
                <div class="text-end">
                    <h3 style="color: var(--red);">12 500 000 so‘m</h3>
                </div>
            </div>

            <div class="summary-card">
                <div>
                    <h5>Yillik jami</h5>
                    <span>2025 yil uchun</span>
                </div>
                <div class="text-end">
                    <h3 style="color: var(--blue-main);">85 400 000 so‘m</h3>
                </div>
            </div>

            <div class="summary-card">
                <div>
                    <h5>Eng ko‘p xarajat filial</h5>
                    <span>Toshkent filiali</span>
                </div>
                <div class="text-end">
                    <h3 style="color: var(--green);">34%</h3>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <!-- Oylik xarajatlar -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">📅 Oylik xarajatlar (so‘mda)</div>
                    <div class="card-body">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Filiallar kesimida taqsimot -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">🏢 Filiallar bo‘yicha xarajat taqsimoti</div>
                    <div class="card-body">
                        <canvas id="branchChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadval misoli -->
        <div class="card mt-4">
            <div class="card-header">📋 Filiallar kesimida batafsil statistik jadval</div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Filial nomi</th>
                            <th>Jami xarajat (so‘m)</th>
                            <th>Ulushi (%)</th>
                            <th>Eng ko‘p sarf yo‘nalishi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>Toshkent</td><td>29 000 000</td><td>34%</td><td>Ofis jihozlari</td></tr>
                        <tr><td>2</td><td>Samarqand</td><td>22 000 000</td><td>26%</td><td>Reklama</td></tr>
                        <tr><td>3</td><td>Andijon</td><td>18 000 000</td><td>21%</td><td>Transport</td></tr>
                        <tr><td>4</td><td>Farg‘ona</td><td>15 000 000</td><td>19%</td><td>Kommunal</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripte_include_end_body')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 📅 Oylik xarajatlar (Line Chart)
    const ctx1 = document.getElementById('monthlyChart');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt'],
            datasets: [{
                label: 'Xarajatlar (so‘m)',
                data: [9500000, 10200000, 8600000, 11500000, 9800000, 12500000, 13400000, 11000000, 9000000, 12500000],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            plugins: { legend: { display: false }},
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' so‘m' }}
            }
        }
    });

    // 🏢 Filiallar kesimida taqsimot (Doughnut Chart)
    const ctx2 = document.getElementById('branchChart');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Toshkent', 'Samarqand', 'Andijon', 'Farg‘ona'],
            datasets: [{
                data: [34, 26, 21, 19],
                backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#dc2626'],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '% ulush';
                        }
                    }
                }
            }
        }
    });

});
</script>
@endsection
