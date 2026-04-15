<div class="bundle-picker" id="documentPackageBundlePicker">
    <div class="bundle-picker__header">
        <span class="bundle-picker__eyebrow">Smart Paketlar</span>
        <h5>Kompleks paketdan boshlang</h5>
        <p>Paket tanlansa ichidagi barcha xizmatlar bo'yicha wizardlar avtomatik yaratiladi va topdagi jami narx fix paket bo'yicha hisoblanadi.</p>
    </div>

    @if (!empty($packageTemplates))
        <div class="bundle-picker__grid">
            @foreach ($packageTemplates as $package)
                @php
                    $items = collect($package['items'] ?? [])->pluck('summary_name')->take(3)->all();
                @endphp
                <button type="button" class="bundle-card" data-package-id="{{ $package['id'] }}">
                    <span class="bundle-card__badge">{{ $package['highlight'] ?: 'Paket' }}</span>
                    <h6>{{ $package['name'] }}</h6>
                    <p>{{ \Illuminate\Support\Str::limit($package['description'] ?: "Bir nechta xizmatdan tashkil topgan kompleks paket.", 120) }}</p>

                    <div class="bundle-card__price">
                        <span>{{ number_format($package['base_price'] ?? 0, 0, '', ' ') }} so'm</span>
                        <strong>{{ number_format($package['promo_price'] ?? 0, 0, '', ' ') }} so'm</strong>
                    </div>

                    <div class="bundle-card__chips">
                        <span>{{ $package['item_count'] ?? 0 }} ta xizmat</span>
                        <span>{{ number_format($package['savings_amount'] ?? 0, 0, '', ' ') }} so'm tejash</span>
                    </div>

                    @if (!empty($items))
                        <div class="bundle-card__items">
                            @foreach ($items as $item)
                                <span>{{ $item }}</span>
                            @endforeach
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    @else
        <div class="bundle-picker__empty">
            Hozircha aktiv paketlar yo'q. Superadmin paket yaratgach shu yerda ko'rinadi.
        </div>
    @endif

    <div class="bundle-picker__status">
        <div>
            <strong class="bundle-picker__title">Paket tanlanmagan</strong>
            <span class="bundle-picker__note">Paket tanlansa wizardlar shu paket tarkibi bo'yicha avtomatik qo'shiladi.</span>
        </div>
        <button type="button" class="btn btn-light btn-sm bundle-picker__clear d-none">Paketni bekor qilish</button>
    </div>
</div>
