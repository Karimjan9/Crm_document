@extends('template')

@section('body')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
        <div><h3>Hujjat jarayoni</h3><div class="text-muted">{{ $document->document_code }} · {{ $document->client?->name }} · {{ $document->service?->name }}</div></div>
        @if($document->order && auth()->user()->can('view', $document->order))
            <a href="{{ route('orders.show', $document->order) }}" class="btn btn-outline-primary align-self-start">Buyurtmaga qaytish</a>
        @endif
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-body">
                <h5>Hujjatlar va majburiy tekshiruvlar</h5>
                <div class="mb-3">Holat: <strong>{{ $document->status_label }}</strong> · Qoldiq: <strong>{{ number_format(max(0, $document->final_price - $document->paid_amount), 0, ',', ' ') }} UZS</strong></div>
                @forelse($document->files as $file)
                    <a class="d-block mb-2" href="{{ $file->file_url }}">{{ $file->original_name }}</a>
                @empty<div class="text-muted mb-2">Fayl hali yuklanmagan.</div>@endforelse
                @foreach($document->checklists as $item)
                    <div class="border-top py-2 d-flex justify-content-between align-items-center gap-2">
                        <div><strong>{{ $item->title }}</strong><small class="d-block text-muted">{{ $item->is_required ? 'Majburiy' : 'Ixtiyoriy' }}{{ $item->requires_file ? ' · Fayl talab qilinadi' : '' }}</small></div>
                        @can('checklistUpdate', $document)
                            <form method="POST" action="{{ route('documents.process.checklist', [$document, $item]) }}">@csrf
                                <input type="hidden" name="is_completed" value="{{ $item->is_completed ? 0 : 1 }}">
                                <button class="btn btn-sm {{ $item->is_completed ? 'btn-outline-success' : 'btn-outline-primary' }}">{{ $item->is_completed ? 'Bajarilgan — qayta ochish' : 'Bajarildi deb belgilash' }}</button>
                            </form>
                        @else<span>{{ $item->is_completed ? 'Bajarilgan' : 'Kutilmoqda' }}</span>@endcan
                    </div>
                @endforeach
            </div></div>
            @can('qaReview', $document)
                <div class="card mb-3"><div class="card-body">
                    <h5>Sifat tekshiruvi — QA</h5>
                    @if($document->latestQaReview)<p>Oxirgi natija: <strong>{{ $document->latestQaReview->result === 'passed' ? 'Tasdiqlangan' : 'Qayta ishlash kerak' }}</strong> · {{ $document->latestQaReview->reviewer?->name }}</p>@endif
                    <form method="POST" action="{{ route('documents.process.qa', $document) }}">@csrf
                        <label for="qa-result" class="form-label">Natija</label><select id="qa-result" name="result" class="form-select mb-2"><option value="passed">Tasdiqlash</option><option value="failed">Qayta ishlashga qaytarish</option></select>
                        <input name="reason" maxlength="500" class="form-control mb-2" placeholder="Sabab">
                        <textarea name="comment" maxlength="4000" class="form-control mb-2" placeholder="Tekshiruv izohi"></textarea>
                        <button class="btn btn-success">QA natijasini saqlash</button>
                    </form>
                </div></div>
            @endcan
            <div class="card"><div class="card-body"><h5>Jarayon tarixi</h5>
                @foreach($document->statusHistories as $history)
                    <div class="border-bottom py-2"><strong>{{ \App\Models\DocumentsModel::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</strong><small class="d-block text-muted">{{ $history->changedBy?->name ?: 'Tizim' }} · {{ $history->created_at?->format('d.m.Y H:i') }}</small>@if($history->reason)<div>{{ $history->reason }}</div>@endif</div>
                @endforeach
            </div></div>
        </div>
        <div class="col-lg-4">
            @can('workflowUpdate', $document)
                <div class="card mb-3"><div class="card-body"><h5>Keyingi bosqich</h5>
                    @if($transitions)
                        <form method="POST" action="{{ route('documents.process.status', $document) }}">@csrf
                            <label for="document-status" class="form-label">Holat</label><select id="document-status" name="status" class="form-select mb-2">@foreach($transitions as $status)<option value="{{ $status }}">{{ \App\Models\DocumentsModel::STATUS_LABELS[$status] }}</option>@endforeach</select>
                            <input name="reason" maxlength="500" class="form-control mb-2" placeholder="O‘zgarish sababi">
                            <button class="btn btn-primary">Bosqichni saqlash</button>
                        </form>
                    @else<p class="text-muted">Jarayon yakunlangan.</p>@endif
                </div></div>
            @endcan
            <div class="card"><div class="card-body"><h5>Mas’ul xodimlar</h5>
                <p>Xodim: <strong>{{ $document->assignedTo?->name ?: 'Biriktirilmagan' }}</strong><br>QA: <strong>{{ $document->qaUser?->name ?: 'Biriktirilmagan' }}</strong></p>
                @can('workflowAssign', $document)
                    <form method="POST" action="{{ route('documents.process.assignment', $document) }}">@csrf
                        <label for="assigned-worker" class="form-label">Ishni bajaruvchi</label><select id="assigned-worker" name="assigned_to_id" class="form-select mb-2"><option value="">Biriktirilmagan</option>@foreach($workers as $worker)<option value="{{ $worker->id }}" @selected($document->assigned_to_id === $worker->id)>{{ $worker->name }}</option>@endforeach</select>
                        <label for="qa-worker" class="form-label">QA tekshiruvchi</label><select id="qa-worker" name="qa_user_id" class="form-select mb-2"><option value="">Biriktirilmagan</option>@foreach($workers as $worker)<option value="{{ $worker->id }}" @selected($document->qa_user_id === $worker->id)>{{ $worker->name }}</option>@endforeach</select>
                        <input name="notes" maxlength="2000" class="form-control mb-2" placeholder="Biriktirish izohi">
                        <button class="btn btn-outline-primary">Xodimlarni biriktirish</button>
                    </form>
                @endcan
            </div></div>
        </div>
    </div>
</div>
@endsection
