@extends('template')

@section('style')
<style>
    .partner-admin-page{padding:28px;background:#f5f7fb;min-height:calc(100vh - 80px)}
    .partner-admin-wrap{max-width:1200px;margin:0 auto}.partner-admin-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px}.partner-admin-head h1{margin:0;color:#102a56}.partner-admin-head p{margin:6px 0;color:#64748b}.panel{background:#fff;border:1px solid #e2e8f0;border-radius:15px;padding:20px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.notice{padding:12px;background:#ecfdf5;color:#047857;border-radius:8px;margin-bottom:14px}.btn{display:inline-block;border:0;border-radius:8px;padding:10px 13px;text-decoration:none;cursor:pointer;font-weight:700;font-size:12px;background:#2563eb;color:#fff}.btn.light{background:#eef2ff;color:#1d4ed8}.badge{display:inline-block;background:#f1f5f9;border-radius:999px;padding:5px 9px;font-size:11px;color:#475569}table{width:100%;border-collapse:collapse}th,td{padding:11px 9px;text-align:left;border-bottom:1px solid #eef2f7;font-size:13px}th{font-size:11px;color:#64748b;text-transform:uppercase}.muted{color:#64748b;font-size:12px}.search{display:flex;gap:8px;margin-bottom:14px}.search input{max-width:320px}@media(max-width:700px){.partner-admin-head{flex-direction:column}.panel{overflow:auto}}
</style>
@endsection

@section('body')
<div class="partner-admin-page"><div class="partner-admin-wrap">
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    <div class="partner-admin-head"><div><h1>B2B Partners</h1><p>Partner kompaniyalar, chegirmalar, filiallar va white-label tracking.</p></div><a class="btn" href="{{ route('admin.partners.create') }}">+ Partner qo‘shish</a></div>
    <div class="panel"><form method="GET" class="search"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Kompaniya yoki code"><button class="btn light" type="submit">Qidirish</button></form><table><thead><tr><th>Kompaniya</th><th>Code / turi</th><th>Filiallar</th><th>Chegirma</th><th>Buyurtmalar</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($partners as $partner)<tr><td><strong>{{ $partner->company_name }}</strong><br><span class="muted">{{ $partner->contact_name ?: $partner->billing_email }}</span></td><td>{{ $partner->code }}<br><span class="muted">{{ $partner->type }}</span></td><td>{{ $partner->filials->pluck('name')->join(', ') ?: '—' }}</td><td>{{ number_format((float)$partner->discount_percent,2,',',' ') }}%</td><td>{{ $partner->orders_count }}</td><td><span class="badge">{{ $partner->status }}</span></td><td><a class="btn light" href="{{ route('admin.partners.edit',$partner) }}">Tahrirlash</a></td></tr>@empty<tr><td colspan="7" class="muted">Partnerlar hali yo‘q.</td></tr>@endforelse
    </tbody></table>{{ $partners->links() }}</div>
</div></div>
@endsection
