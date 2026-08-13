@extends('template')

@section('style')
<style>
    .workflow-page{min-height:calc(100vh - 80px);padding:24px;background:#f4f7fb;color:#172033}
    .workflow-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:18px}
    .workflow-head h1{margin:0;color:#102a56;font-size:28px;font-weight:800}
    .workflow-head p{margin:6px 0 0;color:#64748b}
    .metric-grid{display:grid;grid-template-columns:repeat(5,minmax(140px,1fr));gap:12px;margin-bottom:16px}
    .metric,.panel,.worker-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.05)}
    .metric{padding:15px}.metric small{display:block;color:#64748b;font-size:11px;text-transform:uppercase;font-weight:800}.metric strong{display:block;margin-top:7px;font-size:24px;color:#0f172a}
    .toolbar{display:flex;gap:9px;flex-wrap:wrap;align-items:end;padding:14px;margin-bottom:16px}.toolbar label{display:block;color:#64748b;font-size:11px;font-weight:800;margin-bottom:4px}.toolbar input,.toolbar select{height:39px;min-width:150px;border:1px solid #cbd5e1;border-radius:8px;padding:0 9px}.toolbar button{height:39px}
    .board-wrap{overflow-x:auto;padding-bottom:8px}.board{display:grid;grid-template-columns:repeat(6,minmax(260px,1fr));gap:12px;min-width:1720px}.column{background:#e8eef7;border-radius:12px;padding:9px;min-height:390px}.column-head{display:flex;justify-content:space-between;align-items:center;padding:4px 4px 9px}.column-head strong{font-size:13px;color:#102a56}.column-count{min-width:25px;text-align:center;border-radius:999px;background:#fff;padding:3px 7px;font-size:11px;font-weight:800;color:#475569}.column-body{display:grid;gap:9px;min-height:330px}
    .document-card{background:#fff;border:1px solid #dbe4ef;border-radius:11px;padding:11px;cursor:pointer;box-shadow:0 5px 15px rgba(15,23,42,.06);transition:transform .15s,box-shadow .15s}.document-card:hover{transform:translateY(-2px);box-shadow:0 9px 20px rgba(15,23,42,.11)}
    .card-top{display:flex;justify-content:space-between;gap:7px;align-items:flex-start}.doc-code{font-weight:800;color:#1d4ed8;font-size:12px}.priority{border-radius:999px;padding:3px 6px;font-size:10px;font-weight:800}.priority-urgent{background:#fee2e2;color:#b91c1c}.priority-high{background:#ffedd5;color:#c2410c}.priority-normal{background:#e0f2fe;color:#0369a1}.priority-low{background:#f1f5f9;color:#64748b}.status-mini{display:inline-block;margin-top:6px;border-radius:999px;background:#eff6ff;color:#1d4ed8;padding:3px 6px;font-size:10px;font-weight:800}
    .card-title{font-weight:800;margin-top:8px;font-size:13px;color:#172033}.card-meta{margin-top:6px;display:grid;gap:3px;color:#64748b;font-size:11px}.card-footer{display:flex;justify-content:space-between;gap:8px;border-top:1px solid #eef2f7;margin-top:9px;padding-top:8px;color:#64748b;font-size:10px}
    .avatar{width:24px;height:24px;border-radius:50%;background:#dbeafe;color:#1d4ed8;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:10px}.workers{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px}.worker-card{padding:12px}.worker-card .worker-name{font-weight:800;color:#102a56}.worker-stats{display:flex;gap:12px;margin-top:9px;color:#64748b;font-size:11px}.worker-stats strong{display:block;color:#172033;font-size:15px}
    .empty{border:1px dashed #cbd5e1;border-radius:9px;color:#94a3b8;text-align:center;padding:22px 8px;font-size:11px}.modal-content{border:0;border-radius:15px}.history-row{display:grid;grid-template-columns:10px 1fr;gap:10px;padding:9px 0;border-bottom:1px solid #eef2f7}.history-dot{width:9px;height:9px;background:#2563eb;border-radius:50%;margin-top:5px}.history-row strong{font-size:12px}.history-row small{display:block;color:#64748b;font-size:10px}.history-row p{font-size:11px;margin:3px 0 0;color:#475569}
    @media(max-width:900px){.workflow-page{padding:15px}.workflow-head{flex-direction:column}.metric-grid{grid-template-columns:repeat(2,1fr)}}
</style>
@endsection

@section('body')
<div class="workflow-page" id="workflowApp"
     data-data-url="{{ route('documents.workflow.data') }}"
     data-history-template="{{ route('documents.workflow.history', ['document' => '__DOCUMENT__']) }}"
     data-status-template="{{ route('documents.workflow.transition', ['document' => '__DOCUMENT__']) }}"
     data-assignment-template="{{ route('documents.workflow.assign', ['document' => '__DOCUMENT__']) }}"
     data-checklist-template="{{ route('documents.workflow.checklist', ['document' => '__DOCUMENT__', 'checklist' => '__CHECKLIST__']) }}"
     data-qa-template="{{ route('documents.workflow.qa-review', ['document' => '__DOCUMENT__']) }}">
    <div class="workflow-head">
        <div><h1>Ishlar Kanban</h1><p>Har bir hujjatning real holati, mas’uli, QA va yuklamasi bir ekranda.</p></div>
        <div class="d-flex gap-2"><button class="btn btn-outline-primary" id="refreshBoard"><i class="bx bx-refresh"></i> Yangilash</button><a class="btn btn-light" href="{{ route('orders.index') }}">Buyurtmalar</a></div>
    </div>

    <div class="metric-grid" id="metrics"><div class="metric"><small>Jami</small><strong>—</strong></div><div class="metric"><small>Faol</small><strong>—</strong></div><div class="metric"><small>Shoshilinch</small><strong>—</strong></div><div class="metric"><small>Review / correction</small><strong>—</strong></div><div class="metric"><small>Workload</small><strong>—</strong></div></div>

    <form class="panel toolbar" id="filters">
        @if($filials->isNotEmpty())<div><label>Filial</label><select name="filial_id"><option value="">Barcha filial</option>@foreach($filials as $filial)<option value="{{ $filial->id }}" @selected(($filters['filial_id'] ?? '') == $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>@endif
        <div><label>Qidirish</label><input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kod, mijoz yoki xizmat"></div>
        <div><label>Status</label><select name="status"><option value="">Barchasi</option></select></div>
        <div><label>Priority</label><select name="priority"><option value="">Barchasi</option></select></div>
        <div><label>Queue</label><select name="queue"><option value="">Barchasi</option></select></div>
        <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Filtrlash</button><button type="button" id="resetFilters" class="btn btn-light">Tozalash</button>
    </form>

    <div class="panel p-2 mb-3"><div class="p-2"><h5 class="mb-1">Xodimlar yuklamasi</h5><small class="text-muted">Kimda nechta ish va qancha taxminiy vaqt borligi.</small></div><div class="workers p-2" id="workers"></div></div>
    <div class="board-wrap"><div class="board" id="board"></div></div>
</div>

<div class="modal fade" id="workflowModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title" id="modalTitle">Hujjat</h5><small class="text-muted" id="modalSubtitle"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3"><div class="col-md-6"><h6>Statusni o‘zgartirish</h6><form id="statusForm"><select name="status" id="modalStatus" class="form-select mb-2"></select><input name="reason" class="form-control mb-2" placeholder="Sabab (ixtiyoriy)"><textarea name="comment" class="form-control mb-2" rows="2" placeholder="Izoh"></textarea><button class="btn btn-primary">Statusni saqlash</button></form></div><div class="col-md-6"><h6>Mas’ul va QA</h6><form id="assignmentForm"><select name="assigned_to_id" id="modalAssignee" class="form-select mb-2"><option value="">Mas’ul tanlanmagan</option></select><select name="qa_user_id" id="modalQa" class="form-select mb-2"><option value="">QA tanlanmagan</option></select><div class="row g-2"><div class="col-6"><select name="priority" id="modalPriority" class="form-select"></select></div><div class="col-6"><input name="estimated_workload_minutes" id="modalWorkload" class="form-control" type="number" min="0" placeholder="Daqiqa"></div></div><select name="queue" id="modalQueue" class="form-select my-2"></select><textarea name="notes" class="form-control mb-2" rows="2" placeholder="Taqsimlash izohi"></textarea><button class="btn btn-outline-primary">Biriktirish</button><button type="button" class="btn btn-outline-dark ms-1" id="autoAssign">Auto assign</button></form></div></div><hr><h6>Hujjat status tarixi</h6><div id="historyList"></div></div></div></div></div>
@endsection

@section('script_include_end_body')
<script>
(function () {
    var app = document.getElementById('workflowApp');
    var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
    var state = {data: null, document: null};
    document.querySelector('#workflowModal .modal-body').insertAdjacentHTML('afterbegin', '<div class="row g-3 mb-3" id="checklistQaPanel"><div class="col-md-7"><div class="d-flex justify-content-between align-items-center"><h6 class="mb-2">Majburiy checklist</h6><span class="badge bg-light text-dark" id="checklistProgress">—</span></div><div id="checklistList"></div></div><div class="col-md-5"><h6>QA nazorati</h6><div id="qaState" class="small text-muted mb-2"></div><form id="qaForm"><select name="result" class="form-select mb-2"><option value="passed">QA tasdiqladi</option><option value="failed">QA qaytardi</option></select><input name="reason" class="form-control mb-2" placeholder="QA sababi"><textarea name="comment" class="form-control mb-2" rows="2" placeholder="QA izohi"></textarea><button class="btn btn-outline-success w-100">QA natijasini saqlash</button></form></div></div>');
    var statuses = {draft:'Qoralama',waiting_documents:'Hujjatlar kutilmoqda',received:'Qabul qilindi',priced:'Narx belgilandi',awaiting_payment:'To‘lov kutilmoqda',partially_paid:'Qisman to‘langan',in_processing:'Jarayonda',waiting_review:'Tekshiruv kutilmoqda',qa_failed:'Tuzatish kerak',ready_for_delivery:'Topshirishga tayyor',courier_sent:'Kuryerga berildi',delivered:'Yetkazildi',completed:'Yakunlandi',cancelled:'Bekor qilindi',refunded:'Qaytarildi'};
    var priorities = {urgent:'Shoshilinch',high:'Yuqori',normal:'Oddiy',low:'Past'};
    var queues = {intake:'Qabul',pricing:'Narxlash',processing:'Bajarish',review:'Review',correction:'Tuzatish',delivery:'Yetkazish',general:'Umumiy',completed:'Tugallangan',cancelled:'Bekor qilingan'};
    var stageGroups = [
        {label:'Qabul', statuses:['draft','waiting_documents','received']},
        {label:'Narxlash va to‘lov', statuses:['priced','awaiting_payment','partially_paid']},
        {label:'Bajarish', statuses:['in_processing']},
        {label:'Review va tuzatish', statuses:['waiting_review','qa_failed']},
        {label:'Tayyorlash va yetkazish', statuses:['ready_for_delivery','courier_sent','delivered']},
        {label:'Yakunlangan', statuses:['completed','cancelled','refunded']}
    ];

    function escapeHtml(value) { return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (character) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]; }); }
    function query() { return new URLSearchParams(new FormData(document.getElementById('filters'))).toString(); }
    function minutes(value) { var number = Number(value || 0); return number >= 60 ? Math.floor(number / 60) + ' soat ' + (number % 60 ? number % 60 + ' daq.' : '') : number + ' daq.'; }
    function fillFilters(data) {
        [['status', data.statuses, statuses], ['priority', data.priorities, priorities], ['queue', data.queues, queues]].forEach(function (item) {
            var select = document.querySelector('#filters [name="' + item[0] + '"]');
            var current = select.value;
            select.innerHTML = '<option value="">Barchasi</option>' + item[1].map(function (option) { return '<option value="' + escapeHtml(option.key) + '">' + escapeHtml(item[2][option.key] || option.label) + '</option>'; }).join('');
            select.value = current;
        });
    }
    function renderMetrics(metrics) { document.getElementById('metrics').innerHTML = [['Jami',metrics.total],['Faol',metrics.active],['Shoshilinch',metrics.urgent],['Review / correction',metrics.review],['Workload',minutes(metrics.workload_minutes)]].map(function (item) { return '<div class="metric"><small>' + item[0] + '</small><strong>' + item[1] + '</strong></div>'; }).join(''); }
    function renderWorkers(workers) { document.getElementById('workers').innerHTML = workers.length ? workers.map(function (worker) { return '<div class="worker-card"><div class="d-flex justify-content-between gap-2"><span class="worker-name">' + escapeHtml(worker.name) + '</span><span class="avatar">' + escapeHtml(worker.name.slice(0,1).toUpperCase()) + '</span></div><div class="worker-stats"><div><strong>' + worker.active_count + '</strong> faol</div><div><strong>' + minutes(worker.workload_minutes) + '</strong> vaqt</div><div><strong>' + worker.review_count + '</strong> QA</div></div></div>'; }).join('') : '<div class="empty">Xodimlar topilmadi.</div>'; }
    function card(item) { return '<article class="document-card" data-document-id="' + item.id + '"><div class="card-top"><span class="doc-code">' + escapeHtml(item.document_code || 'DOC-' + item.id) + '</span><span class="priority priority-' + escapeHtml(item.priority) + '">' + escapeHtml(priorities[item.priority] || item.priority) + '</span></div><span class="status-mini">' + escapeHtml(item.status_label || item.status) + '</span><div class="card-title">' + escapeHtml(item.client) + '</div><div class="card-meta"><span><i class="bx bx-briefcase"></i> ' + escapeHtml(item.service) + '</span><span><i class="bx bx-user"></i> ' + escapeHtml(item.assigned_to || 'Mas’ul tanlanmagan') + '</span><span><i class="bx bx-check-shield"></i> QA: ' + escapeHtml(item.qa_user || 'Tanlanmagan') + '</span><span><i class="bx bx-time-five"></i> ' + minutes(item.workload_minutes) + ' · ' + escapeHtml(item.deadline || 'Deadline yo‘q') + '</span></div><div class="card-footer"><span>' + escapeHtml(queues[item.queue] || item.queue) + '</span><span>' + (item.rework_count ? '↻ ' + item.rework_count + ' qayta' : '') + '</span></div></article>'; }
    function renderBoard(columns) {
        var byStatus = {};
        columns.forEach(function (column) { byStatus[column.key] = column; });
        var grouped = stageGroups.map(function (stage) {
            var documents = [];
            stage.statuses.forEach(function (status) { if (byStatus[status]) documents = documents.concat(byStatus[status].documents); });
            return {label:stage.label, count:documents.length, documents:documents};
        });
        document.getElementById('board').innerHTML = grouped.map(function (column) { return '<section class="column"><div class="column-head"><strong>' + escapeHtml(column.label) + '</strong><span class="column-count">' + column.count + '</span></div><div class="column-body">' + (column.documents.length ? column.documents.map(card).join('') : '<div class="empty">Bu bosqich bo‘sh</div>') + '</div></section>'; }).join('');
        document.querySelectorAll('.document-card').forEach(function (item) { item.addEventListener('click', function () { openDocument(Number(item.dataset.documentId)); }); });
    }
    function load() { return fetch(app.dataset.dataUrl + '?' + query(), {headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}).then(function (response) { return response.json(); }).then(function (result) { state.data = result.data; fillFilters(state.data); renderMetrics(state.data.metrics); renderWorkers(state.data.workers); renderBoard(state.data.columns); }); }
    function setOptions() {
        var data = state.data;
        document.getElementById('modalStatus').innerHTML = data.statuses.map(function (item) { return '<option value="' + item.key + '">' + escapeHtml(item.label) + '</option>'; }).join('');
        document.getElementById('modalPriority').innerHTML = data.priorities.map(function (item) { return '<option value="' + item.key + '">' + escapeHtml(priorities[item.key] || item.label) + '</option>'; }).join('');
        document.getElementById('modalQueue').innerHTML = data.queues.map(function (item) { return '<option value="' + item.key + '">' + escapeHtml(queues[item.key] || item.label) + '</option>'; }).join('');
        ['modalAssignee','modalQa'].forEach(function (id) { document.getElementById(id).innerHTML = '<option value="">Tanlanmagan</option>' + data.workers.map(function (worker) { return '<option value="' + worker.id + '">' + escapeHtml(worker.name) + ' · ' + worker.active_count + ' ish</option>'; }).join(''); });
    }
    function openDocument(id) {
        var item = state.data.columns.reduce(function (all, column) { return all.concat(column.documents); }, []).find(function (document) { return document.id === id; });
        if (!item) return;
        state.document = item;
        setOptions();
        document.getElementById('modalTitle').textContent = item.document_code || 'DOC-' + id;
        document.getElementById('modalSubtitle').textContent = item.client + ' · ' + item.service;
        document.getElementById('modalStatus').innerHTML = (item.allowed_transitions || [{key:item.status,label:item.status_label}]).map(function (option) { return '<option value="' + option.key + '">' + escapeHtml(option.label) + '</option>'; }).join('');
        document.getElementById('modalStatus').value = item.status;
        document.getElementById('modalPriority').value = item.priority;
        document.getElementById('modalQueue').value = item.queue;
        document.getElementById('modalAssignee').value = item.assigned_to_id || '';
        document.getElementById('modalQa').value = item.qa_user_id || '';
        document.getElementById('modalWorkload').value = item.workload_minutes || '';
        loadHistory(id);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('workflowModal')).show();
    }
    function loadHistory(id) { return fetch(app.dataset.historyTemplate.replace('__DOCUMENT__', id), {headers:{Accept:'application/json'}}).then(function (response) { return response.json(); }).then(function (result) { var items = result.data.status_history || []; document.getElementById('historyList').innerHTML = items.length ? items.map(function (item) { return '<div class="history-row"><span class="history-dot"></span><div><strong>' + escapeHtml(item.from_label || 'Boshlanish') + ' → ' + escapeHtml(item.to_label) + '</strong><small>' + escapeHtml(item.changed_by) + ' · ' + escapeHtml(item.created_at) + '</small>' + (item.reason ? '<p>' + escapeHtml(item.reason) + '</p>' : '') + (item.comment ? '<p>' + escapeHtml(item.comment) + '</p>' : '') + '</div></div>'; }).join('') : '<div class="empty">Status tarixi yo‘q.</div>'; }); }
    function submitForm(form, url, extra) {
        var body = new FormData(form);
        Object.keys(extra || {}).forEach(function (key) { body.set(key, extra[key]); });
        return fetch(url, {method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},body:body}).then(function (response) { return response.json().then(function (result) { if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join(' ') || 'Saqlashda xatolik.'); return result; }); }).then(function (result) { return load().then(function () { return result; }); });
    }
    document.getElementById('filters').addEventListener('submit', function (event) { event.preventDefault(); load(); });
    document.getElementById('resetFilters').addEventListener('click', function () { document.getElementById('filters').reset(); load(); });
    document.getElementById('refreshBoard').addEventListener('click', load);
    document.getElementById('statusForm').addEventListener('submit', function (event) { event.preventDefault(); submitForm(event.currentTarget, app.dataset.statusTemplate.replace('__DOCUMENT__', state.document.id)).then(function () { bootstrap.Modal.getOrCreateInstance(document.getElementById('workflowModal')).hide(); }).catch(function (error) { alert(error.message); }); });
    document.getElementById('assignmentForm').addEventListener('submit', function (event) { event.preventDefault(); submitForm(event.currentTarget, app.dataset.assignmentTemplate.replace('__DOCUMENT__', state.document.id)).then(function () { bootstrap.Modal.getOrCreateInstance(document.getElementById('workflowModal')).hide(); }).catch(function (error) { alert(error.message); }); });
    document.getElementById('autoAssign').addEventListener('click', function () { submitForm(document.getElementById('assignmentForm'), app.dataset.assignmentTemplate.replace('__DOCUMENT__', state.document.id), {auto_assign:1}).then(function () { bootstrap.Modal.getOrCreateInstance(document.getElementById('workflowModal')).hide(); }).catch(function (error) { alert(error.message); }); });
    load().catch(function (error) { document.getElementById('board').innerHTML = '<div class="empty">' + escapeHtml(error.message) + '</div>'; });
}());
    function renderChecklist(checklist, qa) {
        var items = checklist && checklist.items ? checklist.items : [];
        var requiredTotal = checklist ? checklist.total : 0;
        var completed = checklist ? checklist.completed : 0;
        document.getElementById('checklistProgress').textContent = requiredTotal ? completed + '/' + requiredTotal : 'Talab yo‘q';
        document.getElementById('checklistList').innerHTML = items.length ? items.map(function (item) {
            return '<label class="d-flex align-items-start gap-2 border rounded p-2 mb-2 ' + (item.is_completed ? 'bg-success-subtle' : '') + '"><input type="checkbox" data-checklist-id="' + item.id + '" ' + (item.is_completed ? 'checked' : '') + '><span><strong>' + escapeHtml(item.title) + '</strong><small class="d-block text-muted">' + (item.is_required ? 'Majburiy' : 'Ixtiyoriy') + (item.requires_file ? ' · fayl kerak' : ' · manual tekshiruv') + '</small>' + (item.notes ? '<small class="d-block">' + escapeHtml(item.notes) + '</small>' : '') + '</span></label>';
        }).join('') : '<div class="empty">Bu xizmat uchun checklist talabi sozlanmagan.</div>';
        document.querySelectorAll('#checklistList [data-checklist-id]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var body = new FormData();
                body.set('is_completed', checkbox.checked ? '1' : '0');
                fetch(app.dataset.checklistTemplate.replace('__DOCUMENT__', state.document.id).replace('__CHECKLIST__', checkbox.dataset.checklistId), {method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},body:body}).then(function (response) {
                    return response.json().then(function (result) { if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join(' ') || 'Checklist saqlanmadi.'); return result; });
                }).then(function () { return load().then(function () { return loadChecklistHistory(state.document.id); }); }).catch(function (error) { checkbox.checked = !checkbox.checked; alert(error.message); });
            });
        });
        var qaLabels = {not_required:'QA talab qilinmagan',not_started:'QA kutilmoqda',pending:'QA qayta tekshiruvi kutilmoqda',passed:'QA tasdiqlandi',failed:'QA qaytardi'};
        document.getElementById('qaState').textContent = 'Holat: ' + (qaLabels[qa && qa.status] || 'Noma’lum') + (qa && qa.reviewer ? ' · ' + qa.reviewer : '');
        document.querySelector('#qaForm [name="result"]').value = qa && qa.status === 'failed' ? 'failed' : 'passed';
    }
    function loadChecklistHistory(id) { return fetch(app.dataset.historyTemplate.replace('__DOCUMENT__', id), {headers:{Accept:'application/json'}}).then(function (response) { return response.json(); }).then(function (result) { renderChecklist(result.data.checklist, result.data.qa); }); }
    document.getElementById('workflowModal').addEventListener('shown.bs.modal', function () { if (state.document) loadChecklistHistory(state.document.id); });
    document.getElementById('qaForm').addEventListener('submit', function (event) { event.preventDefault(); submitForm(event.currentTarget, app.dataset.qaTemplate.replace('__DOCUMENT__', state.document.id)).then(function () { return loadChecklistHistory(state.document.id); }).catch(function (error) { alert(error.message); }); });
</script>
@endsection
