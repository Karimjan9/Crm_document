@extends('template')

@section('style')
<style>
    .leads-shell { min-height: calc(100vh - 84px); background: #f4f7fb; }
    .leads-shell .page-content { padding: 24px; }
    .leads-page-head { padding: 4px 2px; }
    .lead-create-card { border: 1px solid #e1e8f2; border-radius: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, .05) !important; }
    .lead-create-card .card-body { padding: 18px; }
    .lead-create-card .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin: 0; }
    .lead-create-card .row > * { width: 100%; min-width: 0; margin: 0; padding: 0; }
    .lead-create-card .form-control, .lead-create-card .form-select { min-height: 42px; border-color: #d4deeb; border-radius: 10px; font-size: 13px; }
    .lead-create-card .btn { min-height: 42px; border-radius: 10px; font-weight: 700; }
    .leads-shell .card { border: 1px solid #e1e8f2; border-radius: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, .05) !important; }
    .lead-table thead th { padding: 13px 16px; border-bottom-color: #dfe8f3; background: #f8fbff; color: #51627d; font-size: 11px; font-weight: 800; letter-spacing: .25px; text-transform: uppercase; white-space: nowrap; }
    .lead-table tbody td { padding: 14px 16px; border-color: #edf2f7; color: #24334a; }
    .lead-table tbody tr:hover { background: #fbfdff; }
    .lead-actions .d-flex { flex-wrap: wrap; }
    .lead-attachments h5 { color: #17305d; font-size: 16px; font-weight: 800; }
    @media (max-width: 768px) {
        .leads-shell .page-content { padding: 16px 14px; }
        .leads-page-head { align-items: flex-start !important; flex-direction: column; }
        .lead-create-card .row { grid-template-columns: 1fr; }
        .lead-table thead th, .lead-table tbody td { padding: 12px; }
    }
</style>
@endsection

@section('body')
@php
    $readOnly = auth()->user()?->hasRole('super_admin');
    $statusLabels = [
        'new' => 'Yangi',
        'contacted' => 'Bog‘lanildi',
        'qualified' => 'Mos lead',
        'quoted' => 'Taklif yuborildi',
        'won' => 'Buyurtmaga aylandi',
        'lost' => 'Yo‘qotildi',
    ];
@endphp

<div class="page-wrapper leads-shell">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4 gap-3 leads-page-head">
            <div>
                <h4 class="mb-1">Lead → mijoz → buyurtma</h4>
                <p class="text-muted mb-0">
                    {{ $readOnly ? 'Barcha filiallardagi leadlar uchun kuzatuv ko‘rinishi.' : 'Manba, tezkor aloqa va konversiyani boshqaring.' }}
                </p>
            </div>
            @if($readOnly)
                <span class="badge rounded-pill text-bg-info px-3 py-2">
                    <i class="bx bx-show me-1"></i> Faqat kuzatuv
                </span>
            @endif
        </div>

        @unless($readOnly)
            <div class="card shadow-sm mb-4 lead-create-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('leads.store') }}" class="row g-2">
                        @csrf
                        <input class="form-control col" name="name" placeholder="Mijoz ismi" required>
                        <input class="form-control col" name="phone" placeholder="Telefon">
                        <input class="form-control col" name="source" placeholder="Manba: Instagram, tavsiya">
                        <input class="form-control col" name="interested_service" placeholder="Qiziqqan xizmat">
                        <input class="form-control col" type="datetime-local" name="next_follow_up_at">
                        @if($filials->count() > 1)
                            <select name="filial_id" class="form-select col">
                                @foreach($filials as $filial)
                                    <option value="{{ $filial->id }}">{{ $filial->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <select name="assigned_to_id" class="form-select col">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="status" value="new">
                        <button class="btn btn-primary col-auto">Lead qo‘shish</button>
                    </form>
                </div>
            </div>
        @endunless

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0 lead-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Manba / xizmat</th>
                            <th>Mas’ul</th>
                            <th>Holat</th>
                            <th>Keyingi aloqa</th>
                            <th>Harakat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr id="lead-{{ $lead->id }}">
                                <td>
                                    <strong>{{ $lead->name }}</strong>
                                    <small class="d-block text-muted">{{ $lead->phone }}</small>
                                </td>
                                <td>
                                    {{ $lead->source ?: '—' }}
                                    <small class="d-block text-muted">{{ $lead->interested_service }}</small>
                                </td>
                                <td>{{ $lead->assignedTo?->name ?: '—' }}</td>
                                <td>
                                    @if($readOnly)
                                        <span class="badge text-bg-light border text-dark">{{ $statusLabels[$lead->status] ?? $lead->status }}</span>
                                    @else
                                        <form method="POST" action="{{ route('leads.update', $lead) }}" class="d-flex gap-1">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $lead->name }}">
                                            <input type="hidden" name="phone" value="{{ $lead->phone }}">
                                            <input type="hidden" name="assigned_to_id" value="{{ $lead->assigned_to_id }}">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}" @selected($lead->status === $status)>{{ $statusLabels[$status] ?? $status }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </td>
                                <td class="{{ $lead->next_follow_up_at?->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ optional($lead->next_follow_up_at)->format('d.m.Y H:i') ?: '—' }}
                                </td>
                                <td class="lead-actions">
                                    @if($readOnly)
                                        <span class="text-muted small"><i class="bx bx-lock-alt me-1"></i>Kuzatuv rejimi</span>
                                    @else
                                        <div class="d-flex gap-1">
                                            <details>
                                                <summary class="btn btn-sm btn-outline-primary">Telegram javob</summary>
                                                <form method="POST" action="{{ route('leads.telegram.reply', $lead) }}" class="p-2 bg-white border">
                                                    @csrf
                                                    <textarea name="message" class="form-control form-control-sm mb-1" maxlength="4000" required></textarea>
                                                    <button class="btn btn-sm btn-primary">Yuborish</button>
                                                </form>
                                            </details>
                                            <details>
                                                <summary class="btn btn-sm btn-outline-secondary">Izoh</summary>
                                                <form method="POST" action="{{ route('leads.activity', $lead) }}" class="p-2 bg-white border">
                                                    @csrf
                                                    <select name="type" class="form-select form-select-sm mb-1">
                                                        <option value="call">Qo‘ng‘iroq</option>
                                                        <option value="message">Xabar</option>
                                                        <option value="note">Izoh</option>
                                                    </select>
                                                    <textarea name="body" class="form-control form-control-sm mb-1" required></textarea>
                                                    <button class="btn btn-sm btn-primary">Saqlash</button>
                                                </form>
                                            </details>
                                            @if(!in_array($lead->status, ['won', 'lost'], true))
                                                <form method="POST" action="{{ route('leads.convert', $lead) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success">Orderga aylantirish</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                    @if($lead->activities->isNotEmpty())
                                        <small class="d-block text-muted mt-1">{{ $lead->activities->first()->body }}</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted p-5">Leadlar hali yo‘q.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $leads->links() }}</div>
        </div>

        <div class="card shadow-sm mt-4 lead-attachments">
            <div class="card-body">
                <h5 class="mb-3">Telegramdan kelgan hujjatlar</h5>
                @forelse($leads as $lead)
                    @php($attachments = $lead->telegramMessages->filter(fn ($message) => $message->attachment_path))
                    @if($attachments->isNotEmpty())
                        <div class="border-bottom py-2">
                            <strong>{{ $lead->name }}</strong>
                            @foreach($attachments as $message)
                                <a class="btn btn-sm btn-outline-primary ms-2" href="{{ route('telegram-messages.file', $message) }}">
                                    {{ data_get($message->attachment_meta, 'file_name', 'Hujjat') }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                @empty
                    <p class="text-muted mb-0">Telegramdan hujjat kelmagan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
