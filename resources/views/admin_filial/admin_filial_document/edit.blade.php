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
.addon-box { border:1px solid #e5e7eb; border-radius:12px; padding:12px; display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; transition:.15s; }
.addon-box:hover { background:#f1f5f9; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <form action="{{ route('admin_filial.document.update', $document->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- GLOBAL ERRORS --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <strong>Xatoliklar mavjud!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- CLIENT --}}
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">Mijoz</h5>
                <input type="text" class="form-control mb-2" value="{{ $document->client->name ?? '-' }}" disabled>
                <input type="hidden" name="client_id" value="{{ $document->client_id }}">
            </div>
        </div>

        {{-- SERVICE --}}
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">Xizmat</h5>
                <select name="service_id" id="serviceSelect" class="form-control select2 @error('service_id') is-invalid @enderror">
                    <option value="">— Tanlang —</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" data-price="{{ $s->price }}" {{ $document->service_id == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ number_format($s->price) }} so'm)
                        </option>
                    @endforeach
                </select>
                @error('service_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- DOCUMENT TYPE --}}
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">Hujjat turi</h5>
                <select name="document_type_id" class="form-control select2 @error('document_type_id') is-invalid @enderror">
                    <option value="">— Tanlang —</option>
                    @foreach($documentTypes as $dt)
                        <option value="{{ $dt->id }}" {{ (old('document_type_id', $document->document_type_id) == $dt->id) ? 'selected' : '' }}>
                            {{ $dt->name }}
                        </option>
                    @endforeach
                </select>
                @error('document_type_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- DIRECTION --}}
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">Yo'nalish</h5>
                <select name="direction_type_id" class="form-control select2 @error('direction_type_id') is-invalid @enderror">
                    <option value="">— Tanlang —</option>
                    @foreach($directions as $d)
                        <option value="{{ $d->id }}" {{ (old('direction_type_id', $document->direction_type_id) == $d->id) ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
                @error('direction_type_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- CONSULATE --}}
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">Konsullik</h5>
                <select name="consulate_type_id" class="form-control select2 @error('consulate_type_id') is-invalid @enderror">
                    <option value="">— Tanlang —</option>
                    @foreach($consulates as $c)
                        <option value="{{ $c->id }}" {{ (old('consulate_type_id', $document->consulate_type_id) == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                @error('consulate_type_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- ADDONS --}}
        <div class="card" id="addonsCard" style="display:none">
            <div class="card-body">
                <h5 class="mb-2">Qo‘shimcha xizmatlar</h5>
                <div id="addonsList"></div>
                @error('addons') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- DISCOUNT & PRICE --}}
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Diskont (%)</label>
                        <input type="number" name="discount" id="discount" class="form-control" value="{{ old('discount', $document->discount ?? 0) }}">
                        @error('discount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hisoblangan summa</label>
                        <input type="text" id="calculatedPrice" class="form-control" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Final price (so'm)</label>
                        <input type="number" name="final_price" id="finalPrice" class="form-control" value="{{ old('final_price', $document->final_price) }}" required>
                        @error('final_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- PAYMENTS --}}
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">To‘lovlar (mavjud)</h5>

                @if($document->payments && $document->payments->count())
                    @foreach($document->payments as $p)
                        <div class="addon-box">
                            <div>
                                <div><b>{{ number_format($p->amount) }} so'm</b></div>
                                <div class="text-muted">{{ $p->payment_type }} • {{ $p->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-muted">Hech qanday to‘lov yo‘q</div>
                @endif

                <hr>

                <h6 class="mt-2">Yangi to‘lov qo‘shish</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="number" name="paid_amount" class="form-control" placeholder="Summani kiriting">
                    </div>
                    <div class="col-md-6">
                        <select name="payment_type" class="form-control">
                            <option value="">Tanlanmagan</option>
                            <option value="cash">Naqd</option>
                            <option value="card">Karta</option>
                            <option value="online">Online</option>
                            <option value="admin_entry">Boshqalar</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <div class="card">
            <div class="card-body">
                <label class="form-label">Izoh</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $document->description) }}</textarea>
            </div>
        </div>

        <button class="btn btn-primary w-100 py-3 mt-3" type="submit">Saqlash</button>
    </form>
</div>
@endsection

@section('script_include_end_body')
<script>
$(function(){
    if ($.fn.select2) $('.select2').select2({ width: '100%' });

    let selectedAddons = @json($document->addons->pluck('id')) || [];
    selectedAddons = selectedAddons.map(x => String(x));
    const addonsBaseTemplate = "{{ route('admin_filial.get_service_addons', ['service' => ':id']) }}";

    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function loadAddons(serviceId, preSelected){
        if(!serviceId) {
            $('#addonsList').html('');
            $('#addonsCard').hide();
            return;
        }
        const url = addonsBaseTemplate.replace(':id', serviceId);
        $.get(url, function(data){
            let html = '';
            data.forEach(function(a){
                let checked = preSelected.includes(String(a.id)) ? 'checked' : '';
                html += `<label class="addon-box">
                            <div>
                                <b>${a.name}</b><br>
                                <small class="text-muted">${Number(a.price).toLocaleString()} so'm</small>
                            </div>
                            <input
                                type="checkbox"
                                name="addons[]"
                                class="addonCheckbox"
                                data-price="${a.price}"
                                value="${a.id}"
                                ${checked}
                            />
                        </label>`;
            });
            $('#addonsList').html(html);
            $('#addonsCard').show();
            debouncedCalculate();
        }).fail(function(jqXHR){
            console.error('Addons load failed', jqXHR);
            $('#addonsList').html('<div class="text-danger">Qoʻshimcha xizmatlarni yuklashda xato</div>');
            $('#addonsCard').show();
        });
    }

    const nf = new Intl.NumberFormat();
    function calculateFast() {
        const sel = document.getElementById('serviceSelect');
        const servicePrice = parseInt(sel.options[sel.selectedIndex]?.dataset.price) || 0;
        const checked = document.querySelectorAll('.addonCheckbox:checked');
        let addonsTotal = 0;
        for (let i = 0; i < checked.length; i++) {
            const p = checked[i].dataset.price;
            addonsTotal += p ? parseInt(p) : 0;
        }
        const total = servicePrice + addonsTotal;
        const discountVal = parseInt(document.getElementById('discount').value) || 0;
        const final = Math.round(total - (total * discountVal / 100));
        document.getElementById('calculatedPrice').value = nf.format(total);
        document.getElementById('finalPrice').value = final;
    }

    const debouncedCalculate = debounce(calculateFast, 120);

    $(document).on('change', '.addonCheckbox', debouncedCalculate);
    $('#serviceSelect').on('change', function(){
        const sid = $(this).val();
        if(!sid) {
            $('#addonsList').html('');
            $('#addonsCard').hide();
            debouncedCalculate();
            return;
        }
        loadAddons(sid, []);
    });

    $('#discount').on('input', debouncedCalculate);

    const currentServiceId = $('#serviceSelect').val();
    if (currentServiceId) loadAddons(currentServiceId, selectedAddons);
    setTimeout(debouncedCalculate, 250);
});
</script>
@endsection
