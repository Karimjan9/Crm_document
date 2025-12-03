@extends('template')

@section('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body { font-family: 'Inter', sans-serif; background: #eef1f4; }
.page-wrapper { padding: 28px; }

/* Glass Card */
.card {
    border: none; border-radius: 18px;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}
.card-header {
    background: linear-gradient(135deg, #3b82f6, #1e3a8a);
    padding: 20px 28px; border-bottom: none;
}
.card-header h4 { font-weight: 600; letter-spacing: -0.5px; }

/* Table Modern */
.table { font-size: 15px; }
.table thead { background: #f8fafc; }
.table-hover tbody tr { transition: 0.15s ease; }
.table-hover tbody tr:hover { background: #eff6ff; transform: scale(1.002); cursor: pointer; }
.table td, .table th { padding: 16px 20px !important; vertical-align: middle; }

/* Sort Icons */
th.sortable { cursor: pointer; position: relative; font-weight: 600; color: #334155; }
th.sortable:after { content: "\f0dc"; font-family: "Font Awesome 6 Free"; font-weight: 900; margin-left: 8px; font-size: 0.75em; opacity: 0.5; }
th.sortable.asc:after { content: "\f0de"; opacity: 1; color: #2563eb; }
th.sortable.desc:after { content: "\f0dd"; opacity: 1; color: #2563eb; }

/* Badges */
.badge { border-radius: 10px; padding: 6px 10px; }
.balance-positive { color: #10b981; font-weight: 600; }
.balance-negative { color: #ef4444; font-weight: 600; }

/* Payment Details Box */
.payment-details {
    background: rgba(248,250,252,0.85);
    border-left: 4px solid #2563eb;
    padding: 22px 26px;
    border-radius: 0 18px 18px 0;
    backdrop-filter: blur(10px);
    animation: fadeIn 0.35s ease;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.payment-details table { border-radius: 12px; overflow: hidden; }
.payment-details-row td { padding: 0 !important; }

.status-badge { font-size: 0.8em; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Documents Table --}}
            <div class="card mb-4">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-layer-group me-2"></i>Hujjatlar Ro'yxati</h4>
                    <span class="badge bg-light text-dark px-3 py-2 shadow-sm">
                        Jami: {{ $documents->count() }} ta hujjat
                    </span>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0" id="documents-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="id">ID</th>
                                <th class="sortable" data-column="document_code">Hujjat Raqami</th>
                                <th class="sortable" data-column="final_price">Jami</th>
                                <th class="sortable" data-column="paid_amount">To‘langan</th>
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
                                    $status = $balance <= 0 ? "To‘langan" : ($doc->paid_amount > 0 ? "Qisman" : "Qarzdor");
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
                                    <td><b>{{ number_format($doc->final_price) }} so‘m</b></td>
                                    <td class="text-success fw-bold">{{ number_format($doc->paid_amount) }} so‘m</td>
                                    <td>
                                        <span class="{{ $balance > 0 ? 'balance-negative' : 'balance-positive' }}">
                                            {{ number_format($balance) }} so‘m
                                        </span>
                                    </td>
                                    <td>
                                        @if($doc->discount > 0)
                                            <span class="badge bg-warning text-dark">{{ number_format($doc->discount) }} so‘m</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        @if($doc->description)
                                            <button class="btn btn-sm btn-info description-btn" data-description="{{ $doc->description }}">Ko'rish</button>
                                        @else - @endif
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

                            
                                <tr class="payment-details-row" id="payment-details-{{ $doc->id }}" style="display:none;">
                                    <td colspan="10">
                                        <div class="payment-details">
                                            <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>To‘lovlar Tarixi</h6>
                                            @if($doc->payments->count() > 0)
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Sana</th>
                                                            <th>Summasi</th>
                                                            <th>Turi</th>
                                                            <th>Izoh</th>
                                                            <th>Qo‘shilgan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($doc->payments as $payment)
                                                            <tr>
                                                                <td>#{{ $payment->id }}</td>
                                                                <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format("d.m.Y") : 'N/A' }}</td>
                                                                <td class="text-success fw-bold">{{ number_format($payment->amount) }} so‘m</td>
                                                                <td><span class="badge bg-primary">{{ $payment->payment_type ?? 'Naqd' }}</span></td>
                                                                <td>{{ $payment->description ?? '—' }}</td>
                                                                <td>{{ $payment->created_at->format("d.m.Y H:i") }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="alert alert-warning mb-0">Hech qanday to‘lov topilmadi.</div>
                                            @endif
                                        </div>
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

{{-- Modallar (payment, description, history) --}}
@include('partials.payment_modal')
@include('partials.description_modal')
@include('partials.history_modal')

@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<script>
$(document).ready(function(){

    // Toggle payment details
    $('.document-row').click(function(){
        const id = $(this).data('document-id');
        const openRow = $('#payment-details-' + id);
        $('.payment-details-row').not(openRow).slideUp(200);
        openRow.slideToggle(250);
    });

    // Sorting
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
                valA = valA.toString().toLowerCase();
                valB = valB.toString().toLowerCase();
            }

            return asc ? (valA > valB ? 1 : -1) : (valA < valB ? 1 : -1);
        });

        tbody.append(rows);
        $('th.sortable').removeClass('asc desc');
        $(this).addClass(asc ? 'asc' : 'desc');
    });

    // Payment modal open
    $('.make-payment-btn').click(function(e){
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

    // Description modal
    $('.description-btn').click(function(e){
        e.stopPropagation();
        let desc = $(this).data('description');
        $('#descriptionContent').text(desc);
        $('#descriptionModal').modal('show');
    });

    // Payment history modal
    $('.payment-history-btn').click(function(e){
        e.stopPropagation();
        let docId = $(this).data('document-id');
        $.ajax({
            url: "/admin_filial/payments/"+docId,
            method: "GET",
            success: function(res){
                let tbody = '';
                res.forEach(function(payment, index){
                    let date = new Date(payment.created_at);
                    const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
                    let day = date.getDate().toString().padStart(2,'0');
                    let month = monthNames[date.getMonth()];
                    let year = date.getFullYear();
                    let hours = date.getHours().toString().padStart(2,'0');
                    let minutes = date.getMinutes().toString().padStart(2,'0');
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

    // Payment AJAX
    $('#paymentForm').submit(function(e){
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
            success: function(res){
                if(res.status==='success') location.reload();
            },
            error: function(xhr){
                alert(xhr.status===422 ? xhr.responseJSON.message : "Xatolik! Server to'lovni qabul qilmadi.");
            }
        });
    });

});
</script>
@endsection
