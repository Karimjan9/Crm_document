@extends('template')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/bot-requests.css') }}">
@endsection

@section('body')
@php
    $payload = $lead->botIntakeRequest->payload ?? [];
    $username = data_get($payload, 'customer.telegram_username');
    $phone = data_get($payload, 'customer.phone') ?: $lead->phone;
    $text = data_get($payload, 'request.notes') ?: data_get($payload, 'request.purpose') ?: 'Hujjat bo‘yicha murojaat';
    $expectedFiles = count($payload['attachments'] ?? []);
@endphp
<div class="page-wrapper bot-requests-shell">
    <div class="page-content">
        <a class="br-back" href="{{ route('bot-requests.index') }}"><i class="bx bx-arrow-back" aria-hidden="true"></i> Botdan so‘rovlar</a>
        <div class="br-heading">
            <div><h1>So‘rov #{{ $lead->id }}</h1><p class="br-muted mb-0">{{ $lead->botIntakeRequest->created_at->timezone('Asia/Tashkent')->format('d.m.Y · H:i') }} · Telegram orqali yuborilgan</p></div>
            <span class="br-badge {{ $lead->status }}">{{ $statuses[$lead->status] ?? $lead->status }}</span>
        </div>
        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="br-detail-grid">
            <div class="br-stack">
                <section class="br-panel">
                    <div class="br-card-head"><h2><i class="bx bx-message-square-detail me-2" aria-hidden="true"></i>Murojaat matni</h2></div>
                    <div class="br-card-body"><div class="br-full-text">{{ $text }}</div></div>
                </section>
                <section class="br-panel">
                    <div class="br-card-head"><h2><i class="bx bx-paperclip me-2" aria-hidden="true"></i>Hujjatlar</h2><span class="br-muted small">{{ $attachments->count() }} ta fayl</span></div>
                    <div class="br-card-body">
                        @if($expectedFiles > $attachments->count())
                            <p class="br-muted small">Qabul qilingan fayllar: {{ $attachments->count() }} / {{ $expectedFiles }}. Qolgan fayllar yuklanmoqda.</p>
                        @endif
                        <div class="br-files">
                            @forelse($attachments as $file)
                                <div class="br-file">
                                    <span class="br-file-icon"><i class="bx bx-file" aria-hidden="true"></i></span>
                                    <div class="br-file-info">
                                        <a href="{{ route('telegram-messages.file', $file) }}" target="_blank" rel="noopener noreferrer">{{ data_get($file->attachment_meta, 'file_name') ?: 'Hujjat #'.$file->id }}</a>
                                        <small>{{ $file->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') }} @if(data_get($file->attachment_meta, 'size')) · {{ number_format(data_get($file->attachment_meta, 'size') / 1024, 0) }} KB @endif</small>
                                        @if($file->body)<div class="br-file-caption">{{ $file->body }}</div>@endif
                                    </div>
                                    <a href="{{ route('telegram-messages.file', $file) }}" class="br-open" aria-label="Hujjatni ochish"><i class="bx bx-download" aria-hidden="true"></i></a>
                                </div>
                            @empty
                                <p class="br-muted mb-0 small">{{ $expectedFiles > 0 ? 'Hujjatlar kelishi kutilmoqda.' : 'Mijoz fayl biriktirmagan.' }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>
                @if($messages->isNotEmpty())
                    <section class="br-panel">
                        <div class="br-card-head"><h2><i class="bx bx-conversation me-2" aria-hidden="true"></i>Telegram yozishmalari</h2><span class="br-muted small">Oxirgi 20 ta xabar</span></div>
                        <div class="br-card-body br-chat">
                            @foreach($messages as $message)
                                <div class="br-chat-message {{ $message->direction }}">
                                    <small>{{ $message->direction === 'outgoing' ? 'CRM javobi' : 'Mijoz' }} · {{ $message->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</small>
                                    <p>{{ $message->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <div class="br-stack">
                <section class="br-panel">
                    <div class="br-card-head"><h2>Mijoz ma’lumotlari</h2></div>
                    <div class="br-card-body">
                        <div class="br-person mb-3"><span class="br-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lead->name, 0, 1)) }}</span><div><strong>{{ $lead->name }}</strong>
                            @if($username && preg_match('/^[A-Za-z0-9_]{5,32}$/', $username))
                                <a href="https://t.me/{{ $username }}" target="_blank" rel="noopener noreferrer" class="small">{{ '@'.$username }}</a>
                            @elseif($username)<small>{{ '@'.$username }}</small>@endif
                        </div></div>
                        @if($phone)<a class="br-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"><i class="bx bx-phone me-1" aria-hidden="true"></i>{{ $phone }}</a>@endif
                        @if(data_get($payload, 'customer.phone_verified'))<span class="br-verified"><i class="bx bx-check-shield" aria-hidden="true"></i> Mijoz o‘z kontaktini yuborgan</span>@endif
                        <hr>
                        <dl class="br-info">
                            <div><dt>Mas’ul xodim</dt><dd>{{ $lead->assignedTo?->name ?: 'Biriktirilmagan' }}</dd></div>
                            <div><dt>Filial</dt><dd>{{ $lead->filial?->name ?: 'Belgilanmagan' }}</dd></div>
                            <div><dt>Xizmat</dt><dd>{{ $lead->interested_service ?: 'Aniqlashtiriladi' }}</dd></div>
                            <div><dt>Reklama manbasi</dt><dd>{{ $lead->campaign ?: 'Telegram bot' }}</dd></div>
                        </dl>
                    </div>
                </section>

                @if($readOnly)
                    <div class="br-panel br-card-body"><span class="br-muted small"><i class="bx bx-lock-alt me-1" aria-hidden="true"></i>Kuzatuv rejimi · so‘rov bilan mas’ul xodim ishlaydi.</span></div>
                @else
                    <section class="br-panel">
                        <div class="br-card-head"><h2>So‘rov bilan ishlash</h2></div>
                        <form method="POST" action="{{ route('leads.update', $lead) }}" class="br-card-body">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $lead->name }}">
                            <input type="hidden" name="phone" value="{{ $lead->phone }}">
                            <input type="hidden" name="filial_id" value="{{ $lead->filial_id }}">
                            <input type="hidden" name="assigned_to_id" value="{{ $lead->assigned_to_id }}">
                            <label for="request-status">Holat</label>
                            <select id="request-status" name="status" class="form-select mb-3">
                                @foreach($statuses as $status => $label)<option value="{{ $status }}" @selected(old('status', $lead->status) === $status)>{{ $label }}</option>@endforeach
                            </select>
                            <label for="request-service">Xizmat / hujjat turi</label>
                            <input id="request-service" name="interested_service" maxlength="180" class="form-control mb-3" value="{{ old('interested_service', $lead->interested_service) }}" placeholder="Masalan, diplom tarjimasi">
                            <button class="btn btn-primary w-100">Saqlash</button>
                        </form>
                    </section>
                    <section class="br-panel">
                        <div class="br-card-head"><h2>Telegram orqali javob</h2><i class="bx bxl-telegram br-muted" aria-hidden="true"></i></div>
                        <form method="POST" action="{{ route('leads.telegram.reply', $lead) }}" class="br-card-body">
                            @csrf
                            <label for="telegram-reply">Mijozga xabar</label>
                            <textarea id="telegram-reply" name="message" maxlength="4000" rows="4" class="form-control mb-3" placeholder="Xizmatingiz bo‘yicha ma’lumot…" required>{{ old('message') }}</textarea>
                            <button class="btn btn-outline-primary w-100"><i class="bx bx-send me-1" aria-hidden="true"></i>Javob yuborish</button>
                        </form>
                    </section>
                    @if(!in_array($lead->status, ['won', 'lost'], true))
                        <form method="POST" action="{{ route('leads.convert', $lead) }}">
                            @csrf
                            <button class="btn btn-success w-100"><i class="bx bx-transfer-alt me-1" aria-hidden="true"></i>Buyurtmaga aylantirish</button>
                        </form>
                    @endif
                @endif
                @if($lead->converted_order_id)
                    <a href="{{ route('orders.show', $lead->converted_order_id) }}" class="btn btn-outline-success">Buyurtma #{{ $lead->converted_order_id }}ni ochish</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
