@extends('template')

@section('body')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3>Margin leak detector</h3>
            <p class="text-muted mb-0">Chegirma, addon narxi, to'lov, rework, filial xarajati va naqd pul signallari.</p>
        </div>
        <form method="POST" action="{{ route('finance.margin-leaks.scan') }}">@csrf<button class="btn btn-primary">Tekshirish</button></form>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card card-body shadow-sm mb-3">
        <h5>Naqd pul reconciliation</h5>
        <form method="POST" action="{{ route('finance.cash-reconciliations.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-3"><label class="form-label">Filial</label><select name="filial_id" class="form-select" required><option value="">Tanlang</option>@foreach($filials as $filial)<option value="{{ $filial->id }}">{{ $filial->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Sana</label><input type="date" name="reconciliation_date" value="{{ today()->toDateString() }}" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label">Amaldagi summa</label><input type="number" name="actual_amount" min="0" step="0.01" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label">Kutilgan summa</label><input type="number" name="expected_amount" min="0" step="0.01" class="form-control" placeholder="Auto"></div>
            <div class="col-md-3"><label class="form-label">Izoh</label><div class="input-group"><input name="notes" class="form-control"><button class="btn btn-outline-primary">Saqlash</button></div></div>
        </form>
    </div>

    <div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Severity</th><th>Signal</th><th>Filial</th><th>Order</th><th>Summa</th><th>Sana</th><th></th></tr></thead>
        <tbody>
        @forelse($leaks as $leak)
            <tr>
                <td><span class="badge bg-{{ $leak->severity === 'high' ? 'danger' : ($leak->severity === 'medium' ? 'warning' : 'secondary') }}">{{ $leak->severity }}</span></td>
                <td>{{ $leak->message }} <small class="text-muted">({{ $leak->leak_type }})</small></td>
                <td>{{ $leak->filial?->name ?: '—' }}</td>
                <td>{{ $leak->order?->order_code ?: '—' }}</td>
                <td>{{ $leak->amount !== null ? number_format($leak->amount,0,',',' ') : '—' }}</td>
                <td>{{ optional($leak->detected_at)->format('d.m.Y H:i') }}</td>
                <td><form method="POST" action="{{ route('finance.margin-leaks.resolve',$leak) }}">@csrf<button class="btn btn-sm btn-outline-success">Yopish</button></form></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Ochiq signal yo'q.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
