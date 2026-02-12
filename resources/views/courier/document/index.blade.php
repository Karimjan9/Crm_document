@extends('template')

@section('style')
<style>
.page-wrapper { padding: 24px; }
.card { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.card-header { background: #1e3a8a; color: #fff; font-weight: 600; }
.badge { padding: 6px 10px; border-radius: 10px; font-size: 12px; }
.badge-sent { background: #f59e0b; color: #1f2937; }
.badge-accepted { background: #22c55e; color: #fff; }
.file-list { display: grid; gap: 6px; }
.file-link { font-size: 12px; color: #1e3a8a; text-decoration: none; }
.file-link:hover { text-decoration: underline; }
.btn-action { border-radius: 8px; }
.details-actions {
    display: flex;
    justify-content: center;
}
.offcanvas {
    width: 420px;
}
.offcanvas-header {
    border-bottom: 1px solid #e2e8f0;
}
.offcanvas-body {
    background: #f8fafc;
}
.oc-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
}
.oc-title {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 6px;
}
.oc-value {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}
.oc-sub {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #475569;
}
.oc-files {
    display: grid;
    gap: 6px;
    margin-top: 6px;
}
.oc-file-link {
    display: inline-flex;
    align-items: center;
    padding: 6px 8px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #1e293b;
    text-decoration: none;
    font-size: 12px;
}
.oc-file-link:hover {
    background: #eef2ff;
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-header">Courier - Hujjatlar</div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Document Code</th>
                                <th>Mijoz</th>
                                <th>Xizmat</th>
                                <th>Yuborgan</th>
                                <th>Yuborilgan</th>
                                <th>Status</th>
                                <th>Fayllar</th>
                                <th>Batafsil</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $key => $assignment)
                                @php $doc = $assignment->document; @endphp
                                @php
                                    $filesData = ($doc->files ?? collect())->map(function ($f) {
                                        return ['name' => $f->original_name, 'url' => $f->file_url];
                                    })->values();
                                @endphp
                                <tr class="doc-row"
                                    data-document-code="{{ $doc->document_code ?? '-' }}"
                                    data-client="{{ $doc->client->name ?? '-' }}"
                                    data-service="{{ $doc->service->name ?? '-' }}"
                                    data-sender="{{ $assignment->sentBy->name ?? '-' }}"
                                    data-sent-at="{{ optional($assignment->sent_at)->format('d.m.Y H:i') ?? '-' }}"
                                    data-status="{{ $assignment->status }}"
                                    data-files='@json($filesData)'>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $doc->document_code ?? '-' }}</td>
                                    <td>{{ $doc->client->name ?? '-' }}</td>
                                    <td>{{ $doc->service->name ?? '-' }}</td>
                                    <td>{{ $assignment->sentBy->name ?? '-' }}</td>
                                    <td>{{ optional($assignment->sent_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                    <td>
                                        @if($assignment->status === 'sent')
                                            <span class="badge badge-sent">Sent</span>
                                        @elseif($assignment->status === 'accepted')
                                            <span class="badge badge-accepted">Accepted</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $doc->files?->count() ? $doc->files->count().' ta' : '-' }}
                                    </td>
                                    <td>
                                        <div class="details-actions">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary details-toggle"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#docDetailsOffcanvas"
                                                aria-controls="docDetailsOffcanvas">
                                                Batafsil
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        @if($assignment->status === 'sent')
                                            <button class="btn btn-sm btn-success btn-action decision-btn"
                                                data-action="accept"
                                                data-id="{{ $assignment->id }}">
                                                Qabul qilish
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-action decision-btn"
                                                data-action="reject"
                                                data-id="{{ $assignment->id }}">
                                                Rad etish
                                            </button>
                                        @elseif($assignment->status === 'accepted')
                                            <button class="btn btn-sm btn-primary btn-action return-btn"
                                                data-id="{{ $assignment->id }}">
                                                Qaytarish
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">Hujjatlar mavjud emas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas: Details -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="docDetailsOffcanvas" aria-labelledby="docDetailsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="docDetailsLabel">Hujjat batafsil ma'lumot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="oc-section">
            <div class="oc-title">Asosiy</div>
            <div class="oc-value">Code: <span id="ocDocumentCode">-</span></div>
            <span class="oc-sub">Mijoz: <span id="ocClient">-</span></span>
            <span class="oc-sub">Xizmat: <span id="ocService">-</span></span>
        </div>
        <div class="oc-section">
            <div class="oc-title">Courier Info</div>
            <div class="oc-value">Yuborgan: <span id="ocSender">-</span></div>
            <span class="oc-sub">Yuborilgan: <span id="ocSentAt">-</span></span>
            <span class="oc-sub">Status: <span id="ocStatus">-</span></span>
        </div>
        <div class="oc-section">
            <div class="oc-title">Fayllar</div>
            <div class="oc-value"><span id="ocFilesCount">-</span></div>
            <div class="oc-files" id="ocFiles"></div>
        </div>
    </div>
</div>

<!-- Decision Modal -->
<div class="modal fade" id="decisionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="decisionForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Courier Qarori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Izoh</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Izoh..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary">Tasdiqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="returnForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hujjatni Qaytarish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Izoh</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Izoh..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary">Qaytarish</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script_include_end_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const decisionBase = {
        accept: "{{ route('courier.documents.accept', ['documentCourier' => '__id__']) }}",
        reject: "{{ route('courier.documents.reject', ['documentCourier' => '__id__']) }}"
    };
    const returnBase = "{{ route('courier.documents.return', ['documentCourier' => '__id__']) }}";

    document.querySelectorAll('.decision-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.dataset.action;
            const id = this.dataset.id;
            const form = document.getElementById('decisionForm');
            form.action = decisionBase[action].replace('__id__', id);
            const modal = new bootstrap.Modal(document.getElementById('decisionModal'));
            modal.show();
        });
    });

    document.querySelectorAll('.return-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const form = document.getElementById('returnForm');
            form.action = returnBase.replace('__id__', id);
            const modal = new bootstrap.Modal(document.getElementById('returnModal'));
            modal.show();
        });
    });

    const table = document.querySelector('table');
    if (table) {
        table.addEventListener('click', function (e) {
            const btn = e.target.closest('.details-toggle');
            if (!btn) return;
            const row = btn.closest('tr.doc-row');
            if (!row) return;

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value ?? '-';
            };

            setText('ocDocumentCode', row.dataset.documentCode || '-');
            setText('ocClient', row.dataset.client || '-');
            setText('ocService', row.dataset.service || '-');
            setText('ocSender', row.dataset.sender || '-');
            setText('ocSentAt', row.dataset.sentAt || '-');
            setText('ocStatus', row.dataset.status || '-');

            const filesWrap = document.getElementById('ocFiles');
            const filesCount = document.getElementById('ocFilesCount');
            if (!filesWrap || !filesCount) return;

            filesWrap.innerHTML = '';
            let files = [];
            try {
                files = JSON.parse(row.dataset.files || '[]');
            } catch (err) {
                files = [];
            }

            if (files.length) {
                filesCount.textContent = `${files.length} ta`;
                files.forEach(file => {
                    const link = document.createElement('a');
                    link.className = 'oc-file-link';
                    link.href = file.url;
                    link.target = '_blank';
                    link.textContent = file.name;
                    filesWrap.appendChild(link);
                });
            } else {
                filesCount.textContent = 'Yo\'q';
            }
        });
    }
});
</script>
@endsection
