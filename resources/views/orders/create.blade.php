@extends('template')

@section('style')
<style>
    .order-create-page { min-height: calc(100vh - 80px); padding: 28px; background: #f5f7fb; }
    .order-create-card { max-width: 980px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 8px 24px rgba(15,23,42,.05); overflow: hidden; }
    .order-create-head { padding: 22px 24px; border-bottom: 1px solid #eef2f7; }
    .order-create-head h1 { margin: 0; color: #102a56; font-size: 26px; font-weight: 800; }
    .order-create-head p { margin: 7px 0 0; color: #64748b; }
    .order-create-body { padding: 24px; }
    .client-search { display: flex; gap: 8px; margin-bottom: 10px; }
    .client-search input { flex: 1; }
    .client-list { max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; }
    .client-option { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 11px 13px; border-bottom: 1px solid #eef2f7; cursor: pointer; }
    .client-option:last-child { border-bottom: 0; }
    .client-option:hover { background: #eff6ff; }
    .client-option input { margin-right: 8px; }
    .client-option small { color: #64748b; }
    .form-label { font-weight: 700; color: #334155; }
    .product-card { border: 1px solid #dbeafe; border-radius: 14px; padding: 14px; background: linear-gradient(135deg,#f8fbff,#eff6ff); }
    .product-card__head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
    .product-card__head strong { color:#102a56; }
    .product-card__meta { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .product-card__meta span { background:#fff; border-radius:999px; padding:5px 9px; font-size:12px; color:#475569; }
    .product-card__services { margin:10px 0 0; padding-left:20px; color:#475569; font-size:13px; }
</style>
@endsection

@section('body')
<div class="order-create-page">
    <div class="order-create-card">
        <div class="order-create-head">
            <h1>Yangi Order / Case</h1>
            <p>Mijoz, sotiladigan paket, mas’ul xodim, deadline va delivery turini bitta oqimda belgilang.</p>
        </div>
        <div class="order-create-body">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="GET" action="{{ route('orders.create') }}" class="client-search">
                <input class="form-control" name="q" value="{{ $search }}" placeholder="Mijoz nomi yoki telefoni bo‘yicha qidiring">
                <button class="btn btn-outline-primary" type="submit">Qidirish</button>
            </form>
            <form method="POST" action="{{ route('orders.store') }}" id="orderCreateForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Mijoz</label>
                    <div class="client-list">
                        @forelse($clients as $client)
                            <label class="client-option">
                                <span><input type="radio" name="client_id" value="{{ $client->id }}" @checked((string) old('client_id') === (string) $client->id || (!$errors->any() && $loop->first && !$search)) required><strong>{{ $client->name }}</strong> <small>{{ $client->phone_number }}</small></span>
                                <small>{{ $client->filial?->name ?: 'Filial aniqlanmagan' }}</small>
                            </label>
                        @empty
                            <div class="p-3 text-muted">Mijoz topilmadi. Avval mijoz yarating yoki qidiruvni o‘zgartiring.</div>
                        @endforelse
                    </div>
                    <small class="text-muted">Ro‘yxatda 200 tagacha mos mijoz ko‘rsatiladi.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Order nomi</label><input name="title" class="form-control" value="{{ old('title') }}" placeholder="Masalan: Germaniya uchun hujjatlar"></div>
                    <div class="col-md-4"><label class="form-label">Ustuvorlik</label><select name="priority" class="form-select"><option value="low" @selected(old('priority') === 'low')>Past</option><option value="normal" @selected(old('priority', 'normal') === 'normal')>Oddiy</option><option value="high" @selected(old('priority') === 'high')>Yuqori</option><option value="urgent" @selected(old('priority') === 'urgent')>Shoshilinch</option></select></div>
                    @if(auth()->user()->filial_id === null)<div class="col-md-6"><label class="form-label">Filial</label><select name="filial_id" id="filialSelect" class="form-select" required><option value="">Filial tanlang</option>@foreach($filials as $filial)<option value="{{ $filial->id }}" @selected((string) old('filial_id', $selectedFilialId) === (string) $filial->id)>{{ $filial->name }}</option>@endforeach</select></div>@endif
                    <div class="col-md-6"><label class="form-label">Umumiy deadline</label><input type="datetime-local" name="promised_at" id="promisedAtInput" class="form-control" value="{{ old('promised_at') }}"></div>
                    <div class="col-md-6"><label class="form-label">Mas’ul xodim</label><select name="responsible_user_id" class="form-select"><option value="">Buyurtmani ochgan xodim</option>@foreach($responsibles as $responsible)<option value="{{ $responsible->id }}" @selected((string) old('responsible_user_id') === (string) $responsible->id)>{{ $responsible->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Yetkazib berish turi</label><select name="delivery_type" class="form-select"><option value="pickup">Filialdan olib ketish</option><option value="courier">Kuryer</option><option value="digital">Digital</option><option value="branch">Boshqa filialga</option></select></div>
                    <div class="col-12"><label class="form-label">Mijoz manbasi</label><input name="customer_source" class="form-control" value="{{ old('customer_source', old('source')) }}" placeholder="Ofis, Instagram, Telegram, hamkor..."></div>
                    <div class="col-12">
                        <label class="form-label">Sotiladigan paket (ixtiyoriy)</label>
                        <select name="package_template_id" id="packageSelect" class="form-select">
                            <option value="">Paket tanlamasdan oddiy order</option>
                            @foreach($packageProducts as $product)
                                <option value="{{ $product['id'] }}" @selected((string) old('package_template_id') === (string) $product['id'])>{{ $product['code'] }} — {{ $product['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2"><span class="form-label d-block">Paket varianti</span><label class="me-3"><input type="radio" name="package_variant" value="standard" @checked(old('package_variant', 'standard') === 'standard')> Standard</label><label><input type="radio" name="package_variant" value="express" @checked(old('package_variant') === 'express')> Express</label></div>
                        <div id="packagePreview" class="product-card mt-3 d-none"></div>
                        <div id="packageAddonBox" class="mt-3 d-none"></div>
                    </div>
                    <div class="col-12"><label class="form-label">Izoh</label><textarea name="description" class="form-control" rows="3" placeholder="Buyurtma bo‘yicha umumiy izoh">{{ old('description') }}</textarea></div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('orders.index') }}" class="btn btn-light">Bekor qilish</a><button class="btn btn-primary" type="submit">Order yaratish</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    const catalogByFilial = @json($packageProductsByFilial);
    const packageSelect = document.getElementById('packageSelect');
    const filialSelect = document.getElementById('filialSelect');
    const preview = document.getElementById('packagePreview');
    const addonBox = document.getElementById('packageAddonBox');
    const deadlineInput = document.getElementById('promisedAtInput');
    const selectedId = @json(old('package_template_id'));
    const initialCatalog = @json($packageProducts);

    const money = value => Number(value || 0).toLocaleString('uz-UZ') + ' so\'m';
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const products = () => {
        const key = filialSelect?.value || '{{ $selectedFilialId }}';
        return key ? (catalogByFilial[key] || []) : initialCatalog;
    };
    const productById = id => products().find(product => String(product.id) === String(id));

    function renderOptions() {
        const current = packageSelect.value || selectedId;
        packageSelect.innerHTML = '<option value="">Paket tanlamasdan oddiy order</option>';
        products().forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.code} — ${product.name}`;
            option.selected = String(product.id) === String(current);
            packageSelect.appendChild(option);
        });
        renderQuote();
    }

    function renderQuote() {
        const product = productById(packageSelect.value);
        if (!product) {
            preview.classList.add('d-none');
            addonBox.classList.add('d-none');
            return;
        }
        const variant = document.querySelector('input[name="package_variant"]:checked')?.value || 'standard';
        const quote = product[variant] || product.standard;
        const included = (product.included_services || []).map(item => `<li>${escapeHtml(item.name)}</li>`).join('');
        preview.innerHTML = `<div class="product-card__head"><strong>${escapeHtml(product.name)}</strong><span class="badge bg-primary">${escapeHtml(product.code)}</span></div><div class="product-card__meta"><span>Standard: ${money(product.standard.price)}</span><span>Express: ${money(product.express.price)}</span><span>Muddat: ${quote.deadline_days} kun</span><span>Marja: ${product.margin_percent}%</span></div><ul class="product-card__services">${included || '<li>Tarkib keyin belgilanadi</li>'}</ul><div class="mt-2 fw-bold">Tanlangan: ${variant === 'express' ? 'Express' : 'Standard'} — ${money(quote.price)}</div>`;
        preview.classList.remove('d-none');
        const additional = product.additional_services || [];
        addonBox.innerHTML = additional.length ? '<div class="form-label">Qo‘shimcha xizmatlar</div>' + additional.map(addon => `<label class="d-block border rounded p-2 mb-2"><input type="checkbox" name="package_addon_ids[]" value="${addon.id}"> ${escapeHtml(addon.name)} — ${money(addon.price)}</label>`).join('') : '';
        addonBox.classList.toggle('d-none', !additional.length);
        if (!deadlineInput.value && quote.deadline_days) {
            const date = new Date(); date.setDate(date.getDate() + Number(quote.deadline_days));
            deadlineInput.value = date.toISOString().slice(0, 16);
        }
    }

    packageSelect.addEventListener('change', renderQuote);
    document.addEventListener('change', event => { if (event.target.name === 'package_variant') renderQuote(); });
    filialSelect?.addEventListener('change', renderOptions);
    renderOptions();
})();
</script>
@endsection
