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

    /* Sort iconlar */
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

            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-file-contract me-2"></i>Hujjatlar Ro'yxati</h4>
                            <span class="badge bg-light text-dark">Jami: {{ $documents->count() }} hujjat</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover table-striped mb-0" id="documents-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-column="id">ID</th>
                                        <th class="sortable" data-column="document_code">Hujjat Raqami</th>
                                        <th class="sortable" data-column="final_price">Jami Summa</th>
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
                                            $status = $balance <= 0 ? "To'langan" : ($doc->paid_amount > 0 ? "Qisman" : "Qarzdor");
                                            $badge = $balance <= 0 ? "success" : ($doc->paid_amount > 0 ? "warning" : "danger");
                                        @endphp
                                        <tr class="document-row" 
                                            data-document-id="{{ $doc->id }}" 
                                            data-id="{{ $doc->id }}"
                                            data-document_code="{{ $doc->document_code }}"
                                            data-final_price="{{ $doc->final_price }}"
                                            data-paid_amount="{{ $doc->paid_amount }}"
                                            data-balance="{{ $balance }}"
                                        >
                                            <td>#{{ $doc->id }}</td>
                                            <td><span class="badge bg-info text-dark">{{ $doc->document_code }}</span></td>
                                            <td><strong>{{ number_format($doc->final_price, 0, ',', ' ') }} so'm</strong></td>
                                            <td><span class="text-success">{{ number_format($doc->paid_amount, 0, ',', ' ') }} so'm</span></td>
                                            <td><span class="{{ $balance > 0 ? 'balance-negative' : 'balance-positive' }}">{{ number_format($balance, 0, ',', ' ') }} so'm</span></td>
                                            <td>
                                                @if($doc->discount > 0)
                                                    <span class="badge bg-warning text-dark">{{ number_format($doc->discount, 0, ',', ' ') }} so'm</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($doc->description)
                                                    <button class="btn btn-sm btn-info description-btn" 
                                                        data-description="{{ $doc->description }}">
                                                        Ko'rish
                                                    </button>
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
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="paymentForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">To'lov qo'shish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="document_id">

                    <div class="mb-3">
                        <label class="form-label">Hujjat raqami:</label>
                        <input type="text" id="document_code" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Qoldiq summa:</label>
                        <input type="text" id="balance" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">To'lov summasi:</label>
                        <input type="number" name="amount" class="form-control" required min="1000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">To'lov turi:</label>
                        <select name="payment_type" class="form-select">
                            <option value="cash">Naqd</option>
                            <option value="card">Plastik karta</option>
                            <option value="online">Onlayn</option>
                            <option value="admin_entry">Boshqalar</option>
                        </select>
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

{{-- Description Modal --}}
<div class="modal fade" id="descriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Izoh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="descriptionContent"></div>
        </div>
    </div>
</div>

{{-- Payment History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">To'lov Tarixi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyContent">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Summa</th>
                            <th>To'lov turi</th>
                            <th>Admin</th>
                            <th>Sana</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script_include_end_body')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<script>
$(document).ready(function () {

    // SORT
    $('th.sortable').click(function () {
        var table = $(this).parents('table').eq(0);
        var tbody = table.find('tbody').eq(0);
        var rows = tbody.find('tr.document-row').toArray();
        var column = $(this).data('column');
        var asc = !$(this).hasClass('asc');

        rows.sort(function (a, b) {
            var valA = $(a).data(column);
            var valB = $(b).data(column);

            if ($.isNumeric(valA) && $.isNumeric(valB)) {
                valA = parseFloat(valA);
                valB = parseFloat(valB);
            } else {
                A = A.toString().toLowerCase();
                B = B.toString().toLowerCase();
            }

            return asc ? A > B : A < B;
        });

        tbody.append(rows);

        $('th.sortable').removeClass('asc desc');
        $(this).addClass(asc ? 'asc' : 'desc');
    });

    // PAYMENT MODAL OPEN
    $('.make-payment-btn').click(function (e) {
        e.stopPropagation();
        let id = $(this).data('document-id');
        let code = $(this).data('document-code');
        let balance = $(this).data('balance');

        $('#document_id').val(id);
        $('#document_code').val(code);
        $('#balance').val(balance + " so'm");
        $('input[name="amount"]').attr('max', balance).val('');

        $('#paymentModal').modal('show');
    });

    // DESCRIPTION MODAL
    $('.description-btn').click(function(e){
        e.stopPropagation();
        let desc = $(this).data('description');
        $('#descriptionContent').text(desc);
        $('#descriptionModal').modal('show');
    });

    // PAYMENT HISTORY MODAL
    $('.payment-history-btn').click(function(e){
        e.stopPropagation();
        let docId = $(this).data('document-id');
        $.ajax({
            url: "/admin_filial/payments/"+docId, // Controllerda route shart
            method: "GET",
            success: function(res){
                let tbody = '';
                res.forEach(function(payment, index){
                    let date = new Date(payment.created_at);

                    // Oy nomini array orqali olish
                    const monthNames = [
                        "January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"
                    ];

                    let day = date.getDate().toString().padStart(2, '0');
                    let month = monthNames[date.getMonth()];
                    let year = date.getFullYear();
                    let hours = date.getHours().toString().padStart(2,'0');
                    let minutes = date.getMinutes().toString().padStart(2,'0');

                    // Format: 25 November 2025, 14:35
                    let formatted = `${day} ${month} ${year}, ${hours}:${minutes}`;
                    tbody += `<tr>
                        <td>${index+1}</td>
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

    // PAYMENT AJAX
    $('#paymentForm').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('admin_filial.add_payment') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                document_id: $('#document_id').val(),
                amount: $('input[name="amount"]').val(),
                payment_type: $('select[name="payment_type"]').val()
            },
            success: function (res) {
                if (res.status === 'success') {
                    location.reload(); // Flash message bilan
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    alert(xhr.responseJSON.message);
                } else {
                    alert("Xatolik! Server to'lovni qabul qilmadi.");
                }
            }
        });
    });

});
</script>
@endsection
