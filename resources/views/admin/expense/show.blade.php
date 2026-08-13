@extends('template')

@section('body')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Xarajat #{{ $expense->id }}</h3>
            <div class="text-muted">{{ optional($expense->created_at)->format('d.m.Y H:i') }}</div>
        </div>
        <a class="btn btn-light" href="{{ route($routePrefix . '.expense.index') }}">Orqaga</a>
    </div>

    <div class="card card-body shadow-sm">
        <dl class="row mb-0">
            <dt class="col-sm-3">Filial</dt><dd class="col-sm-9">{{ $expense->filial?->name ?? "Noma'lum" }}</dd>
            <dt class="col-sm-3">Foydalanuvchi</dt><dd class="col-sm-9">{{ $expense->user?->name ?? "Noma'lum" }}</dd>
            <dt class="col-sm-3">Summa</dt><dd class="col-sm-9">{{ number_format((float) $expense->amount, 2, '.', ' ') }} {{ $expense->currency ?: 'UZS' }}</dd>
            <dt class="col-sm-3">Kategoriya</dt><dd class="col-sm-9">{{ $expense->category?->name ?? 'Umumiy' }}</dd>
            <dt class="col-sm-3">Vendor</dt><dd class="col-sm-9">{{ $expense->vendor?->name ?? '—' }}</dd>
            <dt class="col-sm-3">Payment method</dt><dd class="col-sm-9">{{ $expense->payment_method ?: 'cash' }}</dd>
            <dt class="col-sm-3">Expense turi</dt><dd class="col-sm-9">{{ $expense->expense_type ?: 'branch' }}</dd>
            <dt class="col-sm-3">Expense sanasi</dt><dd class="col-sm-9">{{ optional($expense->expense_date)->format('d.m.Y') ?: '—' }}</dd>
            <dt class="col-sm-3">Tasdiq</dt><dd class="col-sm-9">{{ $expense->approval_status === 'approved' ? 'Tasdiqlangan' : ($expense->approval_status === 'rejected' ? 'Rad etilgan' : 'Kutilmoqda') }} @if($expense->approver) ({{ $expense->approver->name }}) @endif</dd>
            <dt class="col-sm-3">Recurring</dt><dd class="col-sm-9">{{ $expense->is_recurring ? ($expense->recurrence_rule ?: 'Ha') : "Yo'q" }}</dd>
            <dt class="col-sm-3">Budget</dt><dd class="col-sm-9">{{ $expense->budget?->name ?? '—' }}</dd>
            <dt class="col-sm-3">Cost center</dt><dd class="col-sm-9">{{ $expense->costCenter?->name ?? '—' }}</dd>
            <dt class="col-sm-3">Receipt</dt>
            <dd class="col-sm-9">
                @if($expense->receipt_path)
                    <a href="{{ route('expenses.receipt', $expense) }}">{{ $expense->receipt_original_name ?: 'Yuklab olish' }}</a>
                @else
                    —
                @endif
            </dd>
            <dt class="col-sm-3">Izoh</dt><dd class="col-sm-9">{{ $expense->description ?: '—' }}</dd>
        </dl>
    </div>

    <div class="card card-body shadow-sm mt-3">
        <h5>Branch allocation</h5>
        @forelse($expense->allocations as $allocation)
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>{{ $allocation->filial?->name ?? "Noma'lum" }} @if($allocation->note) — {{ $allocation->note }} @endif</span>
                <strong>{{ number_format((float) $allocation->amount, 2, '.', ' ') }} ({{ number_format((float) $allocation->percentage, 2) }}%)</strong>
            </div>
        @empty
            <span class="text-muted">Taqsimot mavjud emas.</span>
        @endforelse
    </div>
</div>
@endsection
