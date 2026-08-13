@extends('template')

@section('style')
<style>
.pricing-page{padding:28px;background:#f5f7fb;min-height:calc(100vh - 80px)}.pricing-wrap{max-width:1250px;margin:0 auto}.head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:18px}.head h1{margin:0;color:#102a56}.head p{margin:6px 0;color:#64748b}.panel{background:#fff;border:1px solid #e2e8f0;border-radius:15px;padding:20px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.btn{display:inline-block;border:0;border-radius:8px;padding:8px 11px;text-decoration:none;cursor:pointer;font-weight:700;font-size:11px;background:#2563eb;color:#fff}.btn.green{background:#16a34a}.btn.red{background:#dc2626}.btn.light{background:#eef2ff;color:#1d4ed8}table{width:100%;border-collapse:collapse}th,td{padding:10px 8px;text-align:left;border-bottom:1px solid #eef2f7;font-size:12px;vertical-align:top}th{font-size:10px;color:#64748b;text-transform:uppercase}.muted{color:#64748b;font-size:11px}.badge{display:inline-block;background:#f1f5f9;border-radius:999px;padding:4px 8px;font-size:10px}.pending{background:#fef3c7;color:#92400e}.approved{background:#dcfce7;color:#166534}.rejected{background:#fee2e2;color:#991b1b}.notice{padding:12px;background:#ecfdf5;color:#047857;border-radius:8px;margin-bottom:14px}.actions{display:flex;gap:5px;flex-wrap:wrap}@media(max-width:800px){.panel{overflow:auto}.head{flex-direction:column}}
</style>
@endsection

@section('body')
<div class="pricing-page"><div class="pricing-wrap">
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    <div class="head"><div><h1>Chegirma approval’lari</h1><p>Katta chegirmalar tasdiqlanmaguncha hujjat hisobiga qo‘llanmaydi.</p></div><a class="btn light" href="{{ route('admin.pricing.index') }}">Tariflarga qaytish</a></div>
    <div class="panel"><table><thead><tr><th>ID / target</th><th>Chegirma</th><th>Sabab</th><th>So‘ragan</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($approvals as $approval)<tr><td>#{{ $approval->id }}<br><span class="muted">{{ $approval->document?->document_code ?: $approval->order?->order_code ?: 'Yangi hujjat' }}</span></td><td>{{ number_format((float)$approval->discount_percent,2,',',' ') }}%<br>{{ number_format((float)$approval->discount_amount,2,',',' ') }}</td><td>{{ $approval->reason }}</td><td>{{ $approval->requestedBy?->name ?: $approval->requestedBy?->login ?: '—' }}<br><span class="muted">{{ optional($approval->created_at)->format('d.m.Y H:i') }}</span></td><td><span class="badge {{ $approval->status }}">{{ $approval->status }}</span></td><td>@if($approval->status==='pending')<div class="actions"><form method="POST" action="{{ route('pricing.approvals.approve',$approval) }}">@csrf<button class="btn green">Tasdiqlash</button></form><form method="POST" action="{{ route('pricing.approvals.reject',$approval) }}">@csrf<button class="btn red">Rad etish</button></form></div>@endif</td></tr>@empty<tr><td colspan="6" class="muted">Approval so‘rovlari yo‘q.</td></tr>@endforelse
    </tbody></table>{{ $approvals->links() }}</div>
</div></div>
@endsection
