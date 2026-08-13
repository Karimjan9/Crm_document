<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>QR receipt {{ $order->order_code }}</title>
    <style>body{font-family:Arial,sans-serif;color:#172033;margin:0;background:#f4f7fb}.receipt{max-width:460px;margin:40px auto;background:#fff;padding:28px;border-radius:16px;text-align:center;box-shadow:0 10px 25px #00000012}.brand{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#1d4ed8;font-weight:800}.code{font-size:28px;font-weight:800;margin:18px 0 4px}.muted{color:#64748b;font-size:13px}.qr{margin:22px auto}.qr img{width:260px;max-width:100%}.row{display:flex;justify-content:space-between;border-top:1px solid #e2e8f0;padding:10px 0;text-align:left}.print{margin-top:18px;padding:10px 14px;border:0;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer}@media print{body{background:#fff}.receipt{box-shadow:none;margin:0 auto}.print{display:none}}</style>
</head>
<body>
<main class="receipt">
    <div class="brand">CRM Document · QR receipt</div>
    <div class="code">{{ $order->order_code }}</div>
    <div class="muted">Buyurtma statusini shaxsiy ma'lumotsiz ko'rish uchun QR kodni skanerlang.</div>
    <div class="qr"><img src="{{ route('orders.portal.qr', ['trackingToken' => $order->tracking_token]) }}" alt="Buyurtma tracking QR"></div>
    <div class="row"><span>Status</span><strong>{{ $order->status_label }}</strong></div>
    <div class="row"><span>Jami</span><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} {{ $order->currency }}</strong></div>
    <div class="row"><span>To'langan</span><strong>{{ number_format($order->paid_amount, 0, ',', ' ') }} {{ $order->currency }}</strong></div>
    <div class="row"><span>Qoldiq</span><strong>{{ number_format($order->balance_amount, 0, ',', ' ') }} {{ $order->currency }}</strong></div>
    <button class="print" onclick="window.print()">Print / saqlash</button>
</main>
</body>
</html>
