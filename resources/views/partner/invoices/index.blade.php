@extends('template')
@section('style')
    @include('partner.partials.styles')
@endsection
@section('body')
<div class="partner-page"><div class="partner-wrap"><div class="partner-head"><div><h1>Oylik invoice’lar</h1><p class="muted">{{ $partner->company_name }}</p></div><a class="btn light" href="{{ route('partner.dashboard') }}">Dashboard</a></div><div class="panel"><table><thead><tr><th>Invoice</th><th>Davr</th><th>Status</th><th>Jami</th><th>Qoldiq</th><th></th></tr></thead><tbody>@forelse($invoices as $invoice)<tr><td><a href="{{ route('partner.invoices.show',$invoice) }}">{{ $invoice->invoice_number }}</a></td><td>{{ optional($invoice->period_start)->format('d.m.Y') }} — {{ optional($invoice->period_end)->format('d.m.Y') }}</td><td><span class="badge">{{ $invoice->status }}</span></td><td>{{ number_format($invoice->total_amount,0,',',' ') }} {{ $invoice->currency }}</td><td>{{ number_format($invoice->balance_amount,0,',',' ') }} {{ $invoice->currency }}</td><td><a class="btn light" href="{{ route('partner.invoices.show',$invoice) }}">Ko‘rish</a></td></tr>@empty<tr><td colspan="6" class="muted">Invoice hali yaratilmagan.</td></tr>@endforelse</tbody></table>{{ $invoices->links() }}</div></div></div>
@endsection
