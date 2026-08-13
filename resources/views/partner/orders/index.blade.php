@extends('template')
@section('style')
    @include('partner.partials.styles')
@endsection
@section('body')
<div class="partner-page"><div class="partner-wrap">
    <div class="partner-head"><div><h1>Partner buyurtmalari</h1><p class="muted">Faqat {{ $partner->company_name }} buyurtmalari.</p></div><div class="partner-actions"><a class="btn" href="{{ route('partner.orders.create') }}">+ Yangi buyurtma</a><a class="btn light" href="{{ route('partner.dashboard') }}">Dashboard</a></div></div>
    <div class="panel"><form method="GET" class="partner-actions" style="margin-bottom:14px"><input class="form-control" style="max-width:300px" name="q" value="{{ request('q') }}" placeholder="Kod, reference yoki mijoz"><select class="form-control" style="max-width:220px" name="status"><option value="">Barcha statuslar</option>@foreach($statuses as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select><button class="btn light" type="submit">Qidirish</button></form><div class="table-wrap"><table><thead><tr><th>Kod</th><th>Mijoz</th><th>Paket</th><th>Status</th><th>Billing</th><th>Jami</th><th></th></tr></thead><tbody>
        @forelse($orders as $order)<tr><td><a href="{{ route('partner.orders.show', $order) }}">{{ $order->order_code }}</a><br><span class="muted">{{ $order->partner_reference }}</span></td><td>{{ $order->client?->name }}<br><span class="muted">{{ $order->client?->phone_number }}</span></td><td>{{ $order->packageTemplate?->name ?: 'Oddiy order' }}</td><td><span class="badge">{{ $order->status_label }}</span></td><td><span class="badge">{{ $order->billing_status }}</span></td><td>{{ number_format($order->total_amount,0,',',' ') }} {{ $order->currency }}</td><td><a class="btn light" href="{{ route('partner.orders.show', $order) }}">Ko‘rish</a></td></tr>@empty<tr><td colspan="7" class="muted">Buyurtma topilmadi.</td></tr>@endforelse
    </tbody></table></div>{{ $orders->links() }}</div>
</div></div>
@endsection
