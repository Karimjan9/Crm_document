<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($trackingBrand = $order->partner?->trackingBrandName() ?: 'Mijoz kabineti')
    @php($trackingPrimary = $order->partner?->trackingPrimaryColor() ?: '#2563eb')
    @php($trackingSecondary = $order->partner?->trackingSecondaryColor() ?: '#0f172a')
    <style>:root{--brand-primary:{{ $trackingPrimary }};--brand-secondary:{{ $trackingSecondary }}}.brand{color:var(--brand-primary)!important}.status{color:var(--brand-primary)!important}</style>
    <script>document.title = @json($order->partner?->tracking_title ?: ($order->order_code . ' — ' . $trackingBrand));</script>
    <title>{{ $order->order_code }} — Mijoz kabineti</title>
    <style>
        body{margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#172033}
        .wrap{max-width:980px;margin:32px auto;padding:0 16px}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 10px 26px rgba(15,23,42,.06);padding:20px;margin-bottom:16px}
        .head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
        .brand{color:#1d4ed8;font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:.12em}
        .head h1{margin:7px 0;font-size:28px}.muted{color:#64748b;font-size:13px}
        .status{display:inline-flex;background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:800}
        .grid{display:grid;grid-template-columns:1.35fr .8fr;gap:16px}
        .row{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #eef2f7}
        .row:last-child{border-bottom:0}.pill{display:inline-block;border-radius:999px;padding:5px 9px;background:#f1f5f9;font-size:11px;font-weight:700}
        .pill.red{background:#fef2f2;color:#b91c1c}.pill.yellow{background:#fffbeb;color:#b45309}.pill.green{background:#ecfdf5;color:#047857}
        .btn{display:inline-block;border:0;border-radius:8px;padding:10px 13px;text-decoration:none;cursor:pointer;font-weight:700;font-size:13px;background:#2563eb;color:#fff}
        .btn.light{background:#eef2ff;color:#1d4ed8}.btn.dark{background:#0f172a}.actions{display:flex;gap:8px;flex-wrap:wrap}
        .qr{text-align:center;background:#f8fafc;border-radius:12px;padding:12px}.qr img{width:180px;max-width:100%}
        textarea,input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:10px;margin:5px 0 10px}
        label{font-size:12px;font-weight:700;color:#475569}h2{font-size:17px;margin:0 0 10px}ul{padding-left:20px;margin:8px 0}
        .notice{background:#ecfdf5;color:#047857;padding:11px;border-radius:8px;margin-bottom:14px}
        @media(max-width:760px){.grid{grid-template-columns:1fr}.head{flex-direction:column}.head h1{font-size:23px}}
    </style>
</head>
<body>
<div class="wrap">
    @if(session('success'))
        <div class="notice">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="brand">Mijoz kabineti · xavfsiz tracking</div>
        @if($order->partner)
            <div style="border-left:4px solid {{ $trackingPrimary }};padding:8px 12px;margin-bottom:12px;background:#f8fafc;border-radius:6px"><strong>{{ $trackingBrand }}</strong>@if($order->partner->brand_logo_url) <img src="{{ $order->partner->brand_logo_url }}" alt="" style="height:22px;vertical-align:middle;margin-left:8px;border-radius:4px">@endif</div>
        @endif
        <div class="head">
            <div>
                <h1>{{ $order->order_code }}</h1>
                <div class="muted">
                    {{ $order->filial?->name ?: 'Filial' }} · {{ optional($order->created_at)->format('d.m.Y') }}
                </div>
            </div>
            <span class="status">{{ $order->status_label }}</span>
        </div>
        <div class="actions" style="margin-top:16px">
            <a class="btn light" href="{{ route('orders.portal.invoice', ['trackingToken' => $order->tracking_token]) }}">Invoice</a>
            <a class="btn light" href="{{ route('orders.portal.receipt', ['trackingToken' => $order->tracking_token]) }}">QR receipt</a>
            @if($paymentLink)
                <a class="btn" href="{{ $paymentLink->url }}">
                    To‘lov linki · {{ number_format($paymentLink->amount, 0, ',', ' ') }} {{ $paymentLink->currency }}
                </a>
            @endif
            <form method="POST" action="{{ route('orders.portal.repeat', ['trackingToken' => $order->tracking_token]) }}">
                @csrf
                <input type="date" name="promised_at" min="{{ today()->toDateString() }}" title="Yangi deadline" style="width:auto;display:inline-block;padding:9px;border:1px solid #cbd5e1;border-radius:8px">
                <button class="btn dark" type="submit">Takroriy buyurtma</button>
            </form>
        </div>
    </div>

    <div class="grid">
        <div>
            <div class="card">
                <h2>Buyurtma statusi</h2>
                @forelse($order->statusHistories as $history)
                    <div class="row">
                        <span>{{ \App\Models\Order::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</span>
                        <span class="muted">{{ optional($history->created_at)->format('d.m.Y H:i') }}</span>
                    </div>
                @empty
                    <div class="muted">Status tarixi tayyorlanmoqda.</div>
                @endforelse
            </div>

            <div class="card">
                <h2>Buyurtma hujjatlari</h2>
                @forelse($order->documents as $document)
                    <div class="row">
                        <span>
                            {{ $document->document_code ?: 'Hujjat' }}
                            <small class="muted">{{ $document->service?->name }}</small>
                        </span>
                        <span class="pill">{{ $document->status_label }}</span>
                    </div>
                @empty
                    <div class="muted">Hujjatlar hali biriktirilmagan.</div>
                @endforelse
            </div>

            <div class="card">
                <h2>Yetishmayotgan fayllar</h2>
                @forelse($missingFiles as $missing)
                    <div class="row">
                        <span>
                            {{ $missing['title'] }}
                            @if($missing['document_code'])
                                <small class="muted">({{ $missing['document_code'] }})</small>
                            @endif
                        </span>
                        <span class="pill red">{{ $missing['reason'] }}</span>
                    </div>
                @empty
                    <div class="pill green">Majburiy fayllar bo‘yicha kamchilik yo‘q</div>
                @endforelse
            </div>

            <div class="card">
                <h2>Tayyor fayllar</h2>
                @forelse($readyFiles as $file)
                    <div class="row">
                        <span>{{ $file['name'] }} <small class="muted">{{ $file['document_code'] }}</small></span>
                        <a class="btn light" href="{{ $file['url'] }}">Yuklab olish</a>
                    </div>
                @empty
                    <div class="muted">Tayyor fayllar hali mavjud emas.</div>
                @endforelse
            </div>

            <div class="card">
                <h2>Kuryer holati</h2>
                @forelse($order->deliveries as $delivery)
                    <div class="row">
                        <span>{{ $delivery->tracking_code ?: 'Yetkazib berish' }}</span>
                        <span class="pill">{{ $delivery->status }}</span>
                    </div>
                @empty
                    <div class="muted">Kuryer hali biriktirilmagan.</div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="card">
                <h2>Deadline Radar</h2>
                <div class="pill {{ $radar['level'] === 'red' ? 'red' : ($radar['level'] === 'yellow' ? 'yellow' : 'green') }}">
                    {{ strtoupper($radar['level']) }} · {{ $radar['score'] }}/100
                </div>
                <p class="muted">{{ $radar['summary'] }}</p>
                @if($radar['reasons'])
                    <ul class="muted">
                        @foreach($radar['reasons'] as $reason)
                            <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="qr">
                    <img src="{{ route('orders.portal.qr', ['trackingToken' => $order->tracking_token]) }}" alt="Tracking QR">
                    <div class="muted">QR kodni saqlab qo‘ying.</div>
                </div>
            </div>

            <div class="card">
                <h2>Support / murojaat</h2>
                <form method="POST" action="{{ route('orders.portal.support', ['trackingToken' => $order->tracking_token]) }}">
                    @csrf
                    <label for="subject">Mavzu</label>
                    <input id="subject" name="subject" maxlength="180" placeholder="Masalan: fayl bo‘yicha savol">
                    <label for="message">Xabar</label>
                    <textarea id="message" name="message" rows="4" maxlength="4000" required></textarea>
                    <label for="contact">Aloqa (ixtiyoriy)</label>
                    <input id="contact" name="contact" maxlength="180" placeholder="Telefon yoki email">
                    <button class="btn" type="submit">Murojaat yuborish</button>
                </form>
            </div>
        </div>
    </div>

    <div class="muted" style="text-align:center;margin:16px">Bu sahifada faqat ushbu buyurtmaga tegishli ma’lumotlar ko‘rsatiladi.</div>
    @if($order->packageTemplate || $order->package_name_snapshot)
        <div class="card">
            <h2>Tanlangan paket</h2>
            <div class="row"><span>{{ $order->package_name_snapshot ?: $order->packageTemplate?->name }}</span><span class="pill">{{ $order->package_variant === 'express' ? 'Express' : 'Standard' }}</span></div>
            <div class="row"><span class="muted">Paket narxi / muddat</span><strong>{{ number_format($order->package_price, 0, ',', ' ') }} {{ $order->currency }} · {{ $order->package_deadline_days }} kun</strong></div>
        </div>
    @endif
</div>

</body>
</html>
