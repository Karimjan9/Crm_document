@extends('template')

@php
    $allocationRows = old('allocations');
    if (!is_array($allocationRows) || $allocationRows === []) {
        $allocationRows = $expense->exists && $expense->allocations->isNotEmpty()
            ? $expense->allocations->map(fn ($allocation) => ['filial_id' => $allocation->filial_id, 'amount' => $allocation->amount])->all()
            : [['filial_id' => $expense->filial_id, 'amount' => $expense->amount]];
    }
@endphp

@section('body')
@php($editing = $expense->exists)
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>{{ $editing ? 'Xarajatni tahrirlash' : 'Yangi xarajat' }}</h3>
        <a class="btn btn-light" href="{{ route($routePrefix . '.expense.index') }}">Orqaga</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route($routePrefix . '.expense.update', $expense) : route($routePrefix . '.expense.store') }}" class="card card-body shadow-sm">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="mb-3">
            <label class="form-label">Filial</label>
            <select name="filial_id" class="form-select" required>
                <option value="">Tanlang</option>
                @foreach($filials as $filial)
                    <option value="{{ $filial->id }}" @selected((string) old('filial_id', $expense->filial_id) === (string) $filial->id)>{{ $filial->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Foydalanuvchi</label>
            <select name="user_id" class="form-select">
                <option value="">Joriy foydalanuvchi</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', $expense->user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->login }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Summa</label>
            <input type="number" name="amount" min="1000" step="0.01" required class="form-control" value="{{ old('amount', $expense->amount) }}">
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Xarajat kategoriyasi</label>
                <select name="category_id" class="form-select">
                    <option value="">Umumiy xarajat</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $expense->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select">
                    <option value="">Vendor tanlanmagan</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $expense->vendor_id) === (string) $vendor->id)>{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment method</label>
                <select name="payment_method" class="form-select">
                    @foreach(['cash' => 'Naqd', 'card' => 'Karta', 'bank_transfer' => 'Bank o‘tkazmasi', 'online' => 'Online', 'other' => 'Boshqa'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_method', $expense->payment_method ?: 'cash') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Expense turi</label>
                <select name="expense_type" class="form-select">
                    @foreach(['branch' => 'Branch expense', 'direct' => 'Direct cost', 'overhead' => 'Overhead'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('expense_type', $expense->expense_type ?: 'branch') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Xarajat sanasi</label>
                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Receipt fayli</label>
                <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                @if($expense->receipt_original_name)<small class="text-muted">Joriy fayl: {{ $expense->receipt_original_name }}</small>@endif
            </div>
            <div class="col-md-6">
                <label class="form-label">Budget</label>
                <select name="budget_id" class="form-select">
                    <option value="">Budget biriktirilmagan</option>
                    @foreach($budgets as $budget)
                        <option value="{{ $budget->id }}" @selected((string) old('budget_id', $expense->budget_id) === (string) $budget->id)>{{ $budget->name }} ({{ number_format($budget->amount, 0, ',', ' ') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cost center</label>
                <select name="cost_center_id" class="form-select">
                    <option value="">Cost center biriktirilmagan</option>
                    @foreach($costCenters as $costCenter)
                        <option value="{{ $costCenter->id }}" @selected((string) old('cost_center_id', $expense->cost_center_id) === (string) $costCenter->id)>{{ $costCenter->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tasdiqlovchi shaxs</label>
                <select name="approver_id" class="form-select">
                    <option value="">Avtomatik / tanlanmagan</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('approver_id', $expense->approver_id) === (string) $user->id)>{{ $user->name }} ({{ $user->login }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Approval status</label>
                <select name="approval_status" class="form-select">
                    @foreach(['pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('approval_status', $expense->approval_status ?: 'pending') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Branch allocation</label>
                <div id="allocationRows">
                    @foreach($allocationRows as $index => $allocation)
                        <div class="input-group mb-2 allocation-row">
                            <select name="allocations[{{ $index }}][filial_id]" class="form-select" required>
                                @foreach($filials as $filial)
                                    <option value="{{ $filial->id }}" @selected((string) ($allocation['filial_id'] ?? '') === (string) $filial->id)>{{ $filial->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="allocations[{{ $index }}][amount]" min="0.01" step="0.01" class="form-control" value="{{ $allocation['amount'] ?? '' }}" required aria-label="Branch allocation amount">
                            <button type="button" class="btn btn-outline-danger remove-allocation">×</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="addAllocation" class="btn btn-sm btn-outline-secondary">+ Filial qo'shish</button>
                <small class="d-block text-muted">Taqsimot summalari umumiy xarajat summasiga teng bo'lishi kerak.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Recurring expense</label>
                <div class="input-group">
                    <select name="recurrence_rule" class="form-select">
                        <option value="">Takrorlanmaydi</option>
                        @foreach(['weekly' => 'Haftalik', 'monthly' => 'Oylik', 'yearly' => 'Yillik'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('recurrence_rule', $expense->recurrence_rule) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="is_recurring" value="0">
                    <input type="checkbox" name="is_recurring" value="1" class="form-check-input ms-2" @checked(old('is_recurring', $expense->is_recurring))>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Izoh</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $expense->description) }}</textarea>
        </div>

        <button class="btn btn-primary" type="submit">Saqlash</button>
    </form>
</div>
<script>
    (() => {
        let index = {{ count($allocationRows) }};
        const rows = document.getElementById('allocationRows');
        document.getElementById('addAllocation')?.addEventListener('click', () => {
            const row = document.querySelector('.allocation-row')?.cloneNode(true);
            if (!row) return;
            row.querySelectorAll('select,input').forEach((field) => {
                field.name = field.name.replace(/allocations\[\d+\]/, `allocations[${index}]`);
                if (field.tagName === 'INPUT') field.value = '';
            });
            rows.appendChild(row);
            index++;
        });
        rows?.addEventListener('click', (event) => {
            if (!event.target.classList.contains('remove-allocation')) return;
            const allocationRows = rows.querySelectorAll('.allocation-row');
            if (allocationRows.length > 1) event.target.closest('.allocation-row').remove();
        });
    })();
</script>
@endsection
