@extends('template')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
.page-wrapper { padding: 24px; }

.card { border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; margin-bottom: 20px; }
.card-body { padding: 28px; }

.form-control, .form-select { border-radius: 10px; }
.addon-badge { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 12px; display: inline-flex; flex-direction: column; gap: 4px; transition: all 0.2s ease; cursor: pointer; }
.addon-badge:hover { background: #e9f2ff; transform: translateY(-2px); box-shadow: 0 3px 8px rgba(0,0,0,0.12); }

.btn-primary { background: linear-gradient(135deg,#2563eb,#3b82f6); border:none; }
.btn-primary:hover { opacity:0.9; }

#phoneSearchResults .result-item:hover { background: #f0f0f0; cursor:pointer; }
.new-client-btn { cursor:pointer; color: #2563eb; font-weight: 600; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <form action="{{ route('admin_filial.document.store') }}" method="POST">
        @csrf

        {{-- CLIENT SEARCH --}}
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Mijoz tanlash</h4>
                <div class="mb-3 position-relative">
                    <label class="form-label">Telefon raqamdan qidirish</label>
                    <input type="text" id="clientPhone" class="form-control" placeholder="99890...">
                    <div id="phoneSearchResults" class="border bg-white rounded shadow-sm" style="display:none; position:absolute; width:100%; z-index:999;"></div>
                </div>
                <div id="newClientForm" class="mt-3" style="display:none;">
                    <label class="form-label">Ism</label>
                    <input type="text" class="form-control mb-2" id="newClientName" name="new_client_name">
                    <label class="form-label">Telefon raqam</label>
                    <input type="text" class="form-control mb-2" id="newClientPhone" name="new_client_phone">
                    <label class="form-label">Izoh</label>
                    <textarea class="form-control mb-2" id="newClientDesc" name="new_client_desc" rows="2"></textarea>
                    <button type="button" class="btn btn-success btn-sm" id="lockClientFormBtn">Mijozni saqlash</button>
                </div>
                <input type="hidden" id="clientId" name="client_id">
            </div>
        </div>

        {{-- SERVICE --}}
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Xizmat</h4>
                <select class="form-select mb-2" id="serviceSelect" name="service_id">
                    <option value="">Tanlang...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" data-price="{{ $service->price }}">{{ $service->name }}</option>
                    @endforeach
                </select>
                <input type="text" id="servicePrice" class="form-control" placeholder="Xizmat narxi" readonly>
            </div>
        </div>

        {{-- ADDONS --}}
        <div class="card" id="addonsContainer" style="display:none;">
            <div class="card-body">
                <h4 class="mb-3">Qo'shimcha xizmatlar</h4>
                <div id="addonsList" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>

        {{-- DISCOUNT & FINAL PRICE --}}
        <div class="card">
            <div class="card-body">
                <label class="form-label">Diskont (%)</label>
                <input type="number" class="form-control mb-2" id="discount" name="discount" value="0">
                <label class="form-label">Final Narx</label>
                <input type="text" class="form-control" id="finalPrice" name="final_price" readonly>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <div class="card">
            <div class="card-body">
                <label class="form-label">Izoh</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
        </div>

        {{-- SUBMIT --}}
        <button class="btn btn-primary w-100 py-3 mt-3" id="mainSaveBtn">Hujjatni saqlash</button>

    </form>
</div>
@endsection

@section('script_include_end_body')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    // CLIENT SEARCH – avvalgi kod o‘zgarmaydi
    let $input = $('#clientPhone'), $results = $('#phoneSearchResults'), timer = null;

    $input.on('input', function(){
        let q = $(this).val().trim(); clearTimeout(timer);
        if(q.length < 3){ $results.hide().html(''); return; }
        timer = setTimeout(() => {
            $.get("{{ route('admin_filial.clients.search') }}", {q:q}, function(res){
                let html = '';
                if(res.length === 0){
                    html = '<div class="p-2 text-muted">Topilmadi</div><div class="p-2 new-client-btn">➕ Yangi mijoz yaratish</div>';
                } else {
                    res.forEach(item => {
                        html += `<div class="p-2 result-item" data-id="${item.id}" data-phone="${item.phone_number}">📞 ${item.phone_number} — ${item.name}</div>`;
                    });
                    html += '<div class="p-2 new-client-btn mt-1">➕ Yangi mijoz yaratish</div>';
                }
                $results.html(html).show();
            });
        },300);
    });

    $(document).on('click', '.result-item', function(){
        $input.val($(this).data('phone'));
        $('#clientId').val($(this).data('id'));
        $('#newClientForm').slideUp();
        $results.hide();
    });

    $(document).on('click', '.new-client-btn', function(){
        $('#clientId').val('');
        $('#newClientPhone').val($('#clientPhone').val());
        $('#newClientForm').slideDown();
        $results.hide();
    });

    $('#lockClientFormBtn').on('click', function(){
        $('#newClientName, #newClientPhone, #newClientDesc').prop('disabled', true);
        $(this).removeClass('btn-success').addClass('btn-secondary').text('Saqlandi ✓').prop('disabled', true);
    });

    // ==============================
    // SERVICE + ADDONS + DISCOUNT
    // ==============================
    let servicePrice = 0;
    let addons = {}; // {addonId: price}
    let addonsChecked = {}; // {addonId: true/false}
    let discount = 0;

    $('#serviceSelect').on('change', function(){
        let $option = $(this).find('option:selected');
        servicePrice = parseFloat($option.data('price')) || 0;
        $('#servicePrice').val(servicePrice);

        let serviceId = $(this).val();
        if(!serviceId){
            addons = {}; addonsChecked = {};
            $('#addonsList').html(''); $('#addonsContainer').slideUp();
            updateFinalPrice(); return;
        }

        $.get("/admin_filial/service/"+serviceId+"/addons", function(res){
            addons = {}; addonsChecked = {};
            let html = '';
            res.forEach(a => {
                addons[a.id] = parseFloat(a.price);
                addonsChecked[a.id] = false;
                html += `<div class="addon-badge">
                            <label>
                                <input type="checkbox" class="addon-checkbox" data-id="${a.id}"> ${a.name}
                                <small class="text-primary d-block">Narx: ${a.price}</small>
                            </label>
                         </div>`;
            });
            $('#addonsList').html(html);
            if(res.length) $('#addonsContainer').slideDown(); else $('#addonsContainer').slideUp();
            updateFinalPrice();
        });
    });

    // checkbox event
    $(document).on('change', '.addon-checkbox', function(){
        let id = $(this).data('id');
        addonsChecked[id] = $(this).is(':checked');
        updateFinalPrice();
    });

    // discount input
    $('#discount').on('input', function(){
        discount = parseFloat($(this).val()) || 0;
        updateFinalPrice();
    });

    // ==============================
    // CALCULATE FINAL PRICE
    // ==============================
    function updateFinalPrice(){
        let total = servicePrice;
        for(let id in addons){
            if(addonsChecked[id]) total += addons[id];
        }
        let final = total - (total*(discount/100));
        final = Math.max(final,0);
        $('#finalPrice').val(final.toFixed(2));
    }

    // FORM SUBMIT
    $('#mainSaveBtn').on('click', function(){
        $(this).prop('disabled', true).text('Saqlanmoqda...');
        $('input, textarea, button, select').not(this).prop('disabled', true);
        $(this).closest('form').submit();
    });

});
</script>


@endsection
