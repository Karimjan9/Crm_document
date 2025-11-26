@extends('template')

@section('style')

{{-- @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'); --}}

<style>
    
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');  

* { margin: 0; padding: 0; box-sizing: border-box; }
body { 
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #1e293b;
    min-height: 100vh;
    padding: 20px; /* 🔥 Asl muammoni hal qiladi */
}

.page-wrapper {
    margin-left: 250px !important; /* Sidebar kengligi qanchaligini ayt — men tuzatib beraman */
    padding: 24px;
    min-height: 100vh;
}
.card { 
    border-radius: 14px; 
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); 
    border: 1px solid #e2e8f0; 
    margin-bottom: 20px;
    transition: all 0.3s ease;
}
.card:hover { box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1); }
.card-body { padding: 24px; }
.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.card-title::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    border-radius: 2px;
}
.form-label { font-weight: 500; color: #334155; margin-bottom: 8px; font-size: 14px; }
.form-control, .form-select {
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: #fff;
}
.form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); background: #fff; }
.form-control:hover { border-color: #cbd5e1; }
.form-control:disabled, .form-control[readonly] { background-color: #f8fafc; border-color: #e2e8f0; color: #64748b; }
.form-control.is-invalid { border-color: #ef4444; }
.invalid-feedback { font-size: 13px; color: #ef4444; margin-top: 6px; }
.alert-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; border-radius: 12px; padding: 16px; font-size: 14px; }
.alert-danger strong { display: block; margin-bottom: 10px; font-weight: 600; }
.alert-danger ul { margin: 0; padding-left: 20px; }
.alert-danger li { margin-bottom: 4px; }
.btn-primary {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    border: none;
    border-radius: 10px;
    font-weight: 600;
    padding: 12px 24px;
    font-size: 15px;
    transition: all 0.3s ease;
    color: white;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3); opacity: 0.95; }
.btn-success { background: #10b981; border: none; border-radius: 10px; font-weight: 600; padding: 10px 16px; font-size: 13px; }
.btn-success:hover { background: #059669; }
.btn-secondary { background: #6b7280; }
.btn-secondary:hover { background: #4b5563; }
#phoneSearchResults { border: 1.5px solid #e2e8f0; background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); max-height: 300px; overflow-y: auto; margin-top: 8px; }
.result-item { padding: 12px 14px; cursor: pointer; transition: all 0.2s ease; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
.result-item:last-child { border-bottom: none; }
.result-item:hover { background: #f0f9ff; color: #1e40af; padding-left: 18px; }
.new-client-btn { padding: 12px 14px; cursor: pointer; color: #2563eb; font-weight: 600; transition: all 0.2s ease; font-size: 13px; background: #f0f9ff; border-radius: 8px; margin: 8px; text-align: center; }
.new-client-btn:hover { background: #e0f2fe; transform: scale(1.02); }
.selected-info { background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(59, 130, 246, 0.05)); border: 1.5px solid #e0e7ff; border-radius: 12px; padding: 16px; margin-top: 16px; }
.selected-info .form-control { background: white; border-color: #e2e8f0; }
textarea.form-control { resize: vertical; }
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }
.section-divider { height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent); margin: 24px 0; }
@media (max-width: 768px) {
    .card-body { padding: 16px; }
    .form-control, .form-select { padding: 10px 12px; font-size: 16px; }
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <form method="POST" action="{{ route('admin_filial.document.store') }}">
        @csrf

        {{-- Error Messages --}}
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

        {{-- Client Selection --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title">Mijoz tanlash</div>

                <div class="form-group">
                    <label class="form-label">Telefon raqamdan qidirish</label>
                    <input type="text" id="clientPhone" class="form-control @error('client_id') is-invalid @enderror" placeholder="998..." value="{{ old('client_phone_search') }}">
                    <div id="phoneSearchResults" style="display: none;"></div>
                    @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="selectedClient" class="selected-info" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Ism</label>
                        <input type="text" class="form-control" id="selectedClientName" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefon raqam</label>
                        <input type="text" class="form-control" id="selectedClientPhone" readonly>
                    </div>
                    <input type="hidden" id="clientId" name="client_id" value="{{ old('client_id') }}">
                </div>

                <div id="newClientForm" style="display: {{ old('new_client_name') ? 'block' : 'none' }}">
                    <div class="section-divider"></div>
                    <div class="form-group">
                        <label class="form-label">Ism</label>
                        <input type="text" class="form-control @error('new_client_name') is-invalid @enderror" id="newClientName" name="new_client_name" value="{{ old('new_client_name') }}">
                        @error('new_client_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefon raqam</label>
                        <input type="text" class="form-control @error('new_client_phone') is-invalid @enderror" id="newClientPhone" name="new_client_phone" value="{{ old('new_client_phone') }}">
                        @error('new_client_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Izoh</label>
                        <textarea class="form-control @error('new_client_desc') is-invalid @enderror" id="newClientDesc" name="new_client_desc" rows="2">{{ old('new_client_desc') }}</textarea>
                        @error('new_client_desc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-success" id="lockClientFormBtn">✓ Mijozni saqlash</button>
                </div>
            </div>
        </div>

        {{-- Services & Payment --}}
        @include('admin.document.partials.service_addons_payment')

        {{-- Description --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title">Qo'shimcha ma'lumot</div>
                <div class="form-group">
                    <label class="form-label">Izoh</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-3 mt-3" id="mainSaveBtn">💾 Hujjatni saqlash</button>
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
                    html = '<div class="p-2 text-muted">Topilmadi</div><div class="new-client-btn">➕ Yangi mijoz yaratish</div>';
                } else {
                    res.forEach(item => {
                        html += `<div class="result-item" data-id="${item.id}" data-phone="${item.phone_number}" data-name="${item.name}">📞 ${item.phone_number} — ${item.name}</div>`;
                    });
                    html += '<div class="new-client-btn mt-1">➕ Yangi mijoz yaratish</div>';
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
