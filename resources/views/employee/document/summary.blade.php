@extends('template')

@section('style')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
      rel="stylesheet">

<style>
    :root {
        --blue-main: #2563eb;
        --blue-hover: #1d4ed8;
        --danger: #dc3545;
        --warning: #f59e0b;
        --success: #16a34a;
        --light-bg: #f5f7fb;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--light-bg);
    }

    .page-content {
        padding: 20px;
    }

    .card {
        border-radius: 14px;
        border: none;
        overflow: hidden;
        background: #fff;
    }

    .card-header {
        background: var(--blue-main) !important;
        padding: 14px 18px;
        font-weight: 600;
    }

    table thead th {
        background: #eef1f6 !important;
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
    }

    .table-hover tbody tr:hover {
        background: #e8f0ff !important;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
    }

    .balance-negative {
        color: var(--danger);
        font-weight: 600;
    }

    .balance-positive {
        color: var(--success);
        font-weight: 600;
    }

    .btn-sm {
        border-radius: 8px;
        font-size: 12px;
        padding: 5px 10px;
    }

    .btn-primary {
        background: var(--blue-main);
        border: none;
    }

    .btn-primary:hover {
        background: var(--blue-hover);
    }

    .modal-content {
        border-radius: 14px;
    }

    .modal-header {
        border-bottom: none;
        font-weight: 600;
    }

    .modal-footer {
        border-top: none;
    }

    th.sortable {
        cursor: pointer;
        position: relative;
    }

    th.sortable::after {
        content: "\f0dc";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        margin-left: 6px;
        opacity: 0.5;
    }

    th.sortable.asc::after {
        content: "\f0de";
        opacity: 1;
    }

    th.sortable.desc::after {
        content: "\f0dd";
        opacity: 1;
    }
</style>
@endsection

@section('body')
@php
    $documentsCount = method_exists($documents, 'total') ? $documents->total() : $documents->count();
@endphp

<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid py-4">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-layer-group me-2"></i>Hujjatlar Ro'yxati</h4>
                    <span class="badge bg-light text-dark px-3 py-2 shadow-sm">
                        Jami: {{ $documentsCount }} ta hujjat
                    </span>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0" id="documents-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="id">ID</th>
                                <th class="sortable" data-column="document_code">Hujjat Raqami</th>
                                <th class="sortable" data-column="final_price">Jami</th>
                                <th class="sortable" data-column="paid_amount">To'langan</th>
                                <th class="sortable" data-column="balance">Qoldiq</th>
                                <th>Chegirma</th>
                                <th>Izoh</th>
                                <th>Holati</th>
                                <th>To'lov</th>
                                <th>To'lov Tarixi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                @php
                                    $balance = $doc->final_price - $doc->paid_amount;
                                    $status = $balance <= 0 ? "To'langan" : ($doc->paid_amount > 0 ? 'Qisman' : 'Qarzdor');
                                    $badge = $balance <= 0 ? 'success' : ($doc->paid_amount > 0 ? 'warning' : 'danger');
                                @endphp
                                <tr class="document-row"
                                    data-document-id="{{ $doc->id }}"
                                    data-id="{{ $doc->id }}"
                                    data-document_code="{{ $doc->document_code }}"
                                    data-final_price="{{ $doc->final_price }}"
                                    data-paid_amount="{{ $doc->paid_amount }}"
                                    data-balance="{{ $balance }}">
                                    <td>#{{ $doc->id }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $doc->document_code }}</span></td>
                                    <td><b>{{ number_format($doc->final_price) }} so'm</b></td>
                                    <td class="text-success fw-bold">{{ number_format($doc->paid_amount) }} so'm</td>
                                    <td>
                                        <span class="{{ $balance > 0 ? 'balance-negative' : 'balance-positive' }}">
                                            {{ number_format($balance) }} so'm
                                        </span>
                                    </td>
                                    <td>
                                        @if($doc->discount > 0)
                                            <span class="badge bg-warning text-dark">{{ number_format($doc->discount) }} so'm</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->description)
                                            <button class="btn btn-sm btn-info description-btn" data-description="{{ $doc->description }}">Ko'rish</button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{ $badge }} status-badge">{{ $status }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary make-payment-btn"
                                            data-document-id="{{ $doc->id }}"
                                            data-document-code="{{ $doc->document_code }}"
                                            data-balance="{{ $balance }}">
                                            <i class="fas fa-money-bill"></i> To'lov
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary payment-history-btn"
                                            data-document-id="{{ $doc->id }}">
                                            <i class="fas fa-history"></i> Tarix
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($documents, 'links'))
                    <div class="p-3">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.payment_modal')
@include('partials.description_modal')
@include('partials.history_modal')
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<script>
$(document).ready(function(){

    $('th.sortable').click(function() {
        const table = $(this).closest('table');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr.document-row').toArray();
        const column = $(this).data('column');
        const asc = !$(this).hasClass('asc');

        rows.sort(function(a, b) {
            let valA = $(a).data(column);
            let valB = $(b).data(column);

            if ($.isNumeric(valA) && $.isNumeric(valB)) {
                valA = parseFloat(valA);
                valB = parseFloat(valB);
            } else {
                valA = String(valA).toLowerCase();
                valB = String(valB).toLowerCase();
            }

            return asc ? (valA > valB ? 1 : -1) : (valA < valB ? 1 : -1);
        });

        tbody.append(rows);
        $('th.sortable').removeClass('asc desc');
        $(this).addClass(asc ? 'asc' : 'desc');
    });

    $('.make-payment-btn').click(function(e){
        e.stopPropagation();

        const id = $(this).data('document-id');
        const code = $(this).data('document-code');
        const balance = $(this).data('balance');

        $('#document_id').val(id);
        $('#document_code').val(code);
        $('#balance').val(balance + " so'm");
        $('input[name="amount"]').attr('max', balance).val('');
        $('#paymentModal').modal('show');
    });

    $('.description-btn').click(function(e){
        e.stopPropagation();
        $('#descriptionContent').text($(this).data('description'));
        $('#descriptionModal').modal('show');
    });

    const paymentHistoryBase = "{{ route('employee.payments', ['document' => '__id__']) }}";

    $('.payment-history-btn').click(function(e){
        e.stopPropagation();

        const docId = $(this).data('document-id');

        $.ajax({
            url: paymentHistoryBase.replace('__id__', docId),
            method: "GET",
            success: function(res){
                let tbody = '';

                res.forEach(function(payment, index){
                    const date = new Date(payment.created_at);
                    const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = monthNames[date.getMonth()];
                    const year = date.getFullYear();
                    const hours = date.getHours().toString().padStart(2, '0');
                    const minutes = date.getMinutes().toString().padStart(2, '0');
                    const formatted = `${day} ${month} ${year}, ${hours}:${minutes}`;

                    tbody += `<tr>
                        <td>${index + 1}</td>
                        <td>${Number(payment.amount).toLocaleString()} so'm</td>
                        <td>${payment.payment_type}</td>
                        <td>${payment.paid_by_admin_id}</td>
                        <td>${formatted}</td>
                    </tr>`;
                });

                $('#historyTableBody').html(tbody);
                $('#historyModal').modal('show');
            }
        });
    });

    $('#paymentForm').submit(function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ route('employee.add_payment') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                document_id: $('#document_id').val(),
                amount: $('input[name="amount"]').val(),
                payment_type: $('select[name="payment_type"]').val()
            },
            success: function(res){
                if (res.status === 'success') {
                    location.reload();
                }
            },
            error: function(xhr){
                alert(xhr.status === 422 ? xhr.responseJSON.message : "Xatolik! Server to'lovni qabul qilmadi.");
            }
        });
    });
});
</script>
@endsection
