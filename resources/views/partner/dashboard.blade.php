@extends('template')

@section('style')
@include('partner.partials.styles')
<style>
    .partner-page{padding:26px;background:#f5f7fb;min-height:calc(100vh - 80px)}
    .partner-wrap{max-width:1180px;margin:0 auto}.partner-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:20px}
    .partner-head h1{margin:0;color:#102a56;font-size:27px}.partner-head p{margin:6px 0;color:#64748b}.partner-actions{display:flex;gap:8px;flex-wrap:wrap}
    .stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}.stat-card,.panel{background:#fff;border:1px solid #e2e8f0;border-radius:15px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
    .stat-card small,.muted{color:#64748b;font-size:12px}.stat-card strong{display:block;font-size:25px;margin-top:6px;color:#102a56}.grid{display:grid;grid-template-columns:1.4fr .9fr;gap:16px}.panel h2{margin:0 0 14px;font-size:17px;color:#102a56}.row{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #eef2f7}.row:last-child{border-bottom:0}.btn{display:inline-block;border:0;border-radius:8px;padding:9px 12px;text-decoration:none;cursor:pointer;font-weight:700;font-size:12px;background:#2563eb;color:#fff}.btn.light{background:#eef2ff;color:#1d4ed8}.btn.danger{background:#fef2f2;color:#b91c1c}.badge{display:inline-block;border-radius:999px;padding:5px 9px;background:#f1f5f9;color:#475569;font-size:11px;font-weight:700}.notice{background:#ecfdf5;color:#047857;padding:12px;border-radius:9px;margin-bottom:15px}.token{display:block;word-break:break-all;background:#0f172a;color:#dbeafe;padding:12px;border-radius:8px;font-family:monospace;font-size:12px}.form-control{width:100%;box-sizing:border-box;margin:4px 0 10px}.form-label{font-weight:700;font-size:12px;color:#475569}.two-col{display:grid;grid-template-columns:1fr 1fr;gap:10px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #eef2f7;text-align:left;font-size:13px}th{font-size:11px;color:#64748b;text-transform:uppercase}@media(max-width:900px){.stat-grid{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}}@media(max-width:520px){.stat-grid,.two-col{grid-template-columns:1fr}.partner-head{flex-direction:column}}
</style>
@endsection

@section('body')
<div class="partner-page"><div class="partner-wrap">
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    @if(session('newApiKey'))
        <div class="panel" style="margin-bottom:16px"><h2>Yangi API key — hozir nusxalang</h2><p class="muted">Bu token qayta ko‘rsatilmaydi. Uni parol kabi himoya qiling.</p><code class="token">{{ session('newApiKey') }}</code></div>
    @endif
    <div class="partner-head">
        <div><h1>{{ $partner->trackingBrandName() }}</h1><p>{{ $partner->code }} · B2B partner kabineti · chegirma {{ number_format((float)$partner->discount_percent, 2, ',', ' ') }}%</p></div>
        <div class="partner-actions"><a class="btn" href="{{ route('partner.orders.create') }}">+ Yangi buyurtma</a><a class="btn light" href="{{ route('partner.invoices.index') }}">Invoice’lar</a><a class="btn light" href="{{ route('partner.orders.index') }}">Buyurtmalar</a></div>
    </div>
    <div class="stat-grid">
        <div class="stat-card"><small>Barcha buyurtmalar</small><strong>{{ number_format($stats['orders']) }}</strong></div>
        <div class="stat-card"><small>Faol buyurtmalar</small><strong>{{ number_format($stats['active']) }}</strong></div>
        <div class="stat-card"><small>Joriy oy aylanmasi</small><strong>{{ number_format($stats['month_total'], 0, ',', ' ') }} {{ $partner->currency }}</strong></div>
        <div class="stat-card"><small>Ochiq invoice qoldig‘i</small><strong>{{ number_format($stats['outstanding'], 0, ',', ' ') }} {{ $partner->currency }}</strong></div>
    </div>
    <div class="grid">
        <div class="panel"><h2>So‘nggi buyurtmalar</h2><div class="table-wrap"><table><thead><tr><th>Kod</th><th>Mijoz</th><th>Filial</th><th>Status</th><th>Summa</th></tr></thead><tbody>
            @forelse($recentOrders as $order)<tr><td><a href="{{ route('partner.orders.show', $order) }}">{{ $order->order_code }}</a><br><span class="muted">{{ $order->partner_reference }}</span></td><td>{{ $order->client?->name }}<br><span class="muted">{{ $order->client?->phone_number }}</span></td><td>{{ $order->filial?->name }}</td><td><span class="badge">{{ $order->status_label }}</span></td><td>{{ number_format($order->total_amount,0,',',' ') }} {{ $order->currency }}</td></tr>@empty<tr><td colspan="5" class="muted">Hali buyurtmalar yo‘q.</td></tr>@endforelse
        </tbody></table></div></div>
        <div>
            <div class="panel" style="margin-bottom:16px"><h2>API key yaratish</h2><form method="POST" action="{{ route('partner.api-keys.store') }}">@csrf<label class="form-label">Key nomi</label><input class="form-control" name="name" placeholder="Production integration" required><label class="form-label">Muddati (kun)</label><input class="form-control" type="number" name="expires_in_days" min="1" max="1095" placeholder="Bo‘sh — muddatsiz"><button class="btn" type="submit">Key yaratish</button></form><hr><div class="muted">API endpoint: <code>{{ url('/api/v1/partner') }}</code></div>@foreach($partner->apiKeys as $key)<div class="row"><span>{{ $key->name }}<br><small class="muted">{{ $key->token_prefix }} · {{ $key->revoked_at ? 'bekor qilingan' : 'faol' }}</small></span>@if(!$key->revoked_at)<form method="POST" action="{{ route('partner.api-keys.revoke', $key) }}">@csrf<button class="btn danger" type="submit">Bekor qilish</button></form>@endif</div>@endforeach</div>
            <div class="panel"><h2>White-label tracking</h2><form method="POST" action="{{ route('partner.branding.update') }}">@csrf<label class="form-label">Tracking brendi</label><input class="form-control" name="brand_name" value="{{ $partner->brand_name }}" placeholder="Kompaniya nomi"><label class="form-label">Logo URL</label><input class="form-control" name="brand_logo_url" value="{{ $partner->brand_logo_url }}" placeholder="https://..."><div class="two-col"><div><label class="form-label">Asosiy rang</label><input class="form-control" type="text" name="brand_primary_color" value="{{ $partner->trackingPrimaryColor() }}"></div><div><label class="form-label">Ikkinchi rang</label><input class="form-control" type="text" name="brand_secondary_color" value="{{ $partner->trackingSecondaryColor() }}"></div></div><label class="form-label">Tracking sarlavhasi</label><input class="form-control" name="tracking_title" value="{{ $partner->tracking_title }}" placeholder="Buyurtma holati"><button class="btn" type="submit">Saqlash</button></form></div>
        </div>
    </div>
</div></div>
@endsection
