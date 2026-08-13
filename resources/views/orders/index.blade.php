@extends('template')

@section('style')
<style>
    .orders-page { min-height: calc(100vh - 80px); padding: 28px; background: #f5f7fb; }
    .orders-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:22px; }
    .orders-head h1 { margin:0; color:#102a56; font-size:28px; font-weight:800; }
    .orders-head p { margin:7px 0 0; color:#64748b; }
    .metric-grid { display:grid; grid-template-columns:repeat(5,minmax(150px,1fr)); gap:14px; margin-bottom:18px; }
    .metric, .panel { background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,.05); }
    .metric { padding:17px; }
    .metric span { display:block; color:#64748b; font-size:12px; text-transform:uppercase; font-weight:700; }
    .metric strong { display:block; margin-top:8px; color:#0f172a; font-size:24px; }
    .toolbar { display:flex; gap:10px; align-items:end; flex-wrap:wrap; padding:16px; margin-bottom:16px; }
    .toolbar label { display:block; color:#64748b; font-size:12px; font-weight:700; margin-bottom:5px; }
    .toolbar input, .toolbar select { height:40px; min-width:180px; border:1px solid #cbd5e1; border-radius:8px; padding:0 10px; }
    .toolbar button { height:40px; }
    .table-panel { overflow:hidden; }
    .table-panel table { margin:0; }
    .table-panel th { background:#f8fafc; color:#475569; font-size:11px; text-transform:uppercase; white-space:nowrap; }
    .table-panel td, .table-panel th { padding:13px 12px; vertical-align:middle; }
    .table-panel td { border-color:#eef2f7; }
    .order-code { color:#1d4ed8; font-weight:800; text-decoration:none; }
    .order-code:hover { text-decoration:underline; }
    .status { display:inline-flex; border-radius:999px; padding:5px 9px; font-size:11px; font-weight:800; background:#eff6ff; color:#1d4ed8; white-space:nowrap; }
    .status-danger { background:#fef2f2; color:#b91c1c; }
    .status-success { background:#ecfdf5; color:#047857; }
    .status-warning { background:#fffbeb; color:#b45309; }
    .money { white-space:nowrap; font-weight:700; color:#0f172a; }
    .progress { height:7px; min-width:90px; background:#e2e8f0; }
    .progress-bar { background:linear-gradient(90deg,#2563eb,#06b6d4); }
    @media(max-width:1100px){ .metric-grid{grid-template-columns:repeat(3,1fr);} }
    @media(max-width:700px){ .orders-page{padding:16px;} .orders-head{flex-direction:column;} .metric-grid{grid-template-columns:repeat(2,1fr);} .toolbar>*{width:100%;} .toolbar input,.toolbar select{width:100%;} }
</style>
@endsection

@section('body')
<div class="orders-page">
    <div class="orders-head">
        <div>
            <h1>Buyurtmalar / Case</h1>
            <p>Mijoz buyurtmasi, hujjatlar, to‘lov va yetkazib berishni bir joydan boshqaring.</p>
        </div>
        <div class="d-flex gap-2"><a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Yangi Order</a><a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Orqaga</a></div>
    </div>

    <div class="metric-grid">
        <div class="metric"><span>Jami order</span><strong>{{ number_format($summary['count']) }}</strong></div>
        <div class="metric"><span>Faol order</span><strong>{{ number_format($summary['active']) }}</strong></div>
        <div class="metric"><span>Umumiy tushum</span><strong>{{ number_format($summary['revenue'], 0, ',', ' ') }}</strong></div>
        <div class="metric"><span>To‘langan</span><strong>{{ number_format($summary['paid'], 0, ',', ' ') }}</strong></div>
        <div class="metric"><span>Taxminiy foyda</span><strong>{{ number_format($summary['profit'], 0, ',', ' ') }}</strong></div>
    </div>

    <form method="GET" class="panel toolbar">
        <div><label>Qidirish</label><input name="q" value="{{ request('q') }}" placeholder="Order kodi yoki mijoz"></div>
        <div><label>Status</label><select name="status"><option value="">Barchasi</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Filtrlash</button>
        <a class="btn btn-light" href="{{ route('orders.index') }}">Tozalash</a>
    </form>

    <div class="panel table-panel">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Order</th><th>Mijoz</th><th>Filial</th><th>Hujjatlar</th><th>Status</th><th>To‘lov</th><th>Foyda</th><th></th></tr></thead>
                <tbody>
                @forelse($orders as $order)
                    @php
                        $paidPercent = (float)$order->total_amount > 0 ? min(100, ((float)$order->paid_amount / (float)$order->total_amount) * 100) : 0;
                        $statusClass = in_array($order->status, ['completed','delivered'], true) ? 'status-success' : (in_array($order->status, ['cancelled'], true) ? 'status-danger' : (in_array($order->status, ['awaiting_payment','partially_paid'], true) ? 'status-warning' : ''));
                    @endphp
                    <tr>
                        <td><a class="order-code" href="{{ route('orders.show', $order) }}">{{ $order->order_code }}</a><small class="d-block text-muted">{{ optional($order->created_at)->format('d.m.Y H:i') }}</small></td>
                        <td><strong>{{ $order->client?->name ?: '—' }}</strong><small class="d-block text-muted">{{ $order->client?->phone_number }}</small></td>
                        <td>{{ $order->filial?->name ?: '—' }}</td>
                        <td><strong>{{ $order->documents_count }}</strong> ta</td>
                        <td><span class="status {{ $statusClass }}">{{ $order->status_label }}</span></td>
                        <td><div class="money">{{ number_format($order->paid_amount, 0, ',', ' ') }} / {{ number_format($order->total_amount, 0, ',', ' ') }}</div><div class="progress mt-1"><div class="progress-bar" style="width:{{ $paidPercent }}%"></div></div></td>
                        <td class="money">{{ number_format($order->profit_amount, 0, ',', ' ') }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('orders.show', $order) }}">Ochish</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">Buyurtmalar topilmadi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
