@php
    $templatePackage = $templatePackage ?? null;
    $initialItems = old('items_payload');

    if (is_string($initialItems)) {
        $decodedItems = json_decode($initialItems, true);
        $initialItems = is_array($decodedItems) ? $decodedItems : [];
    }

    if (!is_array($initialItems)) {
        $initialItems = $templatePackage
            ? $templatePackage->items->map(fn ($item) => \App\Support\PackageTemplateSupport::buildItemPayload($item))->all()
            : [];
    }

    $initialPackageState = [
        'name' => old('name', $templatePackage?->name),
        'product_code' => old('product_code', $templatePackage?->product_code),
        'highlight' => old('highlight', $templatePackage?->highlight),
        'description' => old('description', $templatePackage?->description),
        'promo_price' => old('promo_price', $templatePackage?->promo_price),
        'standard_price' => old('standard_price', $templatePackage?->standard_price ?: $templatePackage?->promo_price),
        'express_price' => old('express_price', $templatePackage?->express_price ?: $templatePackage?->promo_price),
        'standard_deadline_days' => old('standard_deadline_days', $templatePackage?->standard_deadline_days),
        'express_deadline_days' => old('express_deadline_days', $templatePackage?->express_deadline_days),
        'margin_percent' => old('margin_percent', $templatePackage?->margin_percent ?? 0),
        'delivery_type' => old('delivery_type', $templatePackage?->delivery_type ?? 'pickup'),
        'sort_order' => old('sort_order', $templatePackage?->sort_order ?? 0),
        'is_active' => old('is_active', $templatePackage?->is_active ?? true),
        'is_sellable' => old('is_sellable', $templatePackage?->is_sellable ?? true),
        'filial_ids' => old('filial_ids', $templatePackage?->packageFilials?->where('is_available', true)->pluck('filial_id')->all() ?? []),
        'additional_addon_ids' => old('additional_addon_ids', $templatePackage?->packageAddons?->where('is_active', true)->where('is_included', false)->pluck('service_addon_id')->all() ?? []),
        'base_price' => old('base_price', $templatePackage?->base_price ?? 0),
        'items' => $initialItems,
    ];

    $itemErrors = collect($errors->getMessages())
        ->filter(fn ($messages, $key) => str_starts_with($key, 'items_payload'))
        ->flatten()
        ->values();
@endphp

<div class="template-builder">
    <div class="template-builder__main">
        <div class="template-builder__card">
            <div class="template-builder__section-head">
                <div>
                    <h3>Asosiy ma'lumot</h3>
                    <p>Paketning nomi, afzalligi va qisqa tavsifini kiriting.</p>
                </div>
            </div>

            <div class="template-grid template-grid--double">
                <label class="field">
                    <span class="field__label">Paket nomi</span>
                    <input class="field__control" type="text" name="name" value="{{ $initialPackageState['name'] }}" required>
                    @error('name')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field__label">Highlight</span>
                    <input class="field__control" type="text" name="highlight" value="{{ $initialPackageState['highlight'] }}" placeholder="Masalan: Kompleks paket">
                    @error('highlight')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <label class="field">
                <span class="field__label">Paket tavsifi</span>
                <textarea class="field__control field__control--textarea" name="description" rows="4" placeholder="Paket nimasi bilan qulay ekanini yozing">{{ $initialPackageState['description'] }}</textarea>
                @error('description')
                    <span class="field__error">{{ $message }}</span>
                @enderror
            </label>

            <div class="template-grid template-grid--double mt-3">
                <label class="field">
                    <span class="field__label">Mahsulot kodi</span>
                    <input class="field__control" type="text" name="product_code" value="{{ $initialPackageState['product_code'] }}" placeholder="PKG-STUDENT-001">
                    @error('product_code')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Default delivery turi</span>
                    <select class="field__control" name="delivery_type">
                        <option value="pickup" @selected($initialPackageState['delivery_type'] === 'pickup')>Filialdan olib ketish</option>
                        <option value="courier" @selected($initialPackageState['delivery_type'] === 'courier')>Kuryer</option>
                        <option value="digital" @selected($initialPackageState['delivery_type'] === 'digital')>Digital</option>
                        <option value="branch" @selected($initialPackageState['delivery_type'] === 'branch')>Boshqa filialga</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="template-builder__card">
            <div class="template-builder__section-head">
                <div>
                    <h3>Sotiladigan mahsulot sozlamalari</h3>
                    <p>Standard va express variantlar buyurtma ochilganda serverda qayta tekshiriladi.</p>
                </div>
            </div>
            <div class="template-grid template-grid--double">
                <label class="field"><span class="field__label">Standart narx</span><input class="field__control" type="number" min="0" step="0.01" name="standard_price" value="{{ $initialPackageState['standard_price'] }}" required></label>
                <label class="field"><span class="field__label">Express narx</span><input class="field__control" type="number" min="0" step="0.01" name="express_price" value="{{ $initialPackageState['express_price'] }}" required></label>
                <label class="field"><span class="field__label">Standart muddat (kun)</span><input class="field__control" type="number" min="0" name="standard_deadline_days" value="{{ $initialPackageState['standard_deadline_days'] }}"></label>
                <label class="field"><span class="field__label">Express muddat (kun)</span><input class="field__control" type="number" min="0" name="express_deadline_days" value="{{ $initialPackageState['express_deadline_days'] }}"></label>
                <label class="field"><span class="field__label">Maqsadli foyda marjasi (%)</span><input class="field__control" type="number" min="0" max="100" step="0.01" name="margin_percent" value="{{ $initialPackageState['margin_percent'] }}" required></label>
            </div>
            <div class="mt-4">
                <div class="field__label mb-2">Qaysi filiallarda sotiladi?</div>
                <div class="row g-2">
                    @foreach($filials as $filial)
                        <div class="col-md-4"><label class="toggle-field border rounded p-2 w-100 mb-0"><input type="checkbox" name="filial_ids[]" value="{{ $filial->id }}" @checked(in_array($filial->id, $initialPackageState['filial_ids'], true))><span>{{ $filial->name }} @if($filial->code)<small class="text-muted">({{ $filial->code }})</small>@endif</span></label></div>
                    @endforeach
                </div>
                <small class="text-muted">Hech biri tanlanmasa paket barcha filiallar uchun ochiq bo‘ladi.</small>
            </div>
            <div class="mt-4">
                <div class="field__label mb-2">Qo‘shimcha xizmatlar</div>
                <div class="row g-2">
                    @foreach($serviceAddons as $addon)
                        <div class="col-md-6"><label class="toggle-field border rounded p-2 w-100 mb-0"><input type="checkbox" name="additional_addon_ids[]" value="{{ $addon->id }}" @checked(in_array($addon->id, $initialPackageState['additional_addon_ids'], true))><span>{{ $addon->name }} <small class="text-muted">({{ number_format($addon->price, 0, ',', ' ') }} so‘m)</small></span></label></div>
                    @endforeach
                </div>
                @error('additional_addon_ids')<span class="field__error">{{ $message }}</span>@enderror
            </div>
            <label class="toggle-field mt-4 mb-0"><input type="checkbox" name="is_sellable" value="1" @checked($initialPackageState['is_sellable'])><span>Hozir sotuvda ko‘rinsin</span></label>
        </div>

        <div class="template-builder__card">
            <div class="template-builder__section-head">
                <div>
                    <h3>Paket tarkibi</h3>
                    <p>Har bir element alohida hujjat/xizmat konfiguratsiyasi bo'ladi. Employee paketni tanlasa shu elementlar bo'yicha wizardlar avtomatik hosil bo'ladi.</p>
                </div>
                <button type="button" class="btn-primary-strong" id="addPackageItemButton">+ Element qo'shish</button>
            </div>

            @if ($itemErrors->isNotEmpty())
                <div class="alert alert-danger">
                    @foreach ($itemErrors as $itemError)
                        <div>{{ $itemError }}</div>
                    @endforeach
                </div>
            @endif

            <div id="packageItemBuilderList" class="package-item-builder-list"></div>
            <input type="hidden" name="items_payload" id="itemsPayloadInput" value="{{ json_encode($initialItems) }}">
            @error('items_payload')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <aside class="template-builder__aside">
        <div class="template-builder__card template-builder__card--sticky">
            <div class="template-builder__section-head">
                <div>
                    <h3>Narx va preview</h3>
                    <p>Paketning oddiy jami va fix narxi shu yerda ko'rinadi.</p>
                </div>
            </div>

            <div class="price-stack">
                <div class="price-chip">
                    <span>Umumiy summa</span>
                    <strong id="basePriceValue">0 so'm</strong>
                </div>
                <div class="price-chip price-chip--accent">
                    <span>Fix paket narxi</span>
                    <strong id="promoPricePreview">0 so'm</strong>
                </div>
                <div class="price-chip price-chip--saving">
                    <span>Tejaladi</span>
                    <strong id="savingValue">0 so'm</strong>
                </div>
            </div>

            <div class="template-grid">
                <label class="field">
                    <span class="field__label">Eski promo/fix narx (ixtiyoriy)</span>
                    <input class="field__control" type="number" min="0" name="promo_price" id="promoPriceInput" value="{{ $initialPackageState['promo_price'] }}">
                    @error('promo_price')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field__label">Sort order</span>
                    <input class="field__control" type="number" min="0" name="sort_order" value="{{ $initialPackageState['sort_order'] }}">
                    @error('sort_order')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <label class="toggle-field">
                <input type="checkbox" name="is_active" value="1" @checked($initialPackageState['is_active'])>
                <span>Shablon aktiv bo'lsin</span>
            </label>

            <div class="preview-panel">
                <div class="preview-panel__head">
                    <h4>Paket elementlari</h4>
                    <span id="includedItemsCount">0 item</span>
                </div>
                <div id="includedItemsPreview" class="preview-list"></div>
            </div>

            <div class="form-actions">
                <a href="{{ route('superadmin.template_package.index') }}" class="btn-secondary-soft">Bekor qilish</a>
                <button type="submit" class="btn-primary-strong">Saqlash</button>
            </div>
        </div>
    </aside>
</div>

<script>
    window.packageBuilderData = {
        initial: @json($initialPackageState),
        documentTypes: @json($documentTypes),
        directions: @json($directions),
        documentAddons: @json($documentAddons),
        directionAddons: @json($directionAddons),
        serviceAddons: @json($serviceAddons),
        services: @json($services),
        apostilStatics: @json($apostilStatics),
        consuls: @json($consuls),
        consulateTypes: @json($consulateTypes)
    };
</script>
