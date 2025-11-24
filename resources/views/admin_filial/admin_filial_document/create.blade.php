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

        {{-- GLOBAL ERROR LIST --}}
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

        {{-- CLIENT SEARCH --}}
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Mijoz tanlash</h4>

                <div class="mb-3 position-relative">
                    <label class="form-label">Telefon raqamdan qidirish</label>
                    <input type="text" id="clientPhone" class="form-control @error('client_id') is-invalid @enderror" placeholder="998..." value="{{ old('client_phone_search') }}">
                    <div id="phoneSearchResults" class="border bg-white rounded shadow-sm" style="display:none; position:absolute; width:100%; z-index:999;"></div>
                    @error('client_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- SELECTED CLIENT --}}
                <div id="selectedClient" class="mt-3" style="display:none;">
                    <label class="form-label">Ism</label>
                    <input type="text" class="form-control mb-2" id="selectedClientName" readonly>
                    <label class="form-label">Telefon raqam</label>
                    <input type="text" class="form-control mb-2" id="selectedClientPhone" readonly>
                    <input type="hidden" id="clientId" name="client_id" value="{{ old('client_id') }}">
                </div>

                {{-- NEW CLIENT FORM --}}
                <div id="newClientForm" class="mt-3" style="display:{{ old('new_client_name') ? 'block':'none' }}">
                    <label class="form-label">Ism</label>
                    <input type="text" class="form-control mb-2 @error('new_client_name') is-invalid @enderror"
                           id="newClientName" name="new_client_name" value="{{ old('new_client_name') }}">
                    @error('new_client_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <label class="form-label">Telefon raqam</label>
                    <input type="text" class="form-control mb-2 @error('new_client_phone') is-invalid @enderror"
                           id="newClientPhone" name="new_client_phone" value="{{ old('new_client_phone') }}">
                    @error('new_client_phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <label class="form-label">Izoh</label>
                    <textarea class="form-control mb-2 @error('new_client_desc') is-invalid @enderror"
                              id="newClientDesc" name="new_client_desc" rows="2">{{ old('new_client_desc') }}</textarea>
                    @error('new_client_desc')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <button type="button" class="btn btn-success btn-sm" id="lockClientFormBtn">Mijozni saqlash</button>
                </div>
            </div>
        </div>

        {{-- SERVICE + ADDONS + DISCOUNT + FINAL PRICE + PAYMENT --}}
        @include('admin.document.partials.service_addons_payment')

        {{-- DESCRIPTION --}}
        <div class="card">
            <div class="card-body">
                <label class="form-label">Izoh</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button class="btn btn-primary w-100 py-3 mt-3" id="mainSaveBtn">Hujjatni saqlash</button>
    </form>
</div>
@endsection

@section('script_include_end_body')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){
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
                        html += `<div class="p-2 result-item" data-id="${item.id}" data-phone="${item.phone_number}" data-name="${item.name}">📞 ${item.phone_number} — ${item.name}</div>`;
                    });
                    html += '<div class="p-2 new-client-btn mt-1">➕ Yangi mijoz yaratish</div>';
                }
                $results.html(html).show();
            });
        },300);
    });

    $(document).on('click', '.result-item', function(){
        let name = $(this).data('name');
        let phone = $(this).data('phone');
        let id = $(this).data('id');

        $('#selectedClientName').val(name);
        $('#selectedClientPhone').val(phone);
        $('#clientId').val(id);

        $('#selectedClient').slideDown();
        $('#newClientForm').slideUp();
        $results.hide();
        $input.val('');
    });

    $(document).on('click', '.new-client-btn', function(){
        $('#clientId').val('');
        $('#newClientPhone').val($('#clientPhone').val());
        $('#newClientForm').slideDown();
        $('#selectedClient').slideUp();
        $results.hide();
    });

    $('#lockClientFormBtn').on('click', function(){
        $('#newClientName, #newClientPhone, #newClientDesc').prop('readonly', true);
        $(this).removeClass('btn-success').addClass('btn-secondary').text('Saqlandi ✓').prop('disabled', true);
    });

    @if($errors->any() && old('new_client_name'))
        $('#newClientForm').show();
    @endif
});
</script>
@endsection
