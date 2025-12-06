{{-- resources/views/admin/expense/part/index.blade.php --}}
@extends('template')

@section('style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --blue-main: #3b82f6;
    --blue-hover: #2563eb;
    --success: #22c55e;
    --danger: #ef4444;
    --light-bg: #f9fafb;
    --card-shadow: rgba(0, 0, 0, 0.1);
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--light-bg);
}

.page-wrapper {
    padding: 2rem;
}

.card {
    border-radius: 12px;
    box-shadow: 0 4px 10px var(--card-shadow);
    overflow: hidden;
}

.table-hover tbody tr:hover {
    background-color: #f1f5f9;
    cursor: pointer;
}

.table thead th {
    background-color: #f3f4f6;
    font-weight: 600;
}

.badge-expense {
    font-size: 0.85em;
    padding: 0.3em 0.6em;
    border-radius: 8px;
}

.btn-custom {
    border-radius: 8px;
    font-weight: 500;
}

.btn-success {
    background: var(--success);
    border: none;
}

.btn-success:hover {
    background: #16a34a;
}

.modal-header.bg-success {
    background: var(--success);
}

.modal-header.bg-primary {
    background: var(--blue-main);
}

.alert {
    border-radius: 8px;
}

.invalid-feedback {
    display: block;
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Flash message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold"><i class="fas fa-wallet me-2"></i>Xarajatlar Ro'yxati</h4>
                <button class="btn btn-success btn-custom" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus me-1"></i> Xarajat qo'shish
                </button>
            </div>

            <div class="card mb-4 shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Summa</th>
                                <th>Filial</th>
                                <th>Izoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>#{{ $expense->id }}</td>
                                    <td><strong>{{ number_format($expense->amount, 0, ',', ' ') }} so'm</strong></td>
                                    <td>{{ $expense->filial->name }}</td>
                                    <td>{{ $expense->description ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Xarajatlar mavjud emas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin_filial.expense_admin.store') }}" method="POST" id="addExpenseForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Xarajat qo'shish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Summa:</label>
                        <input type="number" name="amount" class="form-control" required min="1000">
                        @error('amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Izoh:</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Yopish</button>
                    <button type="submit" class="btn btn-success btn-custom">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Expense Modal --}}
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="" method="POST" id="editExpenseForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Xarajatni tahrirlash</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="expense_id" id="edit_expense_id">
                    <div class="mb-3">
                        <label class="form-label">Summa:</label>
                        <input type="number" name="amount" id="edit_amount" class="form-control" required min="1000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Filial:</label>
                        <input type="text" name="filial_id" id="edit_filial" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Izoh:</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Yopish</button>
                    <button type="submit" class="btn btn-primary btn-custom">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script_include_end_body')
<script>
$(document).ready(function(){

    // Edit tugmasi bosilganda modalga ma'lumotlarni qo'yish
    $('.edit-expense-btn').click(function(){
        let id = $(this).data('id');
        let amount = $(this).data('amount');
        let filial = $(this).data('filial');
        let description = $(this).data('description');

        $('#edit_expense_id').val(id);
        $('#edit_amount').val(amount);
        $('#edit_filial').val(filial);
        $('#edit_description').val(description);

        $('#editExpenseForm').attr('action', '/admin_filial/expense/' + id);

        $('#editExpenseModal').modal('show');
    });

});
</script>
@endsection
