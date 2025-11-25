@extends('template')

@section('style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.table-hover tbody tr:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}
.payment-details {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
    padding: 15px;
    animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-10px);}
    to {opacity: 1; transform: translateY(0);}
}
.status-badge {
    font-size: 0.8em;
}
.balance-positive {
    color: #28a745;
    font-weight: bold;
}
.balance-negative {
    color: #dc3545;
    font-weight: bold;
}
th.sortable {
    cursor: pointer;
}
th.sortable:after {
    content: "\f0dc"; /* Font Awesome sort icon */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    margin-left: 6px;
    font-size: 0.8em;
}
th.sortable.asc:after {
    content: "\f0de";
}
th.sortable.desc:after {
    content: "\f0dd";
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
<div class="container-fluid py-4">
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                @php
                                    $balance = $doc->final_price - $doc->paid_amount;
                                    $status = $balance <= 0 ? "To'langan" : ($doc->paid_amount > 0 ? "Qisman" : "Qarzdor");
                                    $badge = $balance <= 0 ? "success" : ($doc->paid_amount > 0 ? "warning" : "danger");
                                @endphp
                                <tr class="document-row" data-document-id="{{ $doc->id }}"
                                    data-id="{{ $doc->id }}"
                                    data-document_code="{{ $doc->document_code }}"
                                    data-final_price="{{ $doc->final_price }}"
                                    data-paid_amount="{{ $doc->paid_amount }}"
                                    data-balance="{{ $balance }}">
                                    <td>#{{ $doc->id }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $doc->document_code }}</span></td>
                                    <td><strong>{{ number_format($doc->final_price,0,',',' ') }} so'm</strong></td>
                                    <td><span class="text-success">{{ number_format($doc->paid_amount,0,',',' ') }} so'm</span></td>
                                    <td><span class="{{ $balance > 0 ? 'balance-negative' : 'balance-positive' }}">{{ number_format($balance,0,',',' ') }} so'm</span></td>
                                    <td>
                                        @if($doc->discount > 0)
                                            <span class="badge bg-warning text-dark">{{ number_format($doc->discount,0,',',' ') }} so'm</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $doc->description ? \Illuminate\Support\Str::limit($doc->description,50) : '-' }}</td>
                                    <td><span class="badge bg-{{ $badge }} status-badge">{{ $status }}</span></td>
                                </tr>
                                <tr id="payment-details-{{ $doc->id }}" class="payment-details-row" style="display:none;">
                                    <td colspan="8" class="p-0">
                                        <div class="payment-details">
                                            <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>To'lovlar Tarixi - Hujjat #{{ $doc->document_code }}</h6>
                                            @if($doc->payments && $doc->payments->count() > 0)
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th>To'lov ID</th>
                                                            <th>Sanasi</th>
                                                            <th>Summasi</th>
                                                            <th>Turi</th>
                                                            <th>Izoh</th>
                                                            <th>Qo'shilgan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($doc->payments as $payment)
                                                            <tr>
                                                                <td>#{{ $payment->id }}</td>
                                                                <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y'):'N/A' }}</td>
                                                                <td class="text-success fw-bold">{{ number_format($payment->amount,0,',',' ') }} so'm</td>
                                                                <td><span class="badge bg-primary">{{ $payment->payment_type ?? 'Naqd' }}</span></td>
                                                                <td>{{ $payment->description ?? '-' }}</td>
                                                                <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="alert alert-warning mb-0">To'lovlar topilmadi</div>
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
    </div>
</div>
@endsection

@section('script')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
$(document).ready(function(){
    // Row click -> payments show/hide with animation
    $('.document-row').click(function(){
        var docId = $(this).data('document-id');
        var row = $('#payment-details-' + docId);

        // Boshqa ochiq row-larni yopish
        $('.payment-details-row').not(row).slideUp(200);

        // Joriy rowni toggle
        row.slideToggle(300);
    });

    // Simple arrow sort
    $('th.sortable').click(function(){
        var table = $(this).parents('table').eq(0);
        var tbody = table.find('tbody').eq(0);
        var rows = tbody.find('tr.document-row').toArray();
        var column = $(this).data('column');
        var asc = !$(this).hasClass('asc');

        rows.sort(function(a,b){
            var valA = $(a).data(column);
            var valB = $(b).data(column);
            if($.isNumeric(valA) && $.isNumeric(valB)){
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
});
</script>
@endsection
