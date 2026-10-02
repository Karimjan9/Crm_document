@php
    $intake = $lead->botIntakeRequest?->payload;
    $attachments = $lead->telegramMessages->filter(fn ($message) => $message->attachment_path)->sortBy('telegram_message_id');
    $expectedFiles = count(data_get($intake, 'attachments', []) ?? []);
    $requestText = data_get($intake, 'request.notes') ?: data_get($intake, 'request.purpose') ?: 'Hujjat yuborildi. Xizmat turi va muddatni mijoz bilan aniqlashtiring.';
@endphp
@if($intake || $attachments->isNotEmpty())
    <details class="lead-intake mt-2">
        <summary>Telegram murojaati @if($attachments->isNotEmpty()) · {{ $attachments->count() }} fayl @endif</summary>
        <div class="bg-light border rounded p-2 mt-2">
            @if(data_get($intake, 'customer.phone_verified'))
                <small class="d-block text-success mb-1">Mijoz o‘z kontaktini yuborgan</small>
            @endif
            @if($username = data_get($intake, 'customer.telegram_username'))
                <small class="d-block text-muted mb-1">Telegram: {{ '@'.$username }}</small>
            @endif
            @if($intake)
                <div class="lead-intake-text">{{ $requestText }}</div>
                @foreach(['document_type' => 'Hujjat turi', 'urgency' => 'Muddat'] as $field => $label)
                    @if(($value = data_get($intake, 'request.'.$field)) && $value !== 'Mutaxassis aniqlashtiradi')
                        <small class="d-block text-muted mt-1">{{ $label }}: {{ $value }}</small>
                    @endif
                @endforeach
            @endif
            @if($expectedFiles > $attachments->count())
                <small class="d-block text-muted mt-2">Qabul qilingan fayllar: {{ $attachments->count() }} / {{ $expectedFiles }}. Qolgan fayllar hali kelmagan.</small>
            @endif
            @foreach($attachments as $message)
                <div class="lead-intake-file">
                    <a class="btn btn-sm btn-outline-primary text-break" href="{{ route('telegram-messages.file', $message) }}">{{ data_get($message->attachment_meta, 'file_name', 'Hujjat') }}</a>
                    @if($message->body)
                        <div class="lead-intake-text mt-1">{{ $message->body }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </details>
@endif
