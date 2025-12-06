{{-- resources/views/admin/expense/part/index.blade.php --}}
@extends('template')

@section('style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
        cursor: pointer;
    }
    .badge-expense {
        font-size: 0.85em;
        padding: 0.3em 0.6em;
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid py-4">

            {{-- Flash message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between mb-3">
                <h4><i class="fas fa-wallet me-2"></i>Xarajatlar Ro'yxati</h4>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus"></i> Xarajat qo'shish
                </button>
            </div>

            <div class="card shadow">
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
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
                                    {{-- <td>
                                        <button class="btn btn-sm btn-primary edit-expense-btn"
                                            data-id="{{ $expense->id }}"
                                            data-amount="{{ $expense->amount }}"
                                            data-filial="{{ $expense->filial_id }}"
                                            data-description="{{ $expense->description ?? '' }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Xarajatlar mavjud emas</td>
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
                        <label>Summa:</label>
                        <input type="number" name="amount" class="form-control" required min="1000">
                    </div>
                   @error('amount')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <div class="mb-3">
                        <label>Izoh:</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Yopish</button>
                    <button type="submit" class="btn btn-success">Saqlash</button>
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
                        <label>Summa:</label>
                        <input type="number" name="amount" id="edit_amount" class="form-control" required min="1000">
                    </div>
                    <div class="mb-3">
                        <label>Filial:</label>
                        <input type="text" name="filial_id" id="edit_filial" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Izoh:</label>
                        <textarea name="description" id="edit_description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Yopish</button>
                    <button type="submit" class="btn btn-primary">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script_include_end_body')
{{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script> --}}

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

        // Form actionni update route ga o'rnatish
        $('#editExpenseForm').attr('action', '/admin_filial/expense/' + id);

        $('#editExpenseModal').modal('show');
    });

});
</script>
@endsection
