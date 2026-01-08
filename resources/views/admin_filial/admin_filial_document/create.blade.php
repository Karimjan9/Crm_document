@extends('template')

@section('style')
<style>
:root{
  --accent-start:#2563eb;
  --accent-end:#3b82f6;
  --muted:#64748b;
  --card-radius:16px;
  --shadow-1:0 4px 18px rgba(0,0,0,0.04);
  --shadow-hover:0 10px 32px rgba(37,99,235,0.14);
  --transition:260ms cubic-bezier(.2,.9,.3,1);
}

/* GLOBAL */
body{
  font-family:'Poppins', sans-serif;
  background:#f5f7fb;
  color:#0f172a;
}
.page-wrapper{
    margin-left:250px;
    padding:26px;
    padding-bottom:120px; /* Yangi qo'shildi */
}

}
@media(max-width:900px){ .page-wrapper{ margin-left:0; } }

/* PROGRESS */
.progress-wrap{
  height:6px;
  background:#e3e8ef;
  overflow:hidden;
  border-radius:8px;
  margin-bottom:24px;
}
.progress-bar{
  height:100%;
  width:0%;
  background:linear-gradient(90deg,var(--accent-start),var(--accent-end));
}
.steps-controls{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:24px;
    padding:16px 0;
    position:sticky;
    bottom:0;
    background:#f7f9fc;
    z-index:99;
    border-top:1px solid rgba(0,0,0,0.06);
}
.result-item:hover, .new-client-btn:hover {
    background:linear-gradient(90deg,#3b82f6,#2563eb);
    color:#fff;
    border-color:#2563eb;
}
.result-item.selected {
    background:#10b981;
    color:#fff;
    border-color:#059669;
}
.new-client-btn {font-weight:600; justify-content:center;}


/* CARD */
.card{
  background:rgba(255,255,255,0.88);
  border-radius:var(--card-radius);
  border:1px solid rgba(0,72,255,0.04);
  backdrop-filter:blur(6px);
  margin-bottom:22px;
  box-shadow:var(--shadow-1);
  transition:var(--transition);
}
.card:hover{
  transform:translateY(-2px);
  box-shadow:var(--shadow-hover);
}
.card-header{
  display:flex;
  gap:14px;
  padding:18px 22px;
  border-bottom:1px solid rgba(0,0,0,0.04);
}
.card-icon{
  min-width:11px;
  min-height:11px;
  background:linear-gradient(90deg,var(--accent-start),var(--accent-end));
  border-radius:50%;
}
.card-title{font-weight:600;font-size:15px}
.card-sub{font-size:12px;color:var(--muted)}
.card-body{padding:18px 22px}

/* INPUT GROUP */
.input-row{
  display:flex;
  gap:12px;
  background:#fff;
  padding:12px 14px;
  border-radius:12px;
  border:1px solid rgba(20,20,33,0.06);
  margin-bottom:14px;
  transition:var(--transition);
}
.input-row:hover{
  border-color:rgba(37,99,235,0.25);
  box-shadow:0 6px 22px rgba(37,99,235,0.08);
}
.form-label{
  width:130px;
  font-size:13px;
  font-weight:600;
  color:#1e293b;
}

/* INPUTS */
.form-control,.form-select{
  flex:1;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid rgba(0,0,0,0.06);
  font-size:14px;
}
.form-control:focus,.form-select:focus{
  border-color:var(--accent-end);
  box-shadow:0 0 0 3px rgba(59,130,246,0.22);
}

/* ADDONS */
.addons-wrap{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
}
.addon-badge{
  border:1px solid rgba(0,0,0,0.06);
  padding:10px 12px;
  font-size:13px;
  border-radius:12px;
}
.addon-badge:hover{
  background:var(--accent-start);
  color:#fff;
}

/* BUTTONS */
.btn{
  padding:10px 18px;
  border-radius:12px;
  font-size:14px;
  border:none;
  cursor:pointer;
  font-weight:600;
}
.btn-primary{
  background:linear-gradient(135deg,var(--accent-start),var(--accent-end));
  color:#fff;
}
.btn-primary:hover{opacity:.90}
.btn-muted{
  background:#eef2f6;
}
.btn-muted:hover{
  background:#e2e8f0;
}

/* SEARCH RESULT DESIGN */
.result-item, .new-client-btn{
  padding:10px 12px;
  border-radius:10px;
  font-size:13px;
  border:1px solid rgba(0,0,0,0.08);
  cursor:pointer;
}
.result-item:hover, .new-client-btn:hover{
  background:linear-gradient(90deg,var(--accent-start),var(--accent-end));
  color:#fff;
}

/* RESPONSIVE */
@media(max-width:720px){
  .page-wrapper{padding:10px}
  .form-label{width:100px;font-size:12px}
  .btn{width:100%;text-align:center}
}
</style>
@endsection



@section('body')
<div class="page-wrapper">

{{-- PROGRESS BAR --}}
<div class="progress-wrap">
    <div class="progress-bar" id="progressBar"></div>
</div>

<form method="POST" action="{{ route('admin_filial.document.store') }}" id="multiStepForm">
@csrf

{{-- STEP 1 (Guruh 1 + 2) --}}
<div id="step-1">
    {{-- GROUP 1: CLIENT --}}
    <div class="card {{ old('client_id')||old('new_client_name') ? 'group-active' : '' }}" id="group-client">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon"></div>
                <div>
                    <div class="card-title">1. Mijoz</div>
                    <div class="card-sub">Telefon orqali qidirish yoki yangi mijoz qo'shish</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="input-row">
                <label class="form-label">Telefondan qidirish</label>
                <div style="flex:1">
                    <input type="text" id="clientPhone" class="form-control" placeholder="998..." value="{{ old('client_phone_search') }}">
                    <div id="phoneSearchResults" style="display:none; margin-top:6px; gap:6px; flex-direction:column;"></div>
                </div>
            </div>

            <div id="selectedClient" style="display: {{ old('client_id') ? 'block' : 'none' }}; margin-top:6px;">
                <div class="input-row"><label class="form-label">Ism</label><input type="text" id="selectedClientName" class="form-control" readonly value="{{ old('selected_client_name') }}"></div>
                <div class="input-row"><label class="form-label">Telefon</label><input type="text" id="selectedClientPhone" class="form-control" readonly value="{{ old('selected_client_phone') }}"></div>
                <input type="hidden" id="clientId" name="client_id" value="{{ old('client_id') }}">
            </div>

            <div id="newClientForm" style="display: {{ old('new_client_name') ? 'block' : 'none' }}; margin-top:8px;">
                <div class="input-row"><label class="form-label">Yangi mijoz — Ism</label><input type="text" id="newClientName" name="new_client_name" class="form-control" value="{{ old('new_client_name') }}"></div>
                <div class="input-row"><label class="form-label">Telefon</label><input type="text" id="newClientPhone" name="new_client_phone" class="form-control" value="{{ old('new_client_phone') }}"></div>
                <div class="input-row"><label class="form-label">Izoh</label><textarea id="newClientDesc" name="new_client_desc" class="form-control" rows="2">{{ old('new_client_desc') }}</textarea></div>
                <div style="display:flex;gap:10px;margin-top:6px">
                    <button type="button" class="btn btn-muted" id="resetNewClientBtn">✖ Bekor qilish</button>
                    <button type="button" class="btn btn-primary" id="lockClientFormBtn">✓ Mijozni saqlash</button>
                </div>
            </div>
        </div>
    </div>

    {{-- GROUP 2: DOCUMENT --}}
    <div class="card {{ old('document_type_id')||old('direction_type_id')||old('consulate_required') ? 'group-active' : '' }}" id="group-doc">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon"></div>
                <div>
                    <div class="card-title">2. Hujjat ma'lumotlari</div>
                    <div class="card-sub">Hujjat turi, yo'nalishi va konsullik</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="input-row">
                <label class="form-label">Hujjat turi</label>
                <select name="document_type_id" class="form-select" id="documentType">
                    <option value="">Tanlang...</option>
                    @foreach($documentTypes as $t)
                        <option value="{{ $t->id }}" {{ old('document_type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-row">
                <label class="form-label">Apostil</label>
                <select name="direction_type_id" class="form-select" id="directionType">
                    <option value="">Tanlang...</option>
                    @foreach($directionTypes as $d)
                        <option value="{{ $d->id }}" {{ old('direction_type_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-row">
                <label class="form-label">Legalizatsiya</label>
                <div style="flex:1">
                    <label style="display:flex;align-items:center;gap:10px">
                        <input type="checkbox" id="consulateCheckbox" name="consulate_required" value="1" {{ old('consulate_required') ? 'checked' : '' }}> <span class="inline-help">Konsullik kerak</span>
                    </label>
                    <div id="consulateSelectWrap" style="margin-top:8px; display: {{ old('consulate_required') ? 'block' : 'none' }}">
                        <select name="consulate_type_id" class="form-select">
                            <option value="">Tanlang...</option>
                            @foreach($consulateTypes as $c)
                                <option value="{{ $c->id }}" {{ old('consulate_type_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NEXT STEP --}}
    <div class="steps-controls">
        <div></div>
        <div>
            <button type="button" class="btn btn-primary" id="toStep2Btn">Keyingi →</button>
        </div>
    </div>
</div>

{{-- STEP 2 (Guruh 3 + 4) --}}
<div id="step-2" class="{{ $errors->any() && (old('service_id')||old('paid_amount')) ? '' : 'step-hidden' }}">
    {{-- GROUP 3: SERVICE --}}
    <div class="card {{ old('service_id')||old('addons') ? 'group-active' : '' }}" id="group-service">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon"></div>
                <div>
                    <div class="card-title">3. Xizmat va addonlar</div>
                    <div class="card-sub">Xizmatni tanlang, qo'shimcha xizmatlarni belgilang va chegirma kiriting</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="input-row">
                <label class="form-label">Xizmat</label>
                <select class="form-select" id="serviceSelect" name="service_id">
                    <option value="">Tanlang...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" data-price="{{ $service->price }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-row"><label class="form-label">Xizmat narxi</label><input type="text" id="servicePrice" class="form-control" readonly value="{{ old('service_price') ?? '' }}"></div>

            <div id="addonsContainer" style="display:none">
                <div class="input-row">
                    <label class="form-label">Qo'shimcha</label>
                    <div class="addons-wrap" id="addonsList"></div>
                </div>
            </div>

            <div class="input-row"><label class="form-label">Diskont (%)</label><input type="number" id="discount" name="discount" class="form-control" value="{{ old('discount') ?? 0 }}" min="0" max="100"></div>
            <div class="input-row"><label class="form-label">Final narx</label><input type="text" id="finalPrice" name="final_price" class="form-control" readonly value="{{ old('final_price') ?? 0 }}"></div>
        </div>
    </div>

    {{-- GROUP 4: PAYMENT --}}
    <div class="card {{ old('paid_amount')||old('payment_type') ? 'group-active' : '' }}" id="group-payment">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon"></div>
                <div>
                    <div class="card-title">4. To'lov</div>
                    <div class="card-sub">To'lov turi va miqdori</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="input-row"><label class="form-label">To'lov miqdori</label><input type="number" class="form-control" name="paid_amount" placeholder="0" value="{{ old('paid_amount') ?? '' }}"></div>

            <div class="input-row"><label class="form-label">To'lov turi</label>
                <select name="payment_type" class="form-select">
                    <option value="">Tanlang...</option>
                    <option value="cash" {{ old('payment_type') == 'cash' ? 'selected':'' }}>Naqd</option>
                    <option value="card" {{ old('payment_type') == 'card' ? 'selected':'' }}>Plastik karta</option>
                    <option value="online" {{ old('payment_type') == 'online' ? 'selected' : '' }}>Onlayn</option>
                    <option value="admin_entry" {{ old('payment_type') == 'admin_entry' ? 'selected' : '' }}>Boshqalar</option>
                </select>
            </div>

            <div class="input-row"><label class="form-label">Izoh</label><textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea></div>
        </div>
    </div>

    {{-- STEP 2 controls: Back + Save --}}
    <div class="steps-controls">
        <div>
            <button type="button" class="btn btn-muted" id="backToStep1Btn">← Orqaga</button>
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">💾 Saqlash</button>
        </div>
    </div>
</div>

</form>
</div>
@endsection

@section('script_include_end_body')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    const progressBar = $('#progressBar');
    function updateProgress(step){ progressBar.css('width', step===1?'50%':'100%'); }

    function showStep(n){
        if(n===1){ $('#step-2').slideUp(400,function(){ $('#step-1').slideDown(400); updateProgress(1); }); }
        else { $('#step-1').slideUp(400,function(){ $('#step-2').slideDown(400); updateProgress(2); }); }
        $('html,body').animate({scrollTop:0},400);
    }

    // initial
    @if($errors->any() && (old('service_id')||old('paid_amount')||old('payment_type'))) showStep(2); @else showStep(1); @endif

    $('#toStep2Btn').click(()=>showStep(2));
    $('#backToStep1Btn').click(()=>showStep(1));

    // CLIENT SEARCH
    let timer=null;
    $('#clientPhone').on('input',function(){
        let q=$(this).val().trim(); clearTimeout(timer);
        if(q.length<3){ $('#phoneSearchResults').hide().html(''); return; }
        timer=setTimeout(()=>{
            $.get("{{ route('admin_filial.clients.search') }}",{q:q},function(res){
                let html='';
                if(!res || res.length===0){
                    html='<div class="new-client-btn">➕ Yangi mijoz yaratish</div>';
                } else {
                    res.forEach(item=>{
                        html+=`<div class="result-item" data-id="${item.id}" data-phone="${item.phone_number}" data-name="${item.name}">${item.name} — ${item.phone_number}</div>`;
                    });
                    html+='<div class="new-client-btn">➕ Yangi mijoz yaratish</div>';
                }
                $('#phoneSearchResults').html(html).css({display:'flex', flexDirection:'column', gap:'6px'});
            });
        },200);
    });

    $(document).on('click','.result-item',function(){
        $('.result-item').removeClass('selected'); $(this).addClass('selected');
        $('#selectedClientName').val($(this).data('name')); $('#selectedClientPhone').val($(this).data('phone'));
        $('#clientId').val($(this).data('id')); $('#selectedClient').slideDown(); $('#newClientForm').slideUp(); $('#phoneSearchResults').hide(); $('#clientPhone').val('');
    });

    $(document).on('click','.new-client-btn',function(){ $('#clientId').val(''); $('#newClientPhone').val($('#clientPhone').val()); $('#newClientForm').slideDown(); $('#selectedClient').slideUp(); $('#phoneSearchResults').hide(); });
    $('#resetNewClientBtn').click(function(){ $('#newClientForm').slideUp(); $('#clientPhone').val(''); });
    $('#lockClientFormBtn').click(function(){ $('#newClientForm').slideUp(); });

    // CONSULATE SHOW/HIDE
    $('#consulateCheckbox').change(function(){ $('#consulateSelectWrap').slideToggle(200); });

    // SERVICE + ADDON PRICE CALC
    let addonsData=@json($addons);
    function updatePrice(){
        let basePrice=parseFloat($('#serviceSelect option:selected').data('price')||0);
        let addonPrice=0;
        $('.addon-checkbox:checked').each(function(){ addonPrice+=parseFloat($(this).data('price')||0); });
        let discount=parseFloat($('#discount').val()||0);
        let final=(basePrice+addonPrice)*(1-discount/100);
        $('#servicePrice').val(basePrice);
        $('#finalPrice').val(final.toFixed(2));
    }

    // populate addons
    $('#serviceSelect').change(function(){
        let sid=$(this).val();
        $('#addonsList').html('');
        if(!sid){ $('#addonsContainer').slideUp(); updatePrice(); return; }
        addonsData.filter(a=>a.service_id==sid).forEach(a=>{
            $('#addonsList').append(`<label class="addon-badge"><input type="checkbox" class="addon-checkbox" name="addons[]" value="${a.id}" data-price="${a.price}" style="margin-right:6px">${a.name} (+${a.price})</label>`);
        });
        $('#addonsContainer').slideDown();
        updatePrice();
    });
    $(document).on('change','.addon-checkbox',updatePrice);
    $('#discount').on('input',updatePrice);
    $('#serviceSelect').trigger('change');
});
</script>
<script>
fetch('/admin_filial/api/clients')
    .then(response => response.json())
    .then(data => {
        console.log('Clients:', data);
    })
    .catch(error => console.error(error));
</script>
@endsection
