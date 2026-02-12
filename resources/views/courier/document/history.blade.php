@extends('template')

@section('style')
<style>
.page-wrapper { padding: 24px; }
.card { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.card-header { background: #0f172a; color: #fff; font-weight: 600; }
.badge { padding: 6px 10px; border-radius: 10px; font-size: 12px; }
.badge-rejected { background: #ef4444; color: #fff; }
.badge-returned { background: #22c55e; color: #fff; }
.file-list { display: grid; gap: 6px; }
.file-link { font-size: 12px; color: #1e3a8a; text-decoration: none; }
.file-link:hover { text-decoration: underline; }
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
            <div class="card-header">Courier - Hujjat Tarixi</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Document Code</th>
                                <th>Mijoz</th>
                                <th>Xizmat</th>
                                <th>Status</th>
                                <th>Izoh</th>
                                <th>Vaqt</th>
                                <th>Fayllar</th>
                                <th>Batafsil</th>
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
                                    data-status="{{ $assignment->status }}"
                                    data-comment="{{ $assignment->courier_comment ?? $assignment->return_comment ?? '-' }}"
                                    data-time="{{ $assignment->status === 'rejected' ? (optional($assignment->rejected_at)->format('d.m.Y H:i') ?? '-') : (optional($assignment->returned_at)->format('d.m.Y H:i') ?? '-') }}"
                                    data-files='@json($filesData)'>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $doc->document_code ?? '-' }}</td>
                                    <td>{{ $doc->client->name ?? '-' }}</td>
                                    <td>{{ $doc->service->name ?? '-' }}</td>
                                    <td>
                                        @if($assignment->status === 'rejected')
                                            <span class="badge badge-rejected">Rejected</span>
                                        @elseif($assignment->status === 'returned')
                                            <span class="badge badge-returned">Returned</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $assignment->courier_comment ?? $assignment->return_comment ?? '-' }}
                                    </td>
                                    <td>
                                        @if($assignment->status === 'rejected')
                                            {{ optional($assignment->rejected_at)->format('d.m.Y H:i') ?? '-' }}
                                        @elseif($assignment->status === 'returned')
                                            {{ optional($assignment->returned_at)->format('d.m.Y H:i') ?? '-' }}
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
                                                data-bs-target="#historyDocDetailsOffcanvas"
                                                aria-controls="historyDocDetailsOffcanvas">
                                                Batafsil
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tarix mavjud emas</td>
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
<div class="offcanvas offcanvas-end" tabindex="-1" id="historyDocDetailsOffcanvas" aria-labelledby="historyDocDetailsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="historyDocDetailsLabel">Hujjat batafsil ma'lumot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="oc-section">
            <div class="oc-title">Asosiy</div>
            <div class="oc-value">Code: <span id="hocDocumentCode">-</span></div>
            <span class="oc-sub">Mijoz: <span id="hocClient">-</span></span>
            <span class="oc-sub">Xizmat: <span id="hocService">-</span></span>
        </div>
        <div class="oc-section">
            <div class="oc-title">Status</div>
            <div class="oc-value">Holat: <span id="hocStatus">-</span></div>
            <span class="oc-sub">Vaqt: <span id="hocTime">-</span></span>
            <span class="oc-sub">Izoh: <span id="hocComment">-</span></span>
        </div>
        <div class="oc-section">
            <div class="oc-title">Fayllar</div>
            <div class="oc-value"><span id="hocFilesCount">-</span></div>
            <div class="oc-files" id="hocFiles"></div>
        </div>
    </div>
</div>
@endsection

@section('script_include_end_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('table');
    if (!table) return;

    table.addEventListener('click', function (e) {
        const btn = e.target.closest('.details-toggle');
        if (!btn) return;
        const row = btn.closest('tr.doc-row');
        if (!row) return;

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value ?? '-';
        };

        setText('hocDocumentCode', row.dataset.documentCode || '-');
        setText('hocClient', row.dataset.client || '-');
        setText('hocService', row.dataset.service || '-');
        setText('hocStatus', row.dataset.status || '-');
        setText('hocTime', row.dataset.time || '-');
        setText('hocComment', row.dataset.comment || '-');

        const filesWrap = document.getElementById('hocFiles');
        const filesCount = document.getElementById('hocFilesCount');
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
});
</script>
@endsection
