@extends('template')

@section('style')
<style>
    .case-page{min-height:calc(100vh - 80px);padding:24px;background:#f5f7fb;color:#172033}
    .case-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:18px}
    .case-head h1{margin:0;color:#102a56;font-size:28px;font-weight:800}
    .case-head p{margin:6px 0 0;color:#64748b}
    .case-actions{display:flex;gap:8px;flex-wrap:wrap}
    .case-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.9fr);gap:16px}
    .panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.05);overflow:hidden;margin-bottom:16px}
    .panel-head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:15px 17px;border-bottom:1px solid #eef2f7}
    .panel-head h3{margin:0;font-size:16px;color:#102a56}
    .panel-body{padding:17px}
    .status{display:inline-flex;border-radius:999px;padding:6px 10px;font-size:11px;font-weight:800;background:#eff6ff;color:#1d4ed8}
    .timeline{display:grid;gap:12px}
    .timeline-item{display:grid;grid-template-columns:12px 1fr;gap:10px}
    .timeline-dot{width:10px;height:10px;border-radius:50%;background:#2563eb;margin-top:5px}
    .timeline-copy{border-left:1px solid #dbeafe;padding-left:12px;padding-bottom:7px}
    .timeline-copy strong{display:block;font-size:13px}
    .timeline-copy small{color:#64748b}
    .table-sm td,.table-sm th{padding:9px 8px;vertical-align:middle}
    .check-item{display:flex;align-items:flex-start;gap:10px;padding:11px 0;border-bottom:1px solid #eef2f7}
    .check-item:last-child{border-bottom:0}
    .check-item input{margin-top:4px}
    .check-item.done strong{text-decoration:line-through;color:#64748b}
    .kv{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .kv div{padding:11px;border:1px solid #eef2f7;border-radius:10px;background:#f8fafc}
    .kv span{display:block;color:#64748b;font-size:11px;text-transform:uppercase;font-weight:700}
    .kv strong{display:block;margin-top:5px;font-size:14px;overflow-wrap:anywhere}
    .qr-box{text-align:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px}
    .qr-box img{width:180px;height:180px;max-width:100%}
    .doc-card{border:1px solid #e2e8f0;border-radius:12px;padding:13px;margin-bottom:10px}
    .doc-card:last-child{margin-bottom:0}
    .doc-card-head{display:flex;justify-content:space-between;gap:10px}
    .doc-card h4{margin:0;font-size:14px}
    .doc-card p{margin:6px 0 0;color:#64748b;font-size:12px}
    .money-row{display:flex;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px dashed #e2e8f0}
    .money-row:last-child{border-bottom:0;font-weight:800;font-size:16px}
    .form-control,.form-select{border-radius:8px}
    .delivery-form{display:grid;gap:9px}
    .delivery-form .row{--bs-gutter-x:.6rem}
    .file-list{display:grid;gap:6px}
    .file-list a{font-size:12px}
    .notification-item{font-size:12px;padding:9px 0;border-bottom:1px solid #eef2f7}
    .notification-item:last-child{border-bottom:0}
    @media(max-width:950px){.case-grid{grid-template-columns:1fr}.case-head{flex-direction:column}.case-actions{width:100%}.case-actions a{flex:1;text-align:center}}
    @media(max-width:600px){.case-page{padding:15px}.kv{grid-template-columns:1fr}}
</style>
@endsection

@section('body')
@php
    $canManage = auth()->user()->can('update', $order);
    $createRoute = auth()->user()->hasRole('employee')
        ? 'employee.document.create'
        : (auth()->user()->hasRole('admin_filial') ? 'admin_filial.document.create' : (auth()->user()->hasRole('admin_manager') ? 'admin.document.create' : 'superadmin.document.create'));
@endphp
<div class="case-page">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="case-head">
        <div>
            <h1>{{ $order->order_code }}</h1>
            <p>{{ $order->client?->name }} · {{ $order->filial?->name }} · {{ optional($order->created_at)->format('d.m.Y H:i') }}</p>
        </div>
        <div class="case-actions">
            <a href="{{ route('orders.index') }}" class="btn btn-light">Orqaga</a>
            @if($canManage)
                <a href="{{ route($createRoute, ['order_id' => $order->id]) }}" class="btn btn-primary"><i class="bx bx-plus"></i> Hujjat qo‘shish</a>
            @endif
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-primary" target="_blank"><i class="bx bx-receipt"></i> Invoice</a>
            <a href="{{ route('orders.qr', $order) }}" class="btn btn-outline-dark" target="_blank"><i class="bx bx-qr"></i> QR</a>
            <a href="{{ route('orders.portal.receipt', ['trackingToken' => $order->tracking_token]) }}" class="btn btn-outline-secondary" target="_blank">QR receipt</a>
        </div>
    </div>

    @if($canManage)
        <div class="panel"><div class="panel-body"><div class="row g-3">
            <div class="col-lg-6">
                <h3 class="mb-2">Order to‘lovi</h3>
                <form method="POST" action="{{ route('orders.payments.store', $order) }}" class="delivery-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row"><div class="col-md-6"><select name="cash_session_id" class="form-select"><option value="">Cash session avtomatik</option>@foreach($cashSessions as $session)<option value="{{ $session->id }}">{{ $session->cashier?->name }} · {{ $session->session_date?->format('d.m.Y') }}</option>@endforeach</select></div><div class="col-md-6"><input name="online_transaction_id" class="form-control" placeholder="Online transaction ID"></div></div>
                    <div class="row"><div class="col-md-8"><input type="file" name="payment_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div><div class="col-md-4 small text-muted d-flex align-items-center">Proof private saqlanadi</div></div>
                    <div class="row">
                        <div class="col-md-4"><input name="amount" class="form-control" type="number" min="0.01" step="0.01" placeholder="Summa" required></div>
                        <div class="col-md-4"><select name="payment_type" class="form-select" required><option value="cash">Naqd</option><option value="card">Karta</option><option value="online">Online</option><option value="transfer">Bank transfer</option><option value="admin_entry">Admin kiritdi</option></select></div>
                        <div class="col-md-4"><select name="document_id" class="form-select"><option value="">Order umumiy to‘lovi</option>@foreach($order->documents as $document)<option value="{{ $document->id }}">{{ $document->document_code }}</option>@endforeach</select></div>
                    </div>
                    <button class="btn btn-outline-success">To‘lov qo‘shish</button>
                </form>
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="{{ route('orders.payment-link.store', $order) }}">
                        @csrf
                        <button class="btn btn-outline-dark" type="submit">Mijoz uchun payment link yaratish</button>
                    </form>
                    @php($activePaymentLink = $order->paymentLinks->firstWhere('status', 'pending'))
                    @if($activePaymentLink)
                        <div class="small text-muted mt-2">
                            Faol link:
                            <a href="{{ $activePaymentLink->url }}" target="_blank">{{ $activePaymentLink->url }}</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <h3 class="mb-2">Mijozga xabar</h3>
                <form method="POST" action="{{ route('orders.notifications.store', $order) }}" class="delivery-form">
                    @csrf
                    <div class="row"><div class="col-md-4"><select name="channel" class="form-select"><option value="sms">SMS queue</option><option value="telegram">Telegram queue</option><option value="whatsapp">WhatsApp queue</option><option value="internal">Internal</option></select></div><div class="col-md-8"><input name="recipient" class="form-control" value="{{ $order->client?->phone_number }}" placeholder="Qabul qiluvchi"></div></div>
                    <textarea name="message" class="form-control" rows="2" placeholder="Mijozga yuboriladigan xabar" required></textarea>
                    <button class="btn btn-outline-primary">Notification queue’ga qo‘shish</button>
                </form>
            </div>
        </div></div></div>
    @endif

    <div class="case-grid">
        <div>
            <div class="panel"><div class="panel-head"><h3>Order tarkibi</h3><span class="status">{{ $order->status_label }}</span></div><div class="panel-body">
                <div class="kv mb-3">
                    <div><span>Mas’ul xodim</span><strong>{{ $order->responsibleUser?->name ?: $order->createdBy?->name ?: '—' }}</strong></div>
                    <div><span>Umumiy deadline</span><strong>{{ $order->promised_at?->format('d.m.Y H:i') ?: 'Belgilanmagan' }}</strong></div>
                    <div><span>Mijoz manbasi</span><strong>{{ $order->customer_source ?: $order->source ?: '—' }}</strong></div>
                    <div><span>Yetkazib berish turi</span><strong>{{ match($order->delivery_type) { 'courier' => 'Kuryer', 'digital' => 'Digital', 'branch' => 'Boshqa filial', default => 'Filialdan olib ketish' } }}</strong></div>
                </div>
                @if($order->package_name_snapshot || $order->packageTemplate)
                    <div class="doc-card mb-3" style="background:linear-gradient(135deg,#eff6ff,#f8fafc);border-color:#bfdbfe">
                        <div class="doc-card-head"><h4>{{ $order->package_name_snapshot ?: $order->packageTemplate?->name }}</h4><span class="status">{{ $order->package_variant === 'express' ? 'Express' : 'Standard' }}</span></div>
                        <p>{{ $order->packageTemplate?->product_code ?: 'Paket snapshot' }} · {{ number_format($order->package_price, 0, ',', ' ') }} UZS · {{ $order->package_deadline_days }} kun · marja {{ number_format($order->package_margin_percent, 1) }}%</p>
                        @if($order->packageTemplate?->items?->isNotEmpty())<div class="small mt-2"><strong>Kiritilgan xizmatlar:</strong> {{ $order->packageTemplate->items->map(fn($item) => $item->service?->name ?: $item->documentType?->name)->filter()->implode(', ') }}</div>@endif
                        @if($order->packageTemplate?->packageAddons?->isNotEmpty())<div class="small mt-1"><strong>Qo‘shimcha xizmatlar:</strong> {{ $order->packageTemplate->packageAddons->map(fn($item) => $item->serviceAddon?->name)->filter()->implode(', ') }}</div>@endif
                    </div>
                @endif
                @if($order->documents->isEmpty())
                    <div class="text-muted">Bu orderda hali hujjat yo‘q.</div>
                @else
                    @foreach($order->documents as $document)
                        <div class="doc-card">
                            <div class="doc-card-head"><h4>{{ $document->document_code }} · {{ $document->service?->name ?: 'Xizmat' }}</h4><span class="status">{{ $document->status_label }}</span></div>
                            <p>{{ $document->documentType?->name ?: 'Hujjat turi ko‘rsatilmagan' }} · {{ number_format($document->final_price, 0, ',', ' ') }} UZS · to‘langan {{ number_format($document->paid_amount, 0, ',', ' ') }}</p>
                            @php($documentRequired = $document->checklists->where('is_required', true))
                            @if($documentRequired->isNotEmpty())
                                <div class="small mt-2"><strong>Checklist:</strong> {{ $documentRequired->where('is_completed', true)->count() }}/{{ $documentRequired->count() }} bajarilgan</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">@foreach($documentRequired as $item)<span class="badge {{ $item->is_completed ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->title }}</span>@endforeach</div>
                            @endif
                            @if($document->latestQaReview)<div class="small mt-2"><strong>QA:</strong> {{ $document->latestQaReview->result === 'passed' ? 'Tasdiqlandi' : ucfirst($document->latestQaReview->result) }} · {{ $document->latestQaReview->reviewer?->name ?: 'System' }}</div>@endif
                            @if($document->files?->count())
                                <div class="file-list mt-2">@foreach($document->files as $file)<a href="{{ $file->file_url }}"><i class="bx bx-file"></i> {{ $file->original_name }}</a>@endforeach</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div></div>

            <div class="panel"><div class="panel-head"><h3>Price lines</h3><span>{{ number_format($order->total_amount, 0, ',', ' ') }} UZS</span></div><div class="panel-body">
                @if($order->priceLines->isEmpty())
                    <div class="text-muted">Narx satrlari yo‘q.</div>
                @else
                    <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Nomi</th><th>Hujjat</th><th class="text-end">Summa</th></tr></thead><tbody>@foreach($order->priceLines as $line)<tr><td>{{ $line->name }}</td><td>{{ $line->document?->document_code ?: 'Order' }}</td><td class="text-end">{{ number_format($line->total_price, 0, ',', ' ') }}</td></tr>@endforeach</tbody></table></div>
                @endif
                <div class="money-row mt-3"><span>Jami</span><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} UZS</strong></div>
                <div class="money-row"><span>To‘langan / qoldiq</span><strong>{{ number_format($order->paid_amount, 0, ',', ' ') }} / {{ number_format($order->balance_amount, 0, ',', ' ') }} UZS</strong></div>
                <div class="money-row"><span>Foyda ({{ $order->profit_margin }}%)</span><strong>{{ number_format($order->profit_amount, 0, ',', ' ') }} UZS</strong></div>
            </div></div>

            <div class="panel"><div class="panel-head"><h3>To‘lovlar</h3><span>{{ number_format($order->paid_amount, 0, ',', ' ') }} UZS</span></div><div class="panel-body">
                @if($order->payments->isEmpty())
                    <div class="text-muted">To‘lov mavjud emas.</div>
                @else
                    <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Sana</th><th>Tur</th><th>Hujjat</th><th>Kim</th><th class="text-end">Summa</th></tr></thead><tbody>@foreach($order->payments as $payment)<tr><td>{{ optional($payment->created_at)->format('d.m.Y H:i') }}</td><td>{{ $payment->payment_type }}</td><td>{{ $payment->document?->document_code ?: 'Order' }}</td><td>{{ $payment->paidByAdmin?->name ?: '—' }}</td><td class="text-end">{{ number_format($payment->amount, 0, ',', ' ') }}</td></tr>@endforeach</tbody></table></div>
                @endif
            </div></div>
        </div>

        <div>
            <div class="panel"><div class="panel-head"><h3>Status boshqaruvi</h3></div><div class="panel-body">
                @if($canManage)
                    <form method="POST" action="{{ route('orders.status', $order) }}">@csrf<div class="mb-2"><select name="status" class="form-select">@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>@endforeach</select></div><textarea name="reason" class="form-control mb-2" rows="2" placeholder="Izoh / sabab"></textarea><button class="btn btn-primary w-100">Statusni saqlash</button></form>
                @else
                    <span class="status">{{ $order->status_label }}</span><p class="small text-muted mt-2 mb-0">Sizda statusni o‘zgartirish huquqi yo‘q.</p>
                @endif
            </div></div>

            <div class="panel"><div class="panel-head"><h3>Checklist</h3><span>{{ $order->checklists->where('is_completed', true)->count() }}/{{ $order->checklists->count() }}</span></div><div class="panel-body">
                @if($order->checklists->isEmpty())
                    <div class="text-muted">Checklist yo‘q.</div>
                @else
                    @foreach($order->checklists as $item)
                        @if($canManage)
                            <form method="POST" action="{{ route('orders.checklist.toggle', [$order, $item]) }}" class="check-item {{ $item->is_completed ? 'done' : '' }}">@csrf<input type="hidden" name="is_completed" value="{{ $item->is_completed ? 0 : 1 }}"><input type="checkbox" onchange="this.form.submit()" @checked($item->is_completed)><div><strong>{{ $item->title }}</strong><small class="d-block text-muted">{{ $item->is_required ? 'Majburiy' : 'Ixtiyoriy' }}</small></div></form>
                        @else
                            <div class="check-item {{ $item->is_completed ? 'done' : '' }}"><input type="checkbox" disabled @checked($item->is_completed)><div><strong>{{ $item->title }}</strong><small class="d-block text-muted">{{ $item->is_required ? 'Majburiy' : 'Ixtiyoriy' }}</small></div></div>
                        @endif
                    @endforeach
                @endif
            </div></div>

            <div class="panel"><div class="panel-head"><h3>Courier delivery</h3></div><div class="panel-body">
                @if($order->deliveries->isNotEmpty())
                    @foreach($order->deliveries as $delivery)<div class="kv mb-2"><div><span>Status</span><strong>{{ $delivery->status }}</strong></div><div><span>Tracking</span><strong>{{ $delivery->tracking_code }}</strong></div><div><span>Kuryer</span><strong>{{ $delivery->courier?->name ?: '—' }}</strong></div><div><span>Manzil</span><strong>{{ $delivery->address ?: '—' }}</strong></div></div>@endforeach
                @elseif($canManage)
                    <form method="POST" action="{{ route('orders.delivery.assign', $order) }}" class="delivery-form">@csrf<select name="courier_id" class="form-select" required><option value="">Kuryer tanlang</option>@foreach($couriers as $courier)<option value="{{ $courier->id }}">{{ $courier->name }}</option>@endforeach</select><input name="recipient_name" class="form-control" placeholder="Qabul qiluvchi" value="{{ $order->client?->name }}"><input name="recipient_phone" class="form-control" placeholder="Telefon" value="{{ $order->client?->phone_number }}"><textarea name="address" class="form-control" rows="2" placeholder="Yetkazish manzili" required></textarea><input name="fee" class="form-control" type="number" min="0" step="0.01" placeholder="Delivery narxi"><button class="btn btn-dark">Kuryerga biriktirish</button></form>
                @else
                    <div class="text-muted">Delivery hali biriktirilmagan.</div>
                @endif
            </div></div>

            <div class="panel"><div class="panel-head"><h3>Order xarajatlari</h3><span>{{ number_format($order->cost_amount, 0, ',', ' ') }} UZS</span></div><div class="panel-body">
                @if($canManage)<form method="POST" action="{{ route('orders.costs.store', $order) }}" class="delivery-form mb-3">@csrf<div class="row"><div class="col-5"><input name="category" class="form-control" placeholder="Kategoriya" required></div><div class="col-7"><input name="amount" class="form-control" type="number" min="0.01" step="0.01" placeholder="Summa" required></div></div><textarea name="description" class="form-control" rows="2" placeholder="Izoh"></textarea><input name="margin_reason" class="form-control" placeholder="Marja pastlasa izoh"><button class="btn btn-outline-danger">Xarajat qo‘shish</button></form>@endif
                @if($order->costs->isEmpty())
                    <div class="text-muted small">Hali xarajat kiritilmagan.</div>
                @else
                    @foreach($order->costs as $cost)
                        <div class="notification-item"><strong>{{ $cost->category }}</strong> · {{ number_format($cost->amount, 0, ',', ' ') }} UZS<small class="d-block text-muted">{{ $cost->description }} · {{ $cost->recordedBy?->name ?: 'System' }}</small></div>
                    @endforeach
                @endif
            </div></div>

            <div class="panel"><div class="panel-head"><h3>Tracking QR</h3></div><div class="panel-body"><div class="qr-box"><img src="{{ route('orders.qr', $order) }}" alt="Order tracking QR"><div class="small text-muted mt-2">Mijozga shu QR orqali statusni ko‘rsatish mumkin.</div><a href="{{ route('orders.track', ['trackingToken' => $order->tracking_token]) }}" target="_blank" class="small">Public tracking sahifasini ochish</a></div></div></div>

            <div class="panel"><div class="panel-head"><h3>Status tarixi</h3></div><div class="panel-body">
                @if($order->statusHistories->isEmpty())<div class="text-muted">Tarix mavjud emas.</div>@else<div class="timeline">@foreach($order->statusHistories as $history)<div class="timeline-item"><span class="timeline-dot"></span><div class="timeline-copy"><strong>{{ \App\Models\Order::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</strong><small>{{ optional($history->created_at)->format('d.m.Y H:i') }} · {{ $history->changedBy?->name ?: 'System' }}</small>@if($history->reason)<div class="small text-muted">{{ $history->reason }}</div>@endif</div></div>@endforeach</div>@endif
            </div></div>

            <div class="panel"><div class="panel-head"><h3>Mijoz notification’lari</h3></div><div class="panel-body">
                @if($order->notifications->isEmpty())
                    <div class="text-muted">Notification mavjud emas.</div>
                @else
                    @foreach($order->notifications as $notification)
                        <div class="notification-item"><strong>{{ $notification->event }}</strong> · {{ $notification->channel }} · {{ $notification->status }}<div>{{ $notification->message }}</div><small class="d-block text-muted">{{ $notification->recipient }} · {{ optional($notification->created_at)->format('d.m.Y H:i') }}</small></div>
                    @endforeach
                @endif
            </div></div>
        </div>
    </div>
</div>
@endsection
