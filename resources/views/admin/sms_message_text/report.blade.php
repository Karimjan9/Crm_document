@extends('template')

@section('style')
<style>
    .sms-report {
        padding: 24px;
    }

    .sms-report__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .sms-report__title {
        margin: 0;
        color: #15172a;
        font-size: 24px;
        font-weight: 800;
    }

    .sms-report__subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
    }

    .sms-report__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .sms-report__btn {
        border: 1px solid rgba(37, 99, 235, 0.18);
        background: #2563eb;
        color: #fff;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .sms-report__btn:hover {
        color: #fff;
        background: #1e3a8a;
    }

    .sms-report__btn--light {
        background: #fff;
        color: #1e3a8a;
    }

    .sms-report__btn--light:hover {
        color: #1e3a8a;
        background: #eff6ff;
    }

    .sms-report__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .sms-report__card,
    .sms-report__panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(30, 58, 138, 0.08);
    }

    .sms-report__card {
        padding: 18px;
    }

    .sms-report__label {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sms-report__value {
        margin-top: 8px;
        color: #15172a;
        font-size: 32px;
        font-weight: 900;
    }

    .sms-report__panel {
        padding: 18px;
        margin-bottom: 18px;
    }

    .sms-report__panel-title {
        margin: 0 0 14px;
        color: #15172a;
        font-size: 18px;
        font-weight: 800;
    }

    .sms-type-list {
        display: grid;
        gap: 10px;
    }

    .sms-type-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .sms-type-row strong {
        color: #15172a;
    }

    .sms-type-row span {
        color: #2563eb;
        font-weight: 900;
    }

    .sms-report table {
        width: 100%;
        border-collapse: collapse;
    }

    .sms-report th,
    .sms-report td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
    }

    .sms-report th {
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        background: #f8fafc;
    }

    .sms-report td {
        color: #334155;
        font-size: 14px;
    }

    .sms-report__empty {
        padding: 22px;
        border-radius: 14px;
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-align: center;
    }

    @media (max-width: 900px) {
        .sms-report__head {
            align-items: flex-start;
            flex-direction: column;
        }

        .sms-report__grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content sms-report">
        <div class="sms-report__head">
            <div>
                <h1 class="sms-report__title">SMS hisobot</h1>
                <p class="sms-report__subtitle">SMS shablonlari holati va turlar kesimi.</p>
            </div>
            <div class="sms-report__actions">
                <a href="{{ route('superadmin.sms_message_text.index') }}" class="sms-report__btn sms-report__btn--light">
                    <i class='bx bx-cog'></i>
                    Sozlamalar
                </a>
                <a href="{{ route('superadmin.sms_message_text.create') }}" class="sms-report__btn">
                    <i class='bx bx-plus'></i>
                    Yangi shablon
                </a>
            </div>
        </div>

        <div class="sms-report__grid">
            <div class="sms-report__card">
                <div class="sms-report__label">Jami shablonlar</div>
                <div class="sms-report__value">{{ $stats['total'] }}</div>
            </div>
            <div class="sms-report__card">
                <div class="sms-report__label">Matni bor</div>
                <div class="sms-report__value">{{ $stats['filled'] }}</div>
            </div>
            <div class="sms-report__card">
                <div class="sms-report__label">Bo'sh shablon</div>
                <div class="sms-report__value">{{ $stats['empty'] }}</div>
            </div>
        </div>

        <div class="sms-report__panel">
            <h2 class="sms-report__panel-title">Turlar bo'yicha</h2>
            <div class="sms-type-list">
                @foreach($typeLabels as $type => $label)
                    <div class="sms-type-row">
                        <strong>{{ $label }}</strong>
                        <span>{{ $typeStats[$type] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="sms-report__panel">
            <h2 class="sms-report__panel-title">Oxirgi o'zgartirilgan shablonlar</h2>

            @if($recentMessages->isEmpty())
                <div class="sms-report__empty">Hali SMS shablonlari kiritilmagan.</div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomi</th>
                                <th>Turi</th>
                                <th>Matn</th>
                                <th>Yangilangan vaqt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMessages as $message)
                                <tr>
                                    <td>{{ $message->name }}</td>
                                    <td>{{ $typeLabels[$message->type] ?? $message->type }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($message->message_text1 ?: $message->message_text2 ?: $message->message_text3 ?: 'Matn kiritilmagan', 90) }}</td>
                                    <td>{{ optional($message->updated_at)->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
