@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');  

:root {
  --text-color: #1e293b;
  --text-light: #64748b;
  --blue-main: #1e3a8a;
  --blue-dark: #1e3a8a;
  --blue-light: #1e3a8a;
  --bg: #f8fafc;
  --white: #ffffff;
  --border: #e2e8f0;
  --danger: #ef4444;
  --success: #22c55e;
  --warning: #facc15;
  --shadow: 0 8px 20px rgba(0,0,0,0.08);
  --radius: 12px;
}

body {
  font-family: "Inter", sans-serif;
  background: var(--bg);
  color: var(--text-color);
  margin: 0;
}

/* Layout */
.page-wrapper { padding: 24px; }
.page-breadcrumb {
  background: var(--white);
  border-radius: 16px;
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow);
  border: 1px solid var(--border);
}
.breadcrumb-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--blue-dark);
}

/* Buttons */
.btn-custom {
  background: linear-gradient(135deg, var(--blue-main), var(--blue-light));
  border: none;
  color: #fff;
  padding: 10px 20px;
  border-radius: var(--radius);
  font-weight: 500;
  transition: 0.3s;
}
.btn-custom:hover {
  opacity: 0.95;
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(59, 130, 246, 0.3);
}
.btn-action {
  border: none;
  border-radius: var(--radius);
  padding: 8px 14px;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.25s ease;
}
.btn-edit { background: var(--success); color: white; }
.btn-edit:disabled { background: #a3a3a3; cursor: not-allowed; }
.btn-delete { background: var(--danger); color: white; }
.action-dropdown .dropdown-menu {
  position: static !important;
  transform: none !important;
  inset: auto !important;
  float: none;
  margin-top: 6px;
  padding: 10px;
  border-radius: 14px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow);
}
.action-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s ease;
}
.action-item.send {
  background: #eef2ff;
  color: #1e3a8a;
}
.action-item.complete {
  background: #fee2e2;
  color: #991b1b;
}
.action-item:hover {
  transform: translateX(2px);
  opacity: 0.95;
}
.action-item.disabled-link {
  opacity: 0.6;
}
/* Cards */
.card {
  background: var(--white);
  border-radius: 20px;
  box-shadow: var(--shadow);
  border: 1px solid var(--border);
  transition: 0.3s;
}
.card:hover { box-shadow: 0 12px 24px rgba(37, 99, 235, 0.15); }
.card-body { padding: 30px; }

/* Table */
.table-responsive { max-height: 600px; overflow-y: auto; border-radius: var(--radius); }
table { width: 100%; border-collapse: collapse; font-size: 14px; border-radius: var(--radius); }
thead { background: var(--blue-main); color: #fff; position: sticky; top: 0; z-index: 2; }
th, td { text-align: center; padding: 14px 12px; }
tbody tr { background: var(--white); transition: background 0.25s ease, transform 0.2s; cursor: pointer; }
tbody tr:hover { background: #eef4ff; transform: translateX(2px); }

/* Badges */
.badge {
  padding: 6px 12px;
  border-radius: var(--radius);
  font-size: 13px;
  font-weight: 500;
}
.bg-success { background-color: var(--success); color: #fff; }
.bg-warning { background-color: var(--warning); color: var(--text-color); }

/* Offcanvas details */
.details-actions {
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: center;
}
.offcanvas {
  width: 420px;
}
.offcanvas-header {
  border-bottom: 1px solid var(--border);
}
.offcanvas-body {
  background: #f8fafc;
}
.oc-section {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px 14px;
  margin-bottom: 12px;
}
.oc-title {
  font-size: 12px;
  color: var(--text-light);
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
  font-size: 12px;
  color: #475569;
  margin-top: 4px;
}
.oc-files {
  display: grid;
  gap: 6px;
  margin-top: 6px;
}
.oc-file-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 8px;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  text-decoration: none;
  font-size: 12px;
}
.oc-file-link:hover {
  background: #eef2ff;
  border-color: #c7d2fe;
}

/* Filter Panel */
.filter-panel {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.filter-panel select, .filter-panel input {
  padding: 8px 12px;
  border-radius: var(--radius);
  border: 1px solid var(--border);
  font-size: 14px;
  transition: all 0.3s;
}
.filter-panel select:focus, .filter-panel input:focus {
  border-color: var(--blue-main);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  outline: none;
}

/* Responsive */
@media (max-width: 768px) {
  .page-wrapper { padding: 16px; }
  .breadcrumb-title { font-size: 18px; }
  .btn-custom { padding: 8px 16px; font-size: 14px; }
  table { font-size: 12px; }
  .card-body { padding: 20px; }
}
.disabled-link {
    pointer-events: none;  /* klik bo'lmaydi */
    opacity: 0.5;          /* ko'rinishi susayadi */
    cursor: not-allowed;
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">

    <!-- Breadcrumb & New Document Button -->
    <div class="page-breadcrumb d-flex justify-content-between mb-4">
      <div class="breadcrumb-title">Hujjatlar ro'yxati</div>
      <a href="{{ route('admin_filial.document.create') }}" class="btn btn-custom">+ Yangi hujjat</a>
    </div>

    <!-- Card with Filter & Table -->
    <div class="card radius-10">
      <div class="card-body">

        <!-- Filter Panel -->
        <div class="filter-panel">
          <input type="text" id="search-client" placeholder="Mijoz bo'yicha qidirish">
          <input type="text" id="search-service" placeholder="Xizmat bo'yicha qidirish">
          <select id="filter-status">
            <option value="">Status bo'yicha</option>
            <option value="yopilgan">Yopilgan</option>
            <option value="jarayonda">Jarayonda</option>
          </select>
          <select id="sort-deadline">
            <option value="">Deadline tartib</option>
            <option value="asc">O'sish</option>
            <option value="desc">Kamayish</option>
          </select>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="documents-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Document Code</th>
                <th>Mijoz</th>
                <th>Xizmat</th>
                <th>Muhlat</th>
                <th>Deadline</th>
                <th>Status moddiy</th>
                <th>Status hujjat</th>
                <th>Courier</th>
                <th>Batafsil</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($documents as $key => $doc)
                @php
                  $filesData = ($doc->files ?? collect())->map(function ($f) {
                      return ['name' => $f->original_name, 'url' => $f->file_url];
                  })->values();
                @endphp
                @php
                  $statusText = $doc->paid_amount >= $doc->final_price ? 'yopilgan' : 'jarayonda';
                  $courierAssignment = $doc->courierAssignment;
                  $courierActive = $courierAssignment && in_array($courierAssignment->status, ['sent', 'accepted']);
                @endphp
                <tr class="doc-row"
                    data-doc-id="{{ $doc->id }}"
                    data-status="{{ $statusText }}"
                    data-client="{{ $doc->client->name ?? '' }}"
                    data-service="{{ $doc->service->name ?? '' }}"
                    data-deadline="{{ $doc->deadline_time }}"
                    data-document-type="{{ $doc->documentType->name ?? '-' }}"
                    data-direction-type="{{ $doc->directionType->name ?? '-' }}"
                    data-consulate-type="{{ $doc->consulateType->name ?? '-' }}"
                    data-process-mode="{{ $doc->process_mode ?? '-' }}"
                    data-final-price="{{ $doc->final_price ?? 0 }}"
                    data-paid-amount="{{ $doc->paid_amount ?? 0 }}"
                    data-discount="{{ $doc->discount ?? 0 }}"
                    data-files='@json($filesData)'>
                  <td>{{ $key+1 }}</td>
                  <td>{{ $doc->document_code ?? '-' }}</td>
                  <td>{{ $doc->client->name ?? '-' }}</td>
                  <td>{{ $doc->service->name ?? '-' }}</td>
                  <td>{{ $doc->deadline_time }}-kun</td>
                  <td>{{ $doc->deadline_remaining }}</td>
                  <td>
                    @if($doc->paid_amount >= $doc->final_price)
                      <span class="badge bg-success">Yopilgan</span>
                    @else
                      <span class="badge bg-warning">Jarayonda</span>
                    @endif
                  </td>
                  <td>

                     @if( $doc->status_doc == "process" )
                      <span class="badge bg-warning"> {{  $doc->status_doc }}</span>
                    @else
                      <span class="badge bg-success">{{  $doc->status_doc }}</span>
                    @endif
                  </td>
                  <td>
                    @if($courierAssignment)
                      @php
                        $courierBadgeClass = [
                          'sent' => 'bg-warning',
                          'accepted' => 'bg-success',
                          'rejected' => 'bg-danger',
                          'returned' => 'bg-success',
                        ][$courierAssignment->status] ?? 'bg-warning';
                      @endphp
                      <div>
                        <span class="badge {{ $courierBadgeClass }}">
                          {{ $courierAssignment->status }}
                        </span>
                      </div>
                      <div style="font-size:12px;color:#64748b;">
                        {{ $courierAssignment->courier->name ?? '-' }}
                      </div>
                    @else
                      <span class="badge bg-warning">Yo‘q</span>
                    @endif
                  </td>
                  <td>
                    <div class="details-actions">
                      <button type="button"
                              class="btn btn-custom btn-sm details-toggle"
                              data-bs-toggle="offcanvas"
                              data-bs-target="#docDetailsOffcanvas"
                              aria-controls="docDetailsOffcanvas"
                              data-id="{{ $doc->id }}">
                        Batafsil
                      </button>
                    </div>
                  </td>
                  <td>
                    @if($doc->status_doc === 'finish')
                      <span class="badge bg-success" style="padding:8px 14px; border-radius:10px; opacity:0.7;">
                        Tugallangan
                      </span>
                    @else
                      <div class="dropdown action-dropdown">
                        <button class="btn btn-custom btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          Actions
                        </button>
                        <ul class="dropdown-menu">
                          <li>
                            @if(!$courierActive)
                              <button class="dropdown-item action-item send send-courier-btn" type="button" data-id="{{ $doc->id }}">
                                Courierga jo'natish
                              </button>
                            @else
                              <span class="dropdown-item action-item send text-muted">Courierda</span>
                            @endif
                          </li>
                          <li>
                            <button class="dropdown-item action-item complete complete-btn {{ $courierActive ? 'disabled-link' : '' }}" type="button" data-id="{{ $doc->id }}">
                              Tugallash
                            </button>
                          </li>
                        </ul>
                      </div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:16px;">
          {{ $documents->onEachSide(1)->links() }}
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
      <div class="oc-title">Turi</div>
      <div class="oc-value">Document: <span id="ocDocumentType">-</span></div>
      <span class="oc-sub">Direction: <span id="ocDirectionType">-</span></span>
      <span class="oc-sub">Consulate: <span id="ocConsulateType">-</span></span>
    </div>
    <div class="oc-section">
      <div class="oc-title">Jarayon</div>
      <div class="oc-value">Mode: <span id="ocProcessMode">-</span></div>
    </div>
    <div class="oc-section">
      <div class="oc-title">To'lov</div>
      <div class="oc-value"><span id="ocFinalPrice">-</span></div>
      <span class="oc-sub" id="ocPaidAmount">-</span>
      <span class="oc-sub" id="ocDiscount">-</span>
    </div>
    <div class="oc-section">
      <div class="oc-title">Fayllar</div>
      <div class="oc-value"><span id="ocFilesCount">-</span></div>
      <div class="oc-files" id="ocFiles"></div>
    </div>
  </div>
</div>

<!-- Send to Courier Modal -->
<div class="modal fade" id="sendCourierModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="sendCourierForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Courierga jo'natish</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @if($couriers->count() === 0)
            <div class="alert alert-warning">Courier topilmadi. Avval courier qo'shing.</div>
          @else
            <div class="mb-3">
              <label class="form-label">Courier</label>
              <select name="courier_id" class="form-select" required>
                <option value="">Tanlang</option>
                @foreach($couriers as $courier)
                  <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Izoh</label>
              <textarea name="comment" class="form-control" rows="3" placeholder="Izoh (ixtiyoriy)"></textarea>
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
          <button type="submit" class="btn btn-primary" {{ $couriers->count() === 0 ? 'disabled' : '' }}>Jo'natish</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div id="completeModal" 
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  
  <div style="background:white; padding:25px; border-radius:12px; width:350px; text-align:center;">
      <h4>Hujjatni yakunlash</h4>
      <p>Siz bu hujjatni tugatdingizmi?</p>

      <div style="margin-top:20px; display:flex; justify-content:center; gap:15px;">
          <button id="cancelBtn" 
              style="padding:8px 16px; border-radius:10px; border:none; background:#64748b; color:white;">
              Bekor qilish
          </button>

          <a id="confirmComplete" href="#" 
             style="padding:8px 16px; border-radius:10px; border:none; background:#ef4444; color:white;">
             Ha, tugatildi
          </a>
      </div>
  </div>

</div>
@endsection
@section("script_include_end_body")
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('completeModal');
    const confirmBtn = document.getElementById('confirmComplete');
    const cancelBtn = document.getElementById('cancelBtn');
    const completeBaseUrl = "{{ route('admin_filial.document.complete', ['document' => '__id__']) }}";
    const sendCourierBase = "{{ route('admin_filial.document.send_courier', ['document' => '__id__']) }}";
    const sendCourierForm = document.getElementById('sendCourierForm');

    document.querySelectorAll('.complete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let id = this.dataset.id;

            // Modal dagi tasdiqlash linkiga route ulaymiz
            confirmBtn.href = completeBaseUrl.replace('__id__', id);

            modal.style.display = "flex";
        });
    });

    document.querySelectorAll('.send-courier-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let id = this.dataset.id;
            if (sendCourierForm) {
                sendCourierForm.action = sendCourierBase.replace('__id__', id);
                const courierModal = new bootstrap.Modal(document.getElementById('sendCourierModal'));
                courierModal.show();
            }
        });
    });

    cancelBtn.addEventListener('click', function () {
        modal.style.display = "none";
    });

    // Modalni tashqariga bosib yopish
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.style.display = "none";
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const table = document.getElementById('documents-table').getElementsByTagName('tbody')[0];
  const clientInput = document.getElementById('search-client');
  const serviceInput = document.getElementById('search-service');
  const statusSelect = document.getElementById('filter-status');
  const sortDeadline = document.getElementById('sort-deadline');

  function filterAndSortTable() {
    const clientVal = clientInput.value.toLowerCase();
    const serviceVal = serviceInput.value.toLowerCase();
    const statusVal = statusSelect.value;
    const sortVal = sortDeadline.value;

    Array.from(table.querySelectorAll('tr.doc-row')).forEach(row => {
      const client = row.dataset.client.toLowerCase();
      const service = row.dataset.service.toLowerCase();
      const status = row.dataset.status.toLowerCase();
      let show = true;
      if (!client.includes(clientVal)) show = false;
      if (!service.includes(serviceVal)) show = false;
      if (statusVal && status !== statusVal) show = false;
      row.style.display = show ? '' : 'none';
    });

    let rowsArray = Array.from(table.querySelectorAll('tr.doc-row')).filter(row => row.style.display !== 'none');
    if (sortVal === 'asc') rowsArray.sort((a,b) => parseInt(a.dataset.deadline) - parseInt(b.dataset.deadline));
    if (sortVal === 'desc') rowsArray.sort((a,b) => parseInt(b.dataset.deadline) - parseInt(a.dataset.deadline));
    rowsArray.forEach(row => table.appendChild(row));
  }

  clientInput.addEventListener('input', filterAndSortTable);
  serviceInput.addEventListener('input', filterAndSortTable);
  statusSelect.addEventListener('change', filterAndSortTable);
  sortDeadline.addEventListener('change', filterAndSortTable);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('documents-table');
  table.addEventListener('click', function (e) {
    const btn = e.target.closest('.details-toggle');
    if (!btn) return;
    const row = btn.closest('tr.doc-row');
    if (!row) return;

    const nf = new Intl.NumberFormat('ru-RU');
    const setText = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.textContent = value ?? '-';
    };

    setText('ocDocumentCode', row.querySelector('td:nth-child(2)')?.textContent?.trim() || '-');
    setText('ocClient', row.dataset.client || '-');
    setText('ocService', row.dataset.service || '-');
    setText('ocDocumentType', row.dataset.documentType || '-');
    setText('ocDirectionType', row.dataset.directionType || '-');
    setText('ocConsulateType', row.dataset.consulateType || '-');
    setText('ocProcessMode', row.dataset.processMode || '-');
    setText('ocFinalPrice', `${nf.format(parseFloat(row.dataset.finalPrice || 0))} сум`);
    setText('ocPaidAmount', `To'langan: ${nf.format(parseFloat(row.dataset.paidAmount || 0))} сум`);
    setText('ocDiscount', `Chegirma: ${nf.format(parseFloat(row.dataset.discount || 0))} сум`);

    const filesWrap = document.getElementById('ocFiles');
    const filesCount = document.getElementById('ocFilesCount');
    if (filesWrap && filesCount) {
      filesWrap.innerHTML = '';
      let files = [];
      try { files = JSON.parse(row.dataset.files || '[]'); } catch (err) { files = []; }
      if (files.length) {
        filesCount.textContent = `${files.length} ta`;
        files.forEach(f => {
          const a = document.createElement('a');
          a.className = 'oc-file-link';
          a.href = f.url;
          a.target = '_blank';
          a.textContent = f.name;
          filesWrap.appendChild(a);
        });
      } else {
        filesCount.textContent = 'Yo‘q';
      }
    }
  });
});
</script>
@endsection
