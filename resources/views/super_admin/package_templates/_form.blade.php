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

<div class="builder-guide" aria-label="Shablon yaratish qadamlari">
    <div class="builder-guide__copy">
        <span class="builder-guide__eyebrow">Paket yaratish oqimi</span>
        <strong>4 ta oddiy qadamda sotuvga tayyor shablon yarating</strong>
        <small>Avval paketni nomlang, keyin tarkib va narxini belgilang.</small>
    </div>
    <ol class="builder-guide__steps">
        <li class="is-current"><b>1</b><span>Asosiy ma’lumot</span></li>
        <li><b>2</b><span>Narx va muddat</span></li>
        <li><b>3</b><span>Paket tarkibi</span></li>
        <li><b>4</b><span>Sotuv sozlamalari</span></li>
    </ol>
</div>

<div class="template-builder">
    <div class="template-builder__main">
        <section class="template-builder__card builder-section">
            <div class="template-builder__section-head">
                <div>
                    <span class="builder-section__number">1-qadam</span>
                    <h3>Paketni tanishtiring</h3>
                    <p>Xodim va mijoz paketni bir qarashda tushunishi uchun nom, qisqa belgi va tavsif kiriting.</p>
                </div>
                <i class='bx bx-purchase-tag builder-section__icon'></i>
            </div>

            <div class="template-grid template-grid--double">
                <label class="field">
                    <span class="field__label">Paket nomi <b class="field__required">*</b></span>
                    <input class="field__control" type="text" name="name" value="{{ $initialPackageState['name'] }}" placeholder="Masalan: Talaba uchun apostil paketi" required>
                    @error('name')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Qisqa belgi</span>
                    <input class="field__control" type="text" name="highlight" value="{{ $initialPackageState['highlight'] }}" placeholder="Masalan: Tez boshlash">
                    <small class="field__hint">Kartochkada paket nomining yonida chiqadi.</small>
                    @error('highlight')<span class="field__error">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="field mt-3">
                <span class="field__label">Paket tavsifi</span>
                <textarea class="field__control field__control--textarea" name="description" rows="4" placeholder="Bu paket kimlar uchun va qanday qulaylik beradi?">{{ $initialPackageState['description'] }}</textarea>
                <small class="field__hint">Xodim paketni tanlashidan oldin aynan shu izohni ko‘radi.</small>
                @error('description')<span class="field__error">{{ $message }}</span>@enderror
            </label>
        </section>

        <section class="template-builder__card builder-section">
            <div class="template-builder__section-head">
                <div>
                    <span class="builder-section__number">2-qadam</span>
                    <h3>Narx va bajarish muddati</h3>
                    <p>Bu narxlar paket mijozga sotilganda ishlatiladi. Tarkib qiymati o‘ng tomondagi preview’da avtomatik hisoblanadi.</p>
                </div>
                <i class='bx bx-calculator builder-section__icon'></i>
            </div>

            <div class="template-grid template-grid--double">
                <label class="field">
                    <span class="field__label">Standart narx <b class="field__required">*</b></span>
                    <div class="field__input-wrap"><input class="field__control" id="standardPriceInput" type="number" min="0" step="0.01" name="standard_price" value="{{ $initialPackageState['standard_price'] }}" required><span>so‘m</span></div>
                    <small class="field__hint">Oddiy topshirishdagi mijoz narxi.</small>
                    @error('standard_price')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Express narx <b class="field__required">*</b></span>
                    <div class="field__input-wrap"><input class="field__control" type="number" min="0" step="0.01" name="express_price" value="{{ $initialPackageState['express_price'] }}" required><span>so‘m</span></div>
                    <small class="field__hint">Tezkor topshirish uchun narx.</small>
                    @error('express_price')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Standart muddat</span>
                    <div class="field__input-wrap"><input class="field__control" type="number" min="0" name="standard_deadline_days" value="{{ $initialPackageState['standard_deadline_days'] }}"><span>kun</span></div>
                    @error('standard_deadline_days')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Express muddat</span>
                    <div class="field__input-wrap"><input class="field__control" type="number" min="0" name="express_deadline_days" value="{{ $initialPackageState['express_deadline_days'] }}"><span>kun</span></div>
                    @error('express_deadline_days')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Maqsadli foyda marjasi</span>
                    <div class="field__input-wrap"><input class="field__control" type="number" min="0" max="100" step="0.01" name="margin_percent" value="{{ $initialPackageState['margin_percent'] }}" required><span>%</span></div>
                    <small class="field__hint">Ichki rejalashtirish uchun ko‘rsatkich.</small>
                    @error('margin_percent')<span class="field__error">{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        <section class="template-builder__card builder-section">
            <div class="template-builder__section-head">
                <div>
                    <span class="builder-section__number">3-qadam</span>
                    <h3>Paket tarkibini yig‘ing</h3>
                    <p>Har bir element alohida hujjat va xizmat konfiguratsiyasi. Xodim paketni tanlaganda shu tarkib avtomatik tayyorlanadi.</p>
                </div>
                <button type="button" class="btn-primary-strong" id="addPackageItemButton"><i class='bx bx-plus'></i> Element qo‘shish</button>
            </div>

            <div class="builder-callout">
                <i class='bx bx-bulb'></i>
                <span>Avval <strong>hujjat turi</strong> va <strong>xizmat</strong>ni tanlang. So‘ng apostil yoki legalizatsiya kerak bo‘lsa, jarayon turini bosing va mos maydonlarni to‘ldiring.</span>
            </div>

            @if ($itemErrors->isNotEmpty())
                <div class="alert alert-danger mt-3">
                    @foreach ($itemErrors as $itemError)<div>{{ $itemError }}</div>@endforeach
                </div>
            @endif

            <div id="packageItemBuilderList" class="package-item-builder-list"></div>
            <input type="hidden" name="items_payload" id="itemsPayloadInput" value="{{ json_encode($initialItems) }}">
            @error('items_payload')<span class="field__error">{{ $message }}</span>@enderror
        </section>

        <section class="template-builder__card builder-section">
            <div class="template-builder__section-head">
                <div>
                    <span class="builder-section__number">4-qadam</span>
                    <h3>Sotuv sozlamalari</h3>
                    <p>Paket qayerda sotilishi, yetkazib berish usuli va mijoz tanlashi mumkin bo‘lgan qo‘shimcha xizmatlarni belgilang.</p>
                </div>
                <i class='bx bx-store-alt builder-section__icon'></i>
            </div>

            <div class="template-grid template-grid--double">
                <label class="field">
                    <span class="field__label">Mahsulot kodi</span>
                    <input class="field__control" type="text" name="product_code" value="{{ $initialPackageState['product_code'] }}" placeholder="Bo‘sh qoldirilsa avtomatik yaratiladi">
                    <small class="field__hint">Ichki hisob va tez qidiruv uchun. Ixtiyoriy.</small>
                    @error('product_code')<span class="field__error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span class="field__label">Yetkazib berish usuli</span>
                    <select class="field__control" name="delivery_type">
                        <option value="pickup" @selected($initialPackageState['delivery_type'] === 'pickup')>Filialdan olib ketish</option>
                        <option value="courier" @selected($initialPackageState['delivery_type'] === 'courier')>Kuryer orqali</option>
                        <option value="digital" @selected($initialPackageState['delivery_type'] === 'digital')>Raqamli yuborish</option>
                        <option value="branch" @selected($initialPackageState['delivery_type'] === 'branch')>Boshqa filialga yuborish</option>
                    </select>
                </label>
            </div>

            <div class="builder-choice-group mt-4">
                <div class="builder-choice-group__head"><div><strong>Qaysi filiallarda sotiladi?</strong><span>Hech biri tanlanmasa, paket barcha filiallar uchun ochiq bo‘ladi.</span></div><span class="builder-choice-group__badge">{{ $filials->count() }} ta filial</span></div>
                <div class="builder-choice-grid">
                    @foreach($filials as $filial)
                        <label class="toggle-field builder-choice"><input type="checkbox" name="filial_ids[]" value="{{ $filial->id }}" @checked(in_array($filial->id, $initialPackageState['filial_ids'], true))><span><b>{{ $filial->name }}</b>@if($filial->code)<small>{{ $filial->code }}</small>@endif</span></label>
                    @endforeach
                </div>
            </div>

            <div class="builder-choice-group mt-4">
                <div class="builder-choice-group__head"><div><strong>Mijoz tanlashi mumkin bo‘lgan qo‘shimcha xizmatlar</strong><span>Faqat paket tarkibidagi xizmatlarga tegishli variantlarni belgilang.</span></div><span class="builder-choice-group__badge">Ixtiyoriy</span></div>
                <div class="builder-choice-grid builder-choice-grid--addons">
                    @foreach($serviceAddons as $addon)
                        <label class="toggle-field builder-choice"><input type="checkbox" name="additional_addon_ids[]" value="{{ $addon->id }}" @checked(in_array($addon->id, $initialPackageState['additional_addon_ids'], true))><span><b>{{ $addon->name }}</b><small>{{ number_format($addon->price, 0, ',', ' ') }} so‘m · {{ $addon->deadline }} kun</small></span></label>
                    @endforeach
                </div>
                @error('additional_addon_ids')<span class="field__error">{{ $message }}</span>@enderror
            </div>

            <label class="toggle-field builder-status-toggle mt-4 mb-0"><input type="checkbox" name="is_sellable" value="1" @checked($initialPackageState['is_sellable'])><span><b>Sotuvga ochiq</b><small>Xodimlar hujjat ochayotganda ushbu paketni tanlay oladi.</small></span></label>
        </section>
    </div>

    <aside class="template-builder__aside">
        <section class="template-builder__card template-builder__card--sticky builder-preview">
            <div class="template-builder__section-head">
                <div>
                    <span class="builder-section__number">Live preview</span>
                    <h3>Narx va tarkib nazorati</h3>
                    <p>Elementlarni qo‘shganingiz sari hisob avtomatik yangilanadi.</p>
                </div>
                <i class='bx bx-show builder-section__icon'></i>
            </div>

            <div class="price-stack">
                <div class="price-chip">
                    <span>Tarkib bo‘yicha jami</span>
                    <strong id="basePriceValue">0 so‘m</strong>
                    <small>Tanlangan xizmatlar va qo‘shimchalar</small>
                </div>
                <div class="price-chip price-chip--accent">
                    <span>Mijoz uchun aksiya narxi</span>
                    <strong id="promoPricePreview">0 so‘m</strong>
                    <small>Bo‘sh qoldirilsa standart narx olinadi</small>
                </div>
                <div class="price-chip price-chip--saving">
                    <span>Mijoz tejamkorligi</span>
                    <strong id="savingValue">0 so‘m</strong>
                    <small>Tarkib jami va aksiya narxi orasidagi farq</small>
                </div>
            </div>

            <label class="field mt-4">
                <span class="field__label">Aksiya narxi <em>(ixtiyoriy)</em></span>
                <div class="field__input-wrap"><input class="field__control" type="number" min="0" step="0.01" name="promo_price" id="promoPriceInput" value="{{ $initialPackageState['promo_price'] }}" placeholder="Standart narx ishlatiladi"><span>so‘m</span></div>
                <small class="field__hint">Bo‘sh qoldirsangiz, standart narx avtomatik aksiya narxi sifatida saqlanadi.</small>
                @error('promo_price')<span class="field__error">{{ $message }}</span>@enderror
            </label>

            <div class="template-grid template-grid--double mt-3">
                <label class="field"><span class="field__label">Tartib raqami</span><input class="field__control" type="number" min="0" name="sort_order" value="{{ $initialPackageState['sort_order'] }}"><small class="field__hint">Kichik son avval chiqadi.</small>@error('sort_order')<span class="field__error">{{ $message }}</span>@enderror</label>
                <label class="toggle-field builder-status-toggle builder-status-toggle--compact"><input type="checkbox" name="is_active" value="1" @checked($initialPackageState['is_active'])><span><b>Shablon aktiv</b><small>Nofaol shablon tanlovda ko‘rinmaydi.</small></span></label>
            </div>

            <div class="preview-panel">
                <div class="preview-panel__head"><h4>Paket elementlari</h4><span id="includedItemsCount">0 ta element</span></div>
                <div id="includedItemsPreview" class="preview-list"></div>
            </div>

            <div class="form-actions">
                <a href="{{ route('superadmin.template_package.index') }}" class="btn-secondary-soft">Bekor qilish</a>
                <button type="submit" class="btn-primary-strong"><i class='bx bx-check'></i> Saqlash</button>
            </div>
        </section>
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
