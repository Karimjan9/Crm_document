@extends('template')

@section('body')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><h3 class="mb-1">Finance dashboard</h3><p class="text-muted mb-0">Revenue - Direct Cost - Branch Expense = Gross Profit</p></div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="{{ route('finance.filials.index') }}">Filial P&amp;L</a><a class="btn btn-outline-primary" href="{{ route('finance.ledger.index') }}">Payment ledger</a><a class="btn btn-outline-danger" href="{{ route('finance.margin-leaks.index') }}">Margin leak signals</a><a class="btn btn-outline-secondary" href="{{ route('finance.lookups.index') }}">Finance sozlamalari</a><form class="d-flex gap-2" method="GET"><input class="form-control" type="date" name="date_from" value="{{ $dashboard['period']['from'] }}"><input class="form-control" type="date" name="date_to" value="{{ $dashboard['period']['to'] }}"><button class="btn btn-primary">Ko'rsatish</button></form></div>
    </div>
    <div class="row g-3 mb-4">
        @foreach([['Revenue', $dashboard['kpis']['revenue'], 'primary'], ['Cash received', $dashboard['kpis']['paid'], 'info'], ['Outstanding', $dashboard['kpis']['outstanding'], 'warning'], ['Direct Cost', $dashboard['kpis']['direct_cost'], 'danger'], ['Branch Expense', $dashboard['kpis']['branch_expense'], 'warning'], ['Gross Profit', $dashboard['kpis']['gross_profit'], 'success']] as [$label, $value, $color])
            <div class="col-md-3"><div class="card border-{{ $color }} h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h4 class="mt-2 mb-0 text-{{ $color }}">{{ number_format($value, 0, ',', ' ') }} UZS</h4></div></div></div>
        @endforeach
    </div>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card"><div class="card-header fw-bold d-flex justify-content-between align-items-center"><span>Filial rentabelligi</span><a href="{{ route('finance.filials.index') }}" class="small">To'liq P&amp;L →</a></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Filial</th><th>Revenue</th><th>Direct</th><th>Branch</th><th>Gross Profit</th></tr></thead><tbody>@forelse($dashboard['branches'] as $branch)<tr><td><a href="{{ route('finance.filials.show', $branch['id']) }}">{{ $branch['name'] }}</a> <small class="text-muted">({{ $branch['orders'] }})</small></td><td>{{ number_format($branch['revenue'],0,',',' ') }}</td><td>{{ number_format($branch['direct_cost'],0,',',' ') }}</td><td>{{ number_format($branch['branch_expense'],0,',',' ') }}</td><td class="fw-bold">{{ number_format($branch['gross_profit'],0,',',' ') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>@endforelse</tbody></table></div></div></div>
        <div class="col-lg-5"><div class="card"><div class="card-header fw-bold">Xizmat rentabelligi</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Xizmat</th><th>Revenue</th><th>Direct</th><th>Gross</th><th>Margin</th></tr></thead><tbody>@forelse($dashboard['services'] as $service)<tr><td>{{ $service['name'] }}</td><td>{{ number_format($service['revenue'],0,',',' ') }}</td><td>{{ number_format($service['direct_cost'],0,',',' ') }}</td><td>{{ number_format($service['gross_profit_before_branch_expense'],0,',',' ') }}</td><td><span class="badge bg-{{ $service['margin'] < 0 ? 'danger' : ($service['margin'] < 20 ? 'warning' : 'success') }}">{{ $service['margin'] }}%</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>@endforelse</tbody></table></div></div></div>
    </div>
</div>
@endsection
