@extends('template')

@section('style')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.package-index-shell {
    font-family: 'Manrope', sans-serif;
    color: #0f172a;
    background:
        radial-gradient(900px 420px at 100% -10%, rgba(37, 99, 235, 0.12), transparent 45%),
        linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
    min-height: 100vh;
    padding-bottom: 48px;
}

.package-index-hero {
    position: relative;
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 18px;
    padding: 28px;
    border-radius: 28px;
    background: linear-gradient(135deg, #0f172a 0%, #0f766e 44%, #2563eb 100%);
    color: #fff;
    overflow: hidden;
    box-shadow: 0 30px 70px rgba(15, 23, 42, 0.22);
}

.package-index-hero::after {
    content: "";
    position: absolute;
    inset: auto -40px -80px auto;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.22), rgba(255,255,255,0));
}

.package-index-hero__eyebrow {
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(226,232,240,0.88);
}

.package-index-hero h1 {
    margin: 10px 0 8px;
    font-size: 2rem;
    font-weight: 800;
}

.package-index-hero p {
    margin: 0;
    max-width: 680px;
    color: rgba(241,245,249,0.92);
}

.package-index-cta {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 0 20px;
    border-radius: 16px;
    background: #fff;
    color: #0f172a;
    font-weight: 800;
    text-decoration: none;
}

.package-index-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.package-index-card {
    display: flex;
    flex-direction: column;
    gap: 18px;
    padding: 22px;
    border-radius: 24px;
    background: rgba(255,255,255,0.94);
    border: 1px solid rgba(148, 163, 184, 0.2);
    box-shadow: 0 20px 44px rgba(15, 23, 42, 0.08);
}

.package-index-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
}

.package-index-card__badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 6px 10px;
    background: rgba(14,165,164,0.1);
    color: #0f766e;
    font-size: 12px;
    font-weight: 800;
}

.package-index-card__status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 800;
}

.package-index-card__status.is-active {
    background: rgba(220,252,231,0.9);
    color: #166534;
}

.package-index-card__status.is-inactive {
    background: rgba(241,245,249,0.95);
    color: #475569;
}

.package-index-card h3 {
    margin: 10px 0 6px;
    font-size: 1.1rem;
    font-weight: 800;
}

.package-index-card p {
    margin: 0;
    color: #64748b;
}

.package-index-price {
    display: flex;
    align-items: baseline;
    gap: 10px;
}

.package-index-price strong {
    font-size: 1.4rem;
    font-weight: 800;
}

.package-index-price span {
    color: #94a3b8;
    text-decoration: line-through;
    font-weight: 700;
}

.package-index-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.package-index-meta span,
.package-index-items span {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 6px 10px;
    background: rgba(15,23,42,0.04);
    color: #334155;
    font-size: 12px;
    font-weight: 700;
}

.package-index-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.package-index-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: auto;
}

.package-index-actions__group {
    display: flex;
    gap: 10px;
}

.package-index-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 16px;
    border-radius: 14px;
    border: 0;
    font-weight: 800;
    text-decoration: none;
}

.package-index-btn--primary {
    background: linear-gradient(135deg, #0f766e, #2563eb);
    color: #fff;
}

.package-index-btn--soft {
    background: rgba(241,245,249,0.96);
    color: #334155;
}

.package-index-btn--danger {
    background: rgba(254,226,226,0.9);
    color: #b91c1c;
}

.package-index-empty {
    margin-top: 24px;
    padding: 28px;
    border-radius: 24px;
    border: 1px dashed rgba(148, 163, 184, 0.45);
    background: rgba(255,255,255,0.9);
    text-align: center;
    color: #64748b;
}

@media (max-width: 767px) {
    .package-index-hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .package-index-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .package-index-actions__group {
        width: 100%;
    }

    .package-index-btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')
@php
    $payloadMap = collect($templatePayloads)->keyBy('id');
@endphp
<div class="page-wrapper package-index-shell">
    <div class="page-content">
        <div class="package-index-hero">
            <div>
                <span class="package-index-hero__eyebrow">Package Templates</span>
                <h1>Shablonlar markazi</h1>
                <p>Xodimlar hujjat kiritayotganda tayyor paketni tanlashi uchun promoli, tavsifli va vizual tushunarli shablonlar shu yerda boshqariladi.</p>
            </div>
            <a href="{{ route('superadmin.template_package.create') }}" class="package-index-cta">Yangi shablon</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-4">{{ session('success') }}</div>
        @endif

        @if ($templates->isEmpty())
            <div class="package-index-empty">
                Hozircha shablon yo'q. Birinchi paketni yaratib, employee va filial adminlar uchun tayyor konfiguratsiya taqdim qiling.
            </div>
        @else
            <div class="package-index-grid">
                @foreach ($templates as $template)
                    @php
                        $payload = $payloadMap->get($template->id);
                        $items = collect($payload['items'] ?? [])->pluck('summary_name')->take(4)->all();
                    @endphp
                    <div class="package-index-card">
                        <div class="package-index-card__top">
                            <span class="package-index-card__badge">{{ $template->highlight ?: 'Paket' }}</span>
                            <span class="package-index-card__status {{ $template->is_active ? 'is-active' : 'is-inactive' }}">
                                {{ $template->is_active ? 'Aktiv' : 'Nofaol' }}
                            </span>
                        </div>

                        <div>
                            <h3>{{ $template->name }}</h3>
                            <p>{{ $template->description ?: "Tayyor konfiguratsiya va paket tavsifi hali kiritilmagan." }}</p>
                        </div>

                        <div class="package-index-price">
                            <span>{{ number_format($payload['base_price'] ?? 0, 0, '', ' ') }} so'm</span>
                            <strong>{{ number_format($payload['promo_price'] ?? 0, 0, '', ' ') }} so'm</strong>
                        </div>

                        <div class="package-index-meta">
                            <span>{{ $payload['item_count'] ?? 0 }} ta element</span>
                            <span>{{ number_format($payload['savings_amount'] ?? 0, 0, '', ' ') }} so'm tejash</span>
                            <span>Sort: {{ $template->sort_order }}</span>
                        </div>

                        @if (!empty($items))
                            <div class="package-index-items">
                                @foreach ($items as $item)
                                    <span>{{ $item }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="package-index-actions">
                            <a href="{{ route('superadmin.template_package.edit', $template) }}" class="package-index-btn package-index-btn--primary">Tahrirlash</a>
                            <div class="package-index-actions__group">
                                <a href="{{ route('superadmin.template_package.edit', $template) }}" class="package-index-btn package-index-btn--soft">Preview</a>
                                <form action="{{ route('superadmin.template_package.destroy', $template) }}" method="POST" onsubmit="return confirm('Shablonni o\'chirasizmi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="package-index-btn package-index-btn--danger">O'chirish</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
