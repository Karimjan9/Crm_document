@extends('template')

@section('style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
    .stat-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid py-4">

            <h4><i class="fas fa-chart-bar me-2"></i>Xarajatlar Statistika</h4>

            {{-- Filter form --}}
           

              

            {{-- Total Amount --}}
            <div class="stat-card bg-light d-flex justify-content-between align-items-center">
                <h5>Jami xarajat: <strong>{{ number_format($total_amount, 0, ',', ' ') }} so'm</strong></h5>
            </div>

            {{-- Chart --}}
            <div class="stat-card">
                <canvas id="expenseChart" height="100"></canvas>
            </div>

            {{-- Table --}}
            <div class="card shadow mt-4">
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
</div>
@endsection

@section('script_include_end_body')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('expenseChart').getContext('2d');

const labels = {!! json_encode($chartData->keys()->map(function($user_id) use ($users) {
    $user = $users->firstWhere('id', $user_id);
    return $user ? $user->name : 'Noma\'lum';
})) !!};

const data = {
    labels: labels,
    datasets: [{
        label: 'Xarajatlar summasi (so\'m)',
        data: {!! json_encode($chartData->values()) !!},
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

new Chart(ctx, {
    type: 'bar',
    data: data,
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endsection
