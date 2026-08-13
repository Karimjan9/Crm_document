@extends('template')

@section('body')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Hujjat tafsilotlari</h3>
            <div class="text-muted">{{ $document->document_code ?: 'DOC-'.$document->id }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route($routePrefix . '.document.index') }}">Orqaga</a>
            <a class="btn btn-primary" href="{{ route($routePrefix . '.document.edit', $document) }}">Tahrirlash</a>
            <a class="btn btn-outline-primary" href="{{ route('documents.workflow.index') }}">Ishlar Kanban</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Mijoz</dt>
                        <dd class="col-sm-8">{{ $document->client?->name ?? 'Topilmadi' }}</dd>
                        <dt class="col-sm-4">Xizmat</dt>
                        <dd class="col-sm-8">{{ $document->service?->name ?? 'Topilmadi' }}</dd>
                        <dt class="col-sm-4">Filial</dt>
                        <dd class="col-sm-8">{{ $document->filial?->name ?? 'Noma’lum' }}</dd>
                        <dt class="col-sm-4">Mas’ul xodim</dt>
                        <dd class="col-sm-8">{{ $document->assignedTo?->name ?: ($document->user?->name ?? 'Noma’lum') }}</dd>
                        <dt class="col-sm-4">QA xodimi</dt>
                        <dd class="col-sm-8">{{ $document->qaUser?->name ?: 'Biriktirilmagan' }}</dd>
                        <dt class="col-sm-4">Jarayon</dt>
                        <dd class="col-sm-8">{{ $document->process_mode ?: 'service' }}</dd>
                        <dt class="col-sm-4">Holat</dt>
                        <dd class="col-sm-8"><span class="badge bg-{{ $document->status_color }}">{{ $document->status_label }}</span></dd>
                        <dt class="col-sm-4">Priority / Queue</dt>
                        <dd class="col-sm-8">{{ ucfirst($document->priority ?: 'normal') }} / {{ ucfirst(str_replace('_', ' ', $document->queue ?: 'general')) }}</dd>
                        <dt class="col-sm-4">Workload / qayta ishlash</dt>
                        <dd class="col-sm-8">{{ $document->estimated_workload_minutes ?: 0 }} daqiqa / {{ $document->rework_count ?: 0 }} marta</dd>
                        <dt class="col-sm-4">Deadline</dt>
                        <dd class="col-sm-8">{{ $document->deadline_time ?? 0 }} kun</dd>
                        <dt class="col-sm-4">Izoh</dt>
                        <dd class="col-sm-8">{{ $document->description ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h5 class="card-title">Document status tarixi</h5>
                    @forelse($document->statusHistories as $history)
                        <div class="border-start border-primary ps-3 mb-3">
                            <strong>{{ \App\Models\DocumentsModel::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</strong>
                            <div class="small text-muted">{{ $history->changedBy?->name ?: 'System' }} · {{ optional($history->created_at)->format('d.m.Y H:i') }}</div>
                            @if($history->reason)<div class="small">{{ $history->reason }}</div>@endif
                            @if($history->comment)<div class="small text-muted">{{ $history->comment }}</div>@endif
                        </div>
                    @empty
                        <span class="text-muted">Status tarixi hali yo‘q.</span>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">Majburiy checklist</h5>
                        @php($requiredChecklist = $document->checklists->where('is_required', true))
                        <span class="badge {{ $requiredChecklist->where('is_completed', true)->count() === $requiredChecklist->count() ? 'bg-success' : 'bg-warning text-dark' }}">{{ $requiredChecklist->where('is_completed', true)->count() }}/{{ $requiredChecklist->count() }}</span>
                    </div>
                    @forelse($document->checklists as $item)
                        <div class="d-flex align-items-start gap-2 border-bottom py-2">
                            <span class="{{ $item->is_completed ? 'text-success' : 'text-danger' }}">{{ $item->is_completed ? '✓' : '○' }}</span>
                            <div><strong>{{ $item->title }}</strong><small class="d-block text-muted">{{ $item->is_required ? 'Majburiy' : 'Ixtiyoriy' }}{{ $item->requires_file ? ' · fayl kerak' : ' · manual tekshiruv' }}</small></div>
                        </div>
                    @empty
                        <div class="text-muted">Bu xizmat uchun checklist talabi sozlanmagan.</div>
                    @endforelse
                    @if($document->latestQaReview)
                        <div class="alert {{ $document->latestQaReview->result === 'passed' ? 'alert-success' : 'alert-warning' }} mt-3 mb-0 py-2">
                            QA: <strong>{{ $document->latestQaReview->result === 'passed' ? 'Tasdiqlandi' : ucfirst($document->latestQaReview->result) }}</strong>
                            · {{ $document->latestQaReview->reviewer?->name ?: 'System' }}
                            @if($document->latestQaReview->comment) — {{ $document->latestQaReview->comment }} @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">To‘lov</h5>
                    <div class="d-flex justify-content-between"><span>Final narx</span><strong>{{ number_format((float) $document->final_price, 2, '.', ' ') }}</strong></div>
                    <div class="d-flex justify-content-between"><span>To‘langan</span><strong>{{ number_format((float) $document->paid_amount, 2, '.', ' ') }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Qoldiq</span><strong>{{ number_format(max((float) $document->final_price - (float) $document->paid_amount, 0), 2, '.', ' ') }}</strong></div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Fayllar</h5>
                    @forelse($document->files as $file)
                        <a class="d-block mb-2" href="{{ $file->file_url }}">{{ $file->original_name }}</a>
                    @empty
                        <span class="text-muted">Fayl mavjud emas.</span>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h5 class="card-title">Narx tafsiloti</h5>
                    <p class="small text-muted mb-2">Snapshot vaqtida saqlangan tariflar: {{ optional($document->pricing_locked_at)->format('d.m.Y H:i') ?: 'legacy hujjat' }}</p>
                    <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Satr</th><th>Manba</th><th class="text-end">Summa</th></tr></thead><tbody>
                    @forelse(data_get($document->pricing_snapshot, 'line_items', []) as $line)
                        <tr><td>{{ $line['line_type'] ?? '—' }}<br><span class="small text-muted">{{ $line['price_source'] ?? (!empty($line['price_tariff_id']) ? 'tariff' : 'calculated') }}</span></td><td>{{ $line['name'] ?? '—' }}<br><span class="small text-muted">Tariff #{{ $line['price_tariff_id'] ?? 'legacy' }}</span></td><td class="text-end">{{ number_format((float)($line['total_price'] ?? 0),2,'.',' ') }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">Narx snapshot’i mavjud emas.</td></tr>
                    @endforelse
                    </tbody></table></div>
                    <div class="mt-2 small"><strong>Approval:</strong> {{ $document->discount_approval_status ?: 'not_required' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
