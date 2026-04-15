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
        'highlight' => old('highlight', $templatePackage?->highlight),
        'description' => old('description', $templatePackage?->description),
        'promo_price' => old('promo_price', $templatePackage?->promo_price),
        'sort_order' => old('sort_order', $templatePackage?->sort_order ?? 0),
        'is_active' => old('is_active', $templatePackage?->is_active ?? true),
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
                    <span class="field__label">Fix narx</span>
                    <input class="field__control" type="number" min="0" name="promo_price" id="promoPriceInput" value="{{ $initialPackageState['promo_price'] }}" required>
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
