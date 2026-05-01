@extends('template')

@section('style')
<style>
    .document-shell {
        background: #f5f7fb;
        color: #172033;
        min-height: calc(100vh - 64px);
    }

    .document-shell .page-content {
        padding: 22px;
    }

    .doc-toolbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .doc-title h4 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #102a56;
    }

    .doc-title p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .doc-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 8px;
        padding: 10px 14px;
        background: #1d4ed8;
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .doc-action:hover {
        background: #1e40af;
        color: #fff;
    }

    .filter-panel,
    .data-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .filter-panel {
        padding: 16px;
        margin-bottom: 18px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(140px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .filter-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
    }

    .filter-field input,
    .filter-field select {
        width: 100%;
        height: 40px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 10px;
        color: #172033;
        background: #fff;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
    }

    .filter-actions .btn {
        height: 40px;
        border-radius: 8px;
        font-weight: 600;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .metric-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .metric-card span {
        display: block;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .metric-card strong {
        display: block;
        margin-top: 8px;
        font-size: 24px;
        color: #0f172a;
        line-height: 1.2;
    }

    .metric-card small {
        display: block;
        margin-top: 6px;
        color: #94a3b8;
    }

    .mini-trend {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 8px;
        min-height: 120px;
        align-items: end;
        padding: 16px;
    }

    .trend-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .trend-bar {
        width: 100%;
        min-height: 6px;
        border-radius: 8px 8px 0 0;
        background: #2563eb;
    }

    .trend-item span {
        font-size: 11px;
        color: #64748b;
        white-space: nowrap;
    }

    .data-panel {
        overflow: hidden;
        margin-bottom: 18px;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .panel-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #102a56;
    }

    .panel-header span {
        color: #64748b;
        font-size: 13px;
    }

    .monitor-table {
        margin: 0;
        width: 100%;
    }

    .monitor-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px;
        white-space: nowrap;
    }

    .monitor-table td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #eef2f7;
        color: #172033;
    }

    .doc-code {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 12px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-process { background: #fff7ed; color: #c2410c; }
    .status-finish { background: #ecfdf5; color: #047857; }
    .pay-paid { background: #ecfdf5; color: #047857; }
    .pay-partial { background: #fffbeb; color: #b45309; }
    .pay-debt { background: #fef2f2; color: #b91c1c; }

    .money-main {
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    .money-muted {
        color: #64748b;
        font-size: 12px;
        white-space: nowrap;
    }

    .row-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        white-space: nowrap;
    }

    .row-actions .btn {
        border-radius: 8px;
        font-weight: 600;
    }

    @media (max-width: 1200px) {
        .filter-grid { grid-template-columns: repeat(3, minmax(140px, 1fr)); }
        .metric-grid { grid-template-columns: repeat(2, minmax(180px, 1fr)); }
    }

    @media (max-width: 768px) {
        .document-shell .page-content { padding: 14px; }
        .doc-toolbar { flex-direction: column; }
        .doc-action { width: 100%; justify-content: center; }
        .filter-grid,
        .metric-grid { grid-template-columns: 1fr; }
        .mini-trend { grid-template-columns: repeat(6, 1fr); }
    }
</style>
@endsection

@section('body')
@php
    $money = fn ($amount) => number_format((float) $amount, 0, '.', ' ');
    $maxMonthly = max(collect($monthlyStats)->pluck('documents')->max() ?: 1, 1);
    $paidRate = $summary['final_price'] > 0 ? min(100, round(($summary['paid_amount'] / $summary['final_price']) * 100)) : 0;
@endphp

<div class="page-wrapper document-shell">
    <div class="page-content">
        <div class="doc-toolbar">
            <div class="doc-title">
                <h4>Hujjat monitoringi</h4>
                <p>Filtrlar, to'lov nazorati va oylar kesimidagi umumiy ko'rsatkichlar.</p>
            </div>
            <a href="{{ route($routePrefix . '.document.create') }}" class="doc-action">
                <i class="bx bx-plus"></i>
                Yangi hujjat
            </a>
        </div>

        <form method="GET" action="{{ route($routePrefix . '.document.index') }}" class="filter-panel">
            <div class="filter-grid">
                <div class="filter-field">
                    <label>Qidiruv</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Kod, mijoz, xizmat">
                </div>
                <div class="filter-field">
                    <label>Filial</label>
                    <select name="filial_id">
                        <option value="">Barchasi</option>
                        @foreach($filials as $filial)
                            <option value="{{ $filial->id }}" @selected((string) request('filial_id') === (string) $filial->id)>{{ $filial->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Foydalanuvchi</label>
                    <select name="user_id">
                        <option value="">Barchasi</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Jarayon</label>
                    <select name="process_mode">
                        <option value="">Barchasi</option>
                        @foreach($processLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('process_mode') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Hujjat holati</label>
                    <select name="status_doc">
                        <option value="">Barchasi</option>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status_doc') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>To'lov holati</label>
                    <select name="payment_status">
                        <option value="">Barchasi</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>To'langan</option>
                        <option value="partial" @selected(request('payment_status') === 'partial')>Qisman</option>
                        <option value="debt" @selected(request('payment_status') === 'debt')>Qarzdor</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label>Yil</label>
                    <select name="year">
                        @foreach($yearOptions as $year)
                            <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Oy</label>
                    <select name="month">
                        <option value="">Barcha oylar</option>
                        @foreach($monthNames as $value => $label)
                            <option value="{{ $value }}" @selected((int) $selectedMonth === (int) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label>Sana dan</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="filter-field">
                    <label>Sana gacha</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filtrlash</button>
                    <a href="{{ route($routePrefix . '.document.index') }}" class="btn btn-light">Tozalash</a>
                </div>
            </div>
        </form>

        <div class="metric-grid">
            <div class="metric-card">
                <span>Hujjatlar</span>
                <strong>{{ $summary['documents'] }}</strong>
                <small>Tanlangan davr bo'yicha</small>
            </div>
            <div class="metric-card">
                <span>Jami summa</span>
                <strong>{{ $money($summary['final_price']) }}</strong>
                <small>so'm</small>
            </div>
            <div class="metric-card">
                <span>To'langan</span>
                <strong>{{ $money($summary['paid_amount']) }}</strong>
                <small>{{ $paidRate }}% yopilgan</small>
            </div>
            <div class="metric-card">
                <span>Qoldiq</span>
                <strong>{{ $money($summary['balance']) }}</strong>
                <small>{{ $summary['partial_documents'] }} ta qisman, {{ $summary['debt_documents'] }} ta qarzdor</small>
            </div>
        </div>

        <div class="data-panel">
            <div class="panel-header">
                <h5>{{ $selectedYear }} yil oylar kesimi</h5>
                <span>Hujjat soni bo'yicha tezkor ko'rinish</span>
            </div>
            <div class="mini-trend">
                @foreach($monthlyStats as $month)
                    @php $height = 8 + (($month['documents'] / $maxMonthly) * 90); @endphp
                    <div class="trend-item" title="{{ $month['label'] }}: {{ $month['documents'] }} ta">
                        <div class="trend-bar" style="height: {{ $height }}px;"></div>
                        <span>{{ mb_substr($month['label'], 0, 3) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="data-panel">
            <div class="panel-header">
                <h5>Hujjatlar ro'yxati</h5>
                <span>{{ $documents->total() }} ta yozuv</span>
            </div>
            <div class="table-responsive">
                <table class="table monitor-table align-middle">
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>Mijoz / Xizmat</th>
                            <th>Filial</th>
                            <th>Mas'ul</th>
                            <th>Jarayon</th>
                            <th>Holat</th>
                            <th>Summa</th>
                            <th>To'lov</th>
                            <th>Qoldiq</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            @php
                                $balance = max((float) $doc->final_price - (float) $doc->paid_amount, 0);
                                $processKey = $doc->process_mode ?: 'service';
                                $statusKey = $doc->status_doc ?: 'process';
                                $paymentClass = $balance <= 0 ? 'pay-paid' : ((float) $doc->paid_amount > 0 ? 'pay-partial' : 'pay-debt');
                                $paymentLabel = $balance <= 0 ? "To'langan" : ((float) $doc->paid_amount > 0 ? 'Qisman' : 'Qarzdor');
                            @endphp
                            <tr>
                                <td>
                                    <span class="doc-code">{{ $doc->document_code ?: ('DOC-' . $doc->id) }}</span>
                                    <div class="money-muted">{{ optional($doc->created_at)->format('d.m.Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $doc->client->name ?? 'Mijoz topilmadi' }}</div>
                                    <div class="money-muted">{{ $doc->service->name ?? 'Xizmat topilmadi' }}</div>
                                </td>
                                <td>{{ $doc->filial->name ?? 'Noma\'lum' }}</td>
                                <td>
                                    <div>{{ $doc->user->name ?? 'Noma\'lum' }}</div>
                                    <div class="money-muted">{{ $doc->user->login ?? '' }}</div>
                                </td>
                                <td>{{ $processLabels[$processKey] ?? 'Xizmat' }}</td>
                                <td><span class="status-pill status-{{ $statusKey }}">{{ $statusLabels[$statusKey] ?? $statusKey }}</span></td>
                                <td><span class="money-main">{{ $money($doc->final_price) }}</span></td>
                                <td>
                                    <span class="status-pill {{ $paymentClass }}">{{ $paymentLabel }}</span>
                                    <div class="money-muted">{{ $money($doc->paid_amount) }} so'm</div>
                                </td>
                                <td><span class="money-main">{{ $money($balance) }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button"
                                                class="btn btn-sm btn-primary js-payment"
                                                data-document-id="{{ $doc->id }}"
                                                data-document-code="{{ $doc->document_code ?: ('DOC-' . $doc->id) }}"
                                                data-balance="{{ $balance }}"
                                                @disabled($balance <= 0)>
                                            To'lov
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary js-history"
                                                data-document-id="{{ $doc->id }}">
                                            Tarix
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">Filtr bo'yicha hujjat topilmadi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>

@include('partials.payment_modal')
@include('partials.history_modal')
@endsection

@section('script_include_end_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const paymentUrl = @json(route($routePrefix . '.add_payment'));
    const historyUrl = @json(route($routePrefix . '.payments', ['document' => '__id__']));
    const paymentTypeLabels = @json($paymentTypes);

    const showModal = (id) => {
        const modalElement = document.getElementById(id);
        if (!modalElement || typeof bootstrap === 'undefined') {
            return;
        }
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, function (char) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
    });

    const formatMoney = (value) => `${Number(value || 0).toLocaleString('uz-UZ')} so'm`;

    document.querySelectorAll('.js-payment').forEach((button) => {
        button.addEventListener('click', function () {
            const balance = Number(this.dataset.balance || 0);

            document.getElementById('document_id').value = this.dataset.documentId;
            document.getElementById('document_code').value = this.dataset.documentCode;
            document.getElementById('balance').value = formatMoney(balance);

            const amountInput = document.querySelector('#paymentForm input[name="amount"]');
            amountInput.max = balance;
            amountInput.value = '';

            showModal('paymentModal');
        });
    });

    document.querySelectorAll('.js-history').forEach((button) => {
        button.addEventListener('click', async function () {
            const tbody = document.getElementById('historyTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Yuklanmoqda...</td></tr>';
            showModal('historyModal');

            try {
                const response = await fetch(historyUrl.replace('__id__', this.dataset.documentId), {
                    headers: {'Accept': 'application/json'}
                });
                const payments = await response.json();

                if (!payments.length) {
                    tbody.innerHTML = "<tr><td colspan=\"5\" class=\"text-center text-muted py-4\">To'lov tarixi yo'q.</td></tr>";
                    return;
                }

                tbody.innerHTML = payments.map((payment, index) => {
                    const date = payment.created_at ? new Date(payment.created_at).toLocaleString('uz-UZ') : '-';
                    const paymentType = payment.payment_type_label || paymentTypeLabels[payment.payment_type] || payment.payment_type;

                    return `<tr>
                        <td>${index + 1}</td>
                        <td>${formatMoney(payment.amount)}</td>
                        <td>${escapeHtml(paymentType)}</td>
                        <td>${escapeHtml(payment.paid_by_name || payment.paid_by_admin_id || '-')}</td>
                        <td>${escapeHtml(date)}</td>
                    </tr>`;
                }).join('');
            } catch (error) {
                tbody.innerHTML = "<tr><td colspan=\"5\" class=\"text-center text-danger py-4\">Tarixni yuklab bo'lmadi.</td></tr>";
            }
        });
    });

    document.getElementById('paymentForm')?.addEventListener('submit', async function (event) {
        event.preventDefault();

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        try {
            const response = await fetch(paymentUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    document_id: document.getElementById('document_id').value,
                    amount: this.querySelector('input[name="amount"]').value,
                    payment_type: this.querySelector('select[name="payment_type"]').value
                })
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok || payload.status !== 'success') {
                alert(payload.message || "Xatolik! Server to'lovni qabul qilmadi.");
                return;
            }

            window.location.reload();
        } finally {
            submitButton.disabled = false;
        }
    });
});
</script>
@endsection
