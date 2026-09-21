@extends('template')

@section('style')
<style>
    .workflow-page{min-height:calc(100vh - 80px);padding:24px;background:#f4f7fb;color:#172033}
    .workflow-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:18px}.workflow-head h1{margin:0;color:#102a56;font-size:28px;font-weight:800}.workflow-head p{margin:6px 0 0;color:#64748b}
    .monitor-note{display:flex;gap:12px;align-items:flex-start;padding:14px 16px;margin-bottom:16px;border:1px solid #bfdbfe;border-radius:14px;background:#eff6ff;color:#1e3a5f}.monitor-note i{font-size:21px;color:#2563eb}.monitor-note strong{display:block;font-size:13px}.monitor-note span{display:block;margin-top:3px;font-size:12px;color:#475569}
    .metric-grid{display:grid;grid-template-columns:repeat(5,minmax(140px,1fr));gap:12px;margin-bottom:16px}.metric,.panel,.worker-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.05)}.metric{padding:15px}.metric small{display:block;color:#64748b;font-size:11px;text-transform:uppercase;font-weight:800}.metric strong{display:block;margin-top:7px;font-size:24px;color:#0f172a}
    .toolbar{display:flex;gap:9px;flex-wrap:wrap;align-items:end;padding:14px;margin-bottom:16px}.toolbar label{display:block;color:#64748b;font-size:11px;font-weight:800;margin-bottom:4px}.toolbar input,.toolbar select{height:39px;min-width:150px;border:1px solid #cbd5e1;border-radius:8px;padding:0 9px}.toolbar button{height:39px}
    .board-wrap{overflow-x:auto;padding-bottom:8px}.board{display:grid;grid-template-columns:repeat(6,minmax(260px,1fr));gap:12px;min-width:1720px}.column{background:#e8eef7;border-radius:12px;padding:9px;min-height:390px}.column-head{display:flex;justify-content:space-between;align-items:center;padding:4px 4px 9px}.column-head strong{font-size:13px;color:#102a56}.column-count{min-width:25px;text-align:center;border-radius:999px;background:#fff;padding:3px 7px;font-size:11px;font-weight:800;color:#475569}.column-body{display:grid;gap:9px;min-height:330px}
    .document-card{background:#fff;border:1px solid #dbe4ef;border-radius:11px;padding:11px;box-shadow:0 5px 15px rgba(15,23,42,.06)}.card-top{display:flex;justify-content:space-between;gap:7px;align-items:flex-start}.doc-code{font-weight:800;color:#1d4ed8;font-size:12px}.priority{border-radius:999px;padding:3px 6px;font-size:10px;font-weight:800}.priority-urgent{background:#fee2e2;color:#b91c1c}.priority-high{background:#ffedd5;color:#c2410c}.priority-normal{background:#e0f2fe;color:#0369a1}.priority-low{background:#f1f5f9;color:#64748b}.status-mini{display:inline-block;margin-top:6px;border-radius:999px;background:#eff6ff;color:#1d4ed8;padding:3px 6px;font-size:10px;font-weight:800}.card-title{font-weight:800;margin-top:8px;font-size:13px;color:#172033}.card-meta{margin-top:6px;display:grid;gap:3px;color:#64748b;font-size:11px}.card-footer{display:flex;justify-content:space-between;gap:8px;border-top:1px solid #eef2f7;margin-top:9px;padding-top:8px;color:#64748b;font-size:10px}
    .workload-panel{padding:20px;margin-bottom:18px;overflow:hidden}.workload-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding-bottom:16px;border-bottom:1px solid #e8eef6}.workload-kicker{display:inline-flex;align-items:center;gap:6px;color:#2563eb;font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase}.workload-kicker i{font-size:15px}.workload-title{margin:5px 0 4px;color:#102a56;font-size:19px;font-weight:800}.workload-description{margin:0;max-width:740px;color:#64748b;font-size:12px;line-height:1.55}.monitoring-chip{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;padding:7px 10px;border:1px solid #bbf7d0;border-radius:999px;background:#f0fdf4;color:#15803d;font-size:11px;font-weight:800}.workload-legend{display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:13px 0 4px;color:#64748b;font-size:11px}.workload-legend span{display:inline-flex;align-items:center;gap:5px}.workload-legend i{color:#2563eb;font-size:15px}.workers{display:grid;grid-template-columns:repeat(auto-fit,minmax(245px,1fr));gap:14px;padding-top:10px}.worker-card{min-height:160px;padding:16px;border:1px solid #dbe7f5;border-radius:15px;background:linear-gradient(145deg,#fff 0%,#f8fbff 100%);box-shadow:0 8px 22px rgba(30,64,175,.06)}.worker-card__header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.worker-person{display:flex;align-items:center;gap:10px;min-width:0}.avatar{width:38px;height:38px;flex:0 0 38px;border-radius:12px;background:linear-gradient(145deg,#dbeafe,#eff6ff);color:#1d4ed8;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:13px}.worker-card .worker-name{display:block;color:#102a56;font-size:14px;font-weight:800;line-height:1.2}.worker-filial{display:inline-flex;align-items:center;gap:4px;margin-top:4px;color:#64748b;font-size:11px}.worker-filial i{color:#3b82f6;font-size:13px}.worker-status{padding:4px 7px;border-radius:999px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:800}.worker-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:16px}.worker-stat{min-width:0;padding:9px 8px;border-radius:10px;background:#f8fafc}.worker-stat i{display:block;margin-bottom:5px;font-size:15px}.worker-stat strong{display:block;color:#172033;font-size:16px;font-weight:800;line-height:1.05;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.worker-stat span{display:block;margin-top:4px;color:#64748b;font-size:10px;line-height:1.15}.worker-stat--active i{color:#2563eb}.worker-stat--time i{color:#7c3aed}.worker-stat--qa i{color:#d97706}.empty{grid-column:1 / -1;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;text-align:center;padding:28px 12px;font-size:12px}
    @media(max-width:1200px){.workflow-page{padding:18px}.metric-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:900px){.workflow-page{padding:15px}.workflow-head{flex-direction:column}.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:560px){.metric-grid{grid-template-columns:1fr}.toolbar{align-items:stretch}.toolbar>*,.toolbar input,.toolbar select,.toolbar button{width:100%;min-width:0}.workload-head{flex-direction:column}.worker-stats{gap:6px}.worker-card{padding:14px}}
</style>
@endsection

@section('body')
<div class="page-wrapper">
<div class="workflow-page" id="workflowApp" data-data-url="{{ route('documents.workflow.data') }}">
    <div class="workflow-head">
        <div><h1>Kanban doska</h1><p>Filiallar hujjat oqimini super_admin uchun umumiy monitoring ko'rinishi.</p></div>
        <button class="btn btn-outline-primary" id="refreshBoard"><i class="bx bx-refresh"></i> Yangilash</button>
    </div>

    <div class="monitor-note"><i class="bx bx-show"></i><div><strong>Faqat kuzatuv rejimi</strong><span>Bu doska ish taqsimlash vositasi emas. Xodimga ish berish, ishni boshqa xodimga o'tkazish va ichki jarayonlar filialning o'z boshqaruvida qoladi.</span></div></div>

    <div class="metric-grid" id="metrics"><div class="metric"><small>Jami</small><strong>-</strong></div><div class="metric"><small>Faol</small><strong>-</strong></div><div class="metric"><small>Muhim</small><strong>-</strong></div><div class="metric"><small>Review / tuzatish</small><strong>-</strong></div><div class="metric"><small>Rejalashtirilgan yuklama</small><strong>-</strong></div></div>

    <form class="panel toolbar" id="filters">
        @if($filials->isNotEmpty())<div><label>Filial</label><select name="filial_id"><option value="">Barcha filial</option>@foreach($filials as $filial)<option value="{{ $filial->id }}" @selected(($filters['filial_id'] ?? '') == $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>@endif
        <div><label>Qidirish</label><input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kod, mijoz yoki xizmat"></div>
        <div><label>Status</label><select name="status"><option value="">Barchasi</option></select></div>
        <div><label>Priority</label><select name="priority"><option value="">Barchasi</option></select></div>
        <div><label>Queue</label><select name="queue"><option value="">Barchasi</option></select></div>
        <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Filtrlash</button><button type="button" id="resetFilters" class="btn btn-light">Tozalash</button>
    </form>

    <section class="panel workload-panel" aria-labelledby="workloadTitle">
        <div class="workload-head">
            <div>
                <span class="workload-kicker"><i class="bx bx-bar-chart-alt-2"></i> Filiallar monitoringi</span>
                <h2 class="workload-title" id="workloadTitle">Jamoa yuklamasi</h2>
                <p class="workload-description">Har bir xodim bo‘yicha faol ishlar soni, rejalashtirilgan vaqt va QA / reviewga tushgan ishlar ko‘rsatiladi. Bu bo‘lim faqat kuzatuv uchun.</p>
            </div>
            <span class="monitoring-chip"><i class="bx bx-show"></i> Faqat kuzatuv</span>
        </div>
        <div class="workload-legend" aria-label="Ko‘rsatkichlar izohi">
            <span><i class="bx bx-briefcase"></i> Faol ishlar — ayni paytda bajarilayotgan ishlar</span>
            <span><i class="bx bx-time-five"></i> Reja vaqti — belgilangan umumiy muddat</span>
            <span><i class="bx bx-check-shield"></i> QA / review — tekshiruvga kelgan ishlar</span>
        </div>
        <div class="workers" id="workers"></div>
    </section>
    <div class="board-wrap"><div class="board" id="board"></div></div>
</div>
</div>
@endsection

@section('script_include_end_body')
<script>
(function () {
    var app = document.getElementById('workflowApp');
    var statuses = {draft:'Qoralama',waiting_documents:'Hujjatlar kutilmoqda',received:'Qabul qilindi',priced:'Narx belgilandi',awaiting_payment:"To'lov kutilmoqda",partially_paid:"Qisman to'langan",in_processing:'Jarayonda',waiting_review:'Tekshiruv kutilmoqda',qa_failed:'Tuzatish kerak',ready_for_delivery:'Topshirishga tayyor',courier_sent:'Kuryerga berildi',delivered:'Yetkazildi',completed:'Yakunlandi',cancelled:'Bekor qilindi',refunded:'Qaytarildi'};
    var priorities = {urgent:'Shoshilinch',high:'Yuqori',normal:'Oddiy',low:'Past'};
    var queues = {intake:'Qabul',pricing:'Narxlash',processing:'Bajarish',review:'Review',correction:'Tuzatish',delivery:'Yetkazish',general:'Umumiy',completed:'Tugallangan',cancelled:'Bekor qilingan'};
    var stageGroups = [{label:'Qabul',statuses:['draft','waiting_documents','received']},{label:"Narxlash va to'lov",statuses:['priced','awaiting_payment','partially_paid']},{label:'Bajarish',statuses:['in_processing']},{label:'Review va tuzatish',statuses:['waiting_review','qa_failed']},{label:'Tayyorlash va yetkazish',statuses:['ready_for_delivery','courier_sent','delivered']},{label:'Yakunlangan',statuses:['completed','cancelled','refunded']}];

    function escapeHtml(value) { return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (character) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]; }); }
    function query() { return new URLSearchParams(new FormData(document.getElementById('filters'))).toString(); }
    function minutes(value) { var number = Number(value || 0); return number >= 60 ? Math.floor(number / 60) + ' soat ' + (number % 60 ? number % 60 + ' daq.' : '') : number + ' daq.'; }
    function fillFilters(data) { [['status',data.statuses,statuses],['priority',data.priorities,priorities],['queue',data.queues,queues]].forEach(function (item) { var select = document.querySelector('#filters [name="' + item[0] + '"]'); var current = select.value; select.innerHTML = '<option value="">Barchasi</option>' + item[1].map(function (option) { return '<option value="' + escapeHtml(option.key) + '">' + escapeHtml(item[2][option.key] || option.label) + '</option>'; }).join(''); select.value = current; }); }
    function renderMetrics(metrics) { document.getElementById('metrics').innerHTML = [['Jami',metrics.total],['Faol',metrics.active],['Muhim',metrics.urgent],['Review / tuzatish',metrics.review],['Rejalashtirilgan yuklama',minutes(metrics.workload_minutes)]].map(function (item) { return '<div class="metric"><small>' + item[0] + '</small><strong>' + item[1] + '</strong></div>'; }).join(''); }
    function renderWorkers(workers) { document.getElementById('workers').innerHTML = workers.length ? workers.map(function (worker) { return '<article class="worker-card"><div class="worker-card__header"><div class="worker-person"><span class="avatar">' + escapeHtml(worker.name.slice(0,1).toUpperCase()) + '</span><div><span class="worker-name">' + escapeHtml(worker.name) + '</span><span class="worker-filial"><i class="bx bx-buildings"></i>' + escapeHtml(worker.filial || 'Filial ko\'rsatilmagan') + '</span></div></div><span class="worker-status">Kuzatuv</span></div><div class="worker-stats"><div class="worker-stat worker-stat--active"><i class="bx bx-briefcase"></i><strong>' + worker.active_count + '</strong><span>Faol ishlar</span></div><div class="worker-stat worker-stat--time"><i class="bx bx-time-five"></i><strong>' + minutes(worker.workload_minutes) + '</strong><span>Reja vaqti</span></div><div class="worker-stat worker-stat--qa"><i class="bx bx-check-shield"></i><strong>' + worker.review_count + '</strong><span>QA / review</span></div></div></article>'; }).join('') : '<div class="empty"><i class="bx bx-group"></i><br>Xodimlar topilmadi.</div>'; }
    function card(item) { return '<article class="document-card"><div class="card-top"><span class="doc-code">' + escapeHtml(item.document_code || 'DOC-' + item.id) + '</span><span class="priority priority-' + escapeHtml(item.priority) + '">' + escapeHtml(priorities[item.priority] || item.priority) + '</span></div><span class="status-mini">' + escapeHtml(item.status_label || item.status) + '</span><div class="card-title">' + escapeHtml(item.client) + '</div><div class="card-meta"><span><i class="bx bx-buildings"></i> ' + escapeHtml(item.filial || 'Filial ko\'rsatilmagan') + '</span><span><i class="bx bx-briefcase"></i> ' + escapeHtml(item.service) + '</span><span><i class="bx bx-user"></i> ' + escapeHtml(item.assigned_to || 'Mas\'ul qayd qilinmagan') + '</span><span><i class="bx bx-check-shield"></i> QA: ' + escapeHtml(item.qa_user || 'Qayd qilinmagan') + '</span><span><i class="bx bx-time-five"></i> ' + minutes(item.workload_minutes) + ' · ' + escapeHtml(item.deadline || 'Deadline yo\'q') + '</span></div><div class="card-footer"><span>' + escapeHtml(queues[item.queue] || item.queue) + '</span><span>' + (item.rework_count ? '↻ ' + item.rework_count + ' qayta' : '') + '</span></div></article>'; }
    function renderBoard(columns) { var byStatus = {}; columns.forEach(function (column) { byStatus[column.key] = column; }); var grouped = stageGroups.map(function (stage) { var documents = []; stage.statuses.forEach(function (status) { if (byStatus[status]) documents = documents.concat(byStatus[status].documents); }); return {label:stage.label,count:documents.length,documents:documents}; }); document.getElementById('board').innerHTML = grouped.map(function (column) { return '<section class="column"><div class="column-head"><strong>' + escapeHtml(column.label) + '</strong><span class="column-count">' + column.count + '</span></div><div class="column-body">' + (column.documents.length ? column.documents.map(card).join('') : '<div class="empty">Bu bosqich bo\'sh</div>') + '</div></section>'; }).join(''); }
    function load() { return fetch(app.dataset.dataUrl + '?' + query(), {headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}).then(function (response) { if (!response.ok) throw new Error('Monitoring ma\'lumotlarini yuklab bo\'lmadi.'); return response.json(); }).then(function (result) { fillFilters(result.data); renderMetrics(result.data.metrics); renderWorkers(result.data.workers); renderBoard(result.data.columns); }); }
    function showError(error) { document.getElementById('board').innerHTML = '<div class="empty">' + escapeHtml(error.message) + '</div>'; }
    document.getElementById('filters').addEventListener('submit', function (event) { event.preventDefault(); load().catch(showError); });
    document.getElementById('resetFilters').addEventListener('click', function () { document.getElementById('filters').reset(); load().catch(showError); });
    document.getElementById('refreshBoard').addEventListener('click', function () { load().catch(showError); });
    load().catch(showError);
}());
</script>
@endsection
