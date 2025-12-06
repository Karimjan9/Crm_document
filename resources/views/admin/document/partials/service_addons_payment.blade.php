<div class="card">
    <div class="card-body">
        <h4 class="mb-3">Xizmat</h4>

        {{-- SERVICE SELECT --}}
        <select class="form-select mb-2 @error('service_id') is-invalid @enderror" id="serviceSelect" name="service_id">
            <option value="">Tanlang...</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" data-price="{{ $service->price }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
        @error('service_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <input type="text" id="servicePrice" class="form-control mb-2" placeholder="Xizmat narxi" readonly value="{{ old('service_price') ?? '' }}">

        {{-- ADDONS --}}
        <div id="addonsContainer" class="mt-3" style="display:none;">
            <h5>Qo'shimcha xizmatlar</h5>
            <div id="addonsList" class="d-flex flex-wrap gap-2"></div>
            @error('addons')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
           <label class="form-label mt-3">Hujjat turi</label>
                <select name="document_type_id" class="form-control">
            @foreach($documentTypes as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <label class="form-label mt-3">Hujjat yo'nalishi</label>
        <select name="direction_type_id" class="form-control">
            @foreach($directionTypes as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
        </select>
 <label class="form-label mt-3">Konsullik</label>
        <select name="consulate_type_id" class="form-control">
            @foreach($consulateTypes as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        {{-- DISCOUNT --}}
        <label class="form-label mt-3">Diskont (%)</label>
        <input type="number"
               class="form-control mb-2 @error('discount') is-invalid @enderror"
               id="discount" name="discount" placeholder="0" value="{{ old('discount') ?? "" }}">
        @error('discount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        {{-- FINAL PRICE --}}
        <label class="form-label">Final Narx</label>
        <input type="text" class="form-control mb-2" id="finalPrice" name="final_price" readonly value="{{ old('final_price') ?? 0 }}">

        {{-- PAYMENT INPUTS --}}
        <label class="form-label mt-2">To'lov miqdori</label>
        <input type="number"
               class="form-control mb-2 @error('paid_amount') is-invalid @enderror"
               name="paid_amount" placeholder="0" value="{{ old('paid_amount') ?? "" }}">
        @error('paid_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <label class="form-label">To'lov turi</label>
        <select name="payment_type" class="form-select mb-2 @error('payment_type') is-invalid @enderror">
            <option value="">Tanlang...</option>
            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected':'' }}>Naqd</option>
            <option value="card" {{ old('payment_type') == 'card' ? 'selected':'' }}>Plastik karta</option>
            <option value="online" {{ old('payment_type') == 'online' ? 'selected':'' }}>Onlayn</option>
            <option value="admin_entry" {{ old('payment_type') == 'admin_entry' ? 'selected':'' }}>Boshqalar</option>
        </select>
        @error('payment_type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- JS --}}
<script>
let servicePrice = parseFloat($('#servicePrice').val()) || 0;
let addons = {};
let addonsChecked = {};
let discount = parseFloat($('#discount').val()) || 0;

// SERVICE CHANGE
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
            html += `
                <div class="addon-badge">
                    <label>
                        <input type="checkbox" name="addons[]" value="${a.id}" class="addon-checkbox" data-id="${a.id}">
                        ${a.name}
                        <small class="text-primary d-block">Narx: ${a.price}</small>
                    </label>
                </div>`;
        });

        $('#addonsList').html(html);
        if(res.length) $('#addonsContainer').slideDown(); else $('#addonsContainer').slideUp();

        // old selected addons
        @if(old('addons'))
            let oldAddons = @json(old('addons'));
            oldAddons.forEach(id => {
                $(`.addon-checkbox[value='${id}']`).prop('checked', true);
                addonsChecked[id] = true;
            });
        @endif

        updateFinalPrice();
    });
});

// ADDON CHECKBOX CHANGE
$(document).on('change', '.addon-checkbox', function(){
    let id = $(this).data('id');
    addonsChecked[id] = $(this).is(':checked');
    updateFinalPrice();
});

// DISCOUNT INPUT
$('#discount').on('input', function(){
    discount = parseFloat($(this).val()) || 0;
    updateFinalPrice();
});

// FINAL PRICE CALC
function updateFinalPrice(){
    let total = servicePrice;
    for(let id in addons){
        if(addonsChecked[id]) total += addons[id];
    }
    let final = total - (total*(discount/100));
    final = Math.max(final,0);
    $('#finalPrice').val(final.toFixed(2));
}

// INITIAL FINAL PRICE
updateFinalPrice();
</script>
