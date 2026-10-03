@extends('template')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/bot-requests.css') }}">
@endsection

@section('body')
<div class="page-wrapper bot-requests-shell">
    <div class="page-content">
        <div class="br-heading">
            <div>
                <h1>Botdan so‘rovlar</h1>
                <p class="br-muted mb-0">“Yangi murojaat” orqali kelgan xizmat so‘rovlari va hujjatlar.</p>
            </div>
            <span class="br-source"><i class="bx bxl-telegram" aria-hidden="true"></i> Telegram bot</span>
        </div>

        <div class="br-stats">
            <a href="{{ route('bot-requests.index') }}" class="br-stat {{ $selectedStatus === '' ? 'is-selected' : '' }}">
                <span class="br-stat-icon"><i class="bx bx-conversation" aria-hidden="true"></i></span>
                <div><span class="br-stat-label">Jami so‘rovlar</span><strong>{{ $counts->sum() }}</strong></div>
            </a>
            @foreach(['new' => 'bx-message-rounded-add', 'quoted' => 'bx-send', 'won' => 'bx-check-circle'] as $status => $icon)
                <a href="{{ route('bot-requests.index', ['status' => $status]) }}" class="br-stat {{ $status }} {{ $selectedStatus === $status ? 'is-selected' : '' }}">
                    <span class="br-stat-icon"><i class="bx {{ $icon }}" aria-hidden="true"></i></span>
                    <div><span class="br-stat-label">{{ $statuses[$status] }}</span><strong>{{ $counts[$status] ?? 0 }}</strong></div>
                </a>
            @endforeach
        </div>

        <div class="br-panel">
            <form method="GET" action="{{ route('bot-requests.index') }}" class="br-toolbar">
                <div class="br-search">
                    <label for="bot-request-search">So‘rovni qidirish</label>
                    <input id="bot-request-search" name="q" type="search" maxlength="120" value="{{ $search }}" class="form-control" placeholder="Ism, telefon, Telegram yoki murojaat matni">
                </div>
                <div>
                    <label for="bot-request-status">Holat</label>
                    <select id="bot-request-status" name="status" class="form-select">
                        <option value="">Barcha holatlar</option>
                        @foreach($statuses as $status => $label)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary"><i class="bx bx-search me-1" aria-hidden="true"></i> Qidirish</button>
                @if($search !== '' || $selectedStatus !== '')
                    <a href="{{ route('bot-requests.index') }}" class="btn btn-outline-secondary">Tozalash</a>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table br-table">
                    <thead><tr><th scope="col">Mijoz / kontakt</th><th scope="col">Murojaat</th><th scope="col">Fayllar</th><th scope="col">Mas’ul / filial</th><th scope="col">Sana</th><th scope="col">Holat</th><th scope="col"><span class="visually-hidden">Batafsil</span></th></tr></thead>
                    <tbody>
                        @forelse($requests as $lead)
                            @php
                                $payload = $lead->botIntakeRequest->payload ?? [];
                                $username = data_get($payload, 'customer.telegram_username');
                                $phone = data_get($payload, 'customer.phone') ?: $lead->phone;
                                $text = data_get($payload, 'request.notes') ?: data_get($payload, 'request.purpose') ?: 'Hujjat bo‘yicha murojaat';
                                $expectedFiles = count($payload['attachments'] ?? []);
                            @endphp
                            <tr>
                                <td>
                                    <div class="br-person">
                                        <span class="br-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lead->name, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $lead->name }}</strong>
                                            @if($username && preg_match('/^[A-Za-z0-9_]{5,32}$/', $username))
                                                <a href="https://t.me/{{ $username }}" target="_blank" rel="noopener noreferrer" class="small">{{ '@'.$username }}</a>
                                            @elseif($username)
                                                <small>{{ '@'.$username }}</small>
                                            @endif
                                            @if($phone)
                                                <a class="br-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
                                            @endif
                                            @if(data_get($payload, 'customer.phone_verified'))
                                                <span class="br-verified"><i class="bx bx-check-shield" aria-hidden="true"></i> Kontakt tasdiqlangan</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('bot-requests.show', $lead) }}" class="br-preview">{{ \Illuminate\Support\Str::limit($text, 155) }}</a>
                                    <span class="br-cell-meta">#{{ $lead->id }} @if($lead->interested_service) · {{ $lead->interested_service }} @endif</span>
                                </td>
                                <td>
                                    <span class="br-file-count"><i class="bx bx-paperclip" aria-hidden="true"></i>{{ $lead->intake_files_count }}</span>
                                    @if($expectedFiles > $lead->intake_files_count)
                                        <small class="br-cell-meta">{{ $expectedFiles - $lead->intake_files_count }} ta kutilmoqda</small>
                                    @endif
                                </td>
                                <td><span class="small fw-semibold">{{ $lead->assignedTo?->name ?: 'Biriktirilmagan' }}</span><span class="br-cell-meta">{{ $lead->filial?->name ?: 'Filial belgilanmagan' }}</span></td>
                                <td><span class="small">{{ $lead->botIntakeRequest->created_at->timezone('Asia/Tashkent')->format('d.m.Y') }}</span><span class="br-cell-meta">{{ $lead->botIntakeRequest->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span></td>
                                <td><span class="br-badge {{ $lead->status }}">{{ $statuses[$lead->status] ?? $lead->status }}</span></td>
                                <td><a href="{{ route('bot-requests.show', $lead) }}" class="br-open" aria-label="So‘rov #{{ $lead->id }}: batafsil"><i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="br-empty">
                                <i class="bx bx-message-square-detail" aria-hidden="true"></i>
                                <h2>{{ $search !== '' || $selectedStatus !== '' ? 'So‘rov topilmadi' : 'Hozircha so‘rovlar yo‘q' }}</h2>
                                <p class="br-muted mb-0">{{ $search !== '' || $selectedStatus !== '' ? 'Qidiruv yoki holat filtrini o‘zgartirib ko‘ring.' : 'Mijoz botda “Yangi murojaat” yuborganda, shu yerda paydo bo‘ladi.' }}</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="br-footer">
                <span>{{ $requests->total() }} ta so‘rov · {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} ko‘rsatilmoqda</span>
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
