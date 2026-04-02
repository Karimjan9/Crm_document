@extends('template')

@section('style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body { 
    font-family: 'Poppins', sans-serif; 
    background: #f8fafc; 
    color: #1e293b; 
}

.page-wrapper { 
    padding: 24px; 
}

.stat-card { 
    border-radius: 12px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
    padding: 20px; 
    margin-bottom: 20px; 
    background: #fff; 
    font-family: 'Poppins', sans-serif;
}

.filter-card { 
    display: flex; 
    gap: 12px; 
    flex-wrap: wrap; 
    margin-bottom: 20px; 
    padding: 20px; 
    border-radius: 12px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
    background: #fff; 
    font-family: 'Poppins', sans-serif;
}

.filter-card select, .filter-card input { 
    padding: 8px 12px; 
    border-radius: 10px; 
    border: 1px solid #d1d5db; 
    font-size: 14px; 
    font-family: 'Poppins', sans-serif;
}

.filter-card button { 
    border-radius: 10px; 
    border: none; 
    background: linear-gradient(135deg,#2563eb,#3b82f6); 
    color: #fff; 
    padding: 8px 16px; 
    cursor: pointer; 
    transition: all 0.3s; 
    font-family: 'Poppins', sans-serif;
}

.filter-card button:hover { 
    opacity: 0.9; 
}

.table-hover tbody tr:hover { 
    background-color: #eef4ff; 
    transform: translateX(2px); 
    transition: all 0.2s ease; 
}

.badge { 
    padding: 5px 10px; 
    border-radius: 10px; 
    font-size: 13px; 
    font-weight: 500; 
    font-family: 'Poppins', sans-serif;
}

.bg-success { 
    background-color: #22c55e; 
    color: #fff; 
    font-family: 'Poppins', sans-serif;
}

.bg-warning { 
    background-color: #facc15; 
    color: #1e293b; 
    font-family: 'Poppins', sans-serif;
}

@media(max-width:768px){ 
    .filter-card{ flex-direction: column; } 
}
</style>
@endsection


@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <h4 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Xarajatlar Statistikasi</h4>

        {{-- FILTERS --}}
        <form method="GET" id="filterForm" class="filter-card mb-4">
            <input type="text" id="monthYear" name="month_year" placeholder="Oy va yil" 
                value="{{ $year_filter && $month_filter ? \Carbon\Carbon::create($year_filter,$month_filter)->format('F/Y') : '' }}">
        </form>

        {{-- TOTAL AMOUNT --}}
        <div class="stat-card d-flex justify-content-between align-items-center mb-4">
            <h5>Jami xarajat: <strong>{{ number_format($total_amount, 0, ',', ' ') }} so'm</strong></h5>
        </div>

        {{-- CHART --}}
        <div class="stat-card mb-4">
            <canvas id="expenseChart" height="100"></canvas>
        </div>

        {{-- TABLE --}}
        <div class="card shadow mb-4">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Foydalanuvchi</th>
                            <th>Summa</th>
                            <th>Izoh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>#{{ $expense->id }}</td>
                            <td>{{ $expense->user->name ?? '-' }}</td>
                            <td><strong>{{ number_format($expense->amount, 0, ',', ' ') }} so'm</strong></td>
                            <td>{{ $expense->description ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Xarajatlar mavjud emas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script_include_end_body')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
// Month & Year picker
flatpickr("#monthYear", {
    plugins: [
        new monthSelectPlugin({
            shorthand: true,
            dateFormat: "Y-m",   // backend uchun
            altFormat: "F/Y",    // inputda ko‘rinadigan format
            theme: "light"
        })
    ],
    altInput: true,
    onChange: function(selectedDates, dateStr, instance) {
        document.getElementById('filterForm').submit();
    }
});

// Chart
const ctx = document.getElementById('expenseChart').getContext('2d');
const labels = {!! json_encode($chartData->keys()->map(function($user_id) use ($users) {
    $user = $users->firstWhere('id', $user_id);
    return $user ? $user->name : 'Noma\'lum';
})) !!};

const backgroundColors = [
    '#2563eb','#3b82f6','#60a5fa','#2563eb','#1d4ed8','#3b82f6','#60a5fa','#2563eb'
];

const data = {
    labels: labels,
    datasets: [{
        label: 'Xarajatlar summasi (so\'m)',
        data: {!! json_encode($chartData->values()) !!},
        backgroundColor: backgroundColors,
        borderColor: backgroundColors.map(c => c),
        borderWidth: 1
    }]
};

new Chart(ctx, {
    type: 'bar',
    data: data,
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true },
            x: { ticks: { autoSkip: false } }
        }
    }
});
</script>
@endsection
