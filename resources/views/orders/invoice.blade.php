<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_code }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#172033;margin:32px}.head{display:flex;justify-content:space-between;border-bottom:2px solid #172033;padding-bottom:18px}.head h1{margin:0}.meta{color:#64748b;font-size:13px;line-height:1.8}table{width:100%;border-collapse:collapse;margin-top:25px}th,td{border-bottom:1px solid #e2e8f0;padding:10px;text-align:left}th{background:#f8fafc}.right{text-align:right}.summary{margin:22px 0 0 auto;width:320px}.summary div{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #e2e8f0}.qr{margin-top:25px;text-align:right}.qr img{width:150px;height:150px}@media print{.print-hide{display:none}}
    </style>
</head>
<body>
<div class="head">
    <div><h1>INVOICE</h1><div class="meta">Invoice: <strong>{{ $order->invoice?->invoice_number ?: 'INV-' . $order->order_code }}</strong><br>Order: <strong>{{ $order->order_code }}</strong><br>Yaratilgan: {{ optional($order->created_at)->format('d.m.Y H:i') }}<br>Filial: {{ $order->filial?->name }}</div></div>
    <div class="meta">
        @if($publicPortal ?? false)
            <strong>{{ $order->partner?->trackingBrandName() ?: 'Customer invoice' }}</strong><br>{{ $order->invoice?->invoice_number ?: 'INV-' . $order->order_code }}
        @else
            <strong>{{ $order->client?->name }}</strong><br>{{ $order->client?->phone_number }}
        @endif
    </div>
</div>
@if($order->package_name_snapshot)
    <div class="meta" style="margin-top:18px;padding:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px">
        <strong>Paket:</strong> {{ $order->package_name_snapshot }} ({{ $order->package_variant === 'express' ? 'Express' : 'Standard' }}) ·
        {{ number_format($order->package_price, 0, ',', ' ') }} {{ $order->currency }} · {{ $order->package_deadline_days }} kun
    </div>
@endif
<table><thead><tr><th>#</th><th>Xizmat</th><th>Hujjat</th><th class="right">Summa</th></tr></thead><tbody>
@foreach($order->priceLines as $i=>$line)<tr><td>{{ $i+1 }}</td><td>{{ $line->name }}</td><td>{{ $line->document?->document_code ?: 'Order' }}</td><td class="right">{{ number_format($line->total_price,0,',',' ') }} {{ $order->currency }}</td></tr>@endforeach
</tbody></table>
<div class="summary"><div><span>Jami</span><strong>{{ number_format($order->total_amount,0,',',' ') }} {{ $order->currency }}</strong></div><div><span>To‘langan</span><strong>{{ number_format($order->paid_amount,0,',',' ') }}</strong></div><div><span>Qoldiq</span><strong>{{ number_format($order->balance_amount,0,',',' ') }}</strong></div></div>
<div class="qr"><img src="{{ request()->routeIs('orders.portal.invoice') ? route('orders.portal.qr', ['trackingToken' => $order->tracking_token]) : route('orders.qr',$order) }}" alt="QR"><div class="meta">Buyurtma holatini tekshirish uchun skanerlang.</div></div>
<button class="print-hide" onclick="window.print()">Print</button>
</body>
</html>
