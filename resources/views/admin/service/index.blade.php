@extends('template')

@section('style')
<style>
    .services-page { padding: 26px; color: #17233d; }
    .services-hero { display:flex; align-items:center; justify-content:space-between; gap:24px; padding:26px 30px; border:1px solid #dce6f4; border-radius:20px; background:linear-gradient(120deg,#fff 0%,#f2f7ff 100%); box-shadow:0 8px 24px rgba(32,73,132,.08); }
    .services-eyebrow { margin:0 0 5px; color:#557198; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .services-hero h1 { margin:0; font-size:1.55rem; font-weight:800; color:#102d5c; }
    .services-hero p { margin:7px 0 0; color:#60708b; max-width:660px; }
    .services-new { display:inline-flex; align-items:center; gap:8px; flex-shrink:0; border:0; border-radius:11px; padding:12px 17px; background:#e51f4b; color:#fff!important; font-weight:700; text-decoration:none; box-shadow:0 7px 16px rgba(229,31,75,.25); }
    .services-new:hover { background:#c9153d; transform:translateY(-1px); }
    .services-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:15px; margin:20px 0; }
    .services-stat { padding:17px 19px; border:1px solid #dfe8f5; border-radius:15px; background:#fff; box-shadow:0 5px 16px rgba(31,63,110,.05); }
    .services-stat span { display:block; color:#73809a; font-size:.78rem; font-weight:700; }
    .services-stat strong { display:block; margin-top:3px; color:#12356d; font-size:1.45rem; line-height:1.2; }
    .services-catalogue { padding:22px; border:1px solid #dfe8f5; border-radius:19px; background:#fff; box-shadow:0 7px 20px rgba(31,63,110,.06); }
    .services-catalogue-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; padding:0 2px 17px; border-bottom:1px solid #e9eff8; }
    .services-catalogue-head h2 { margin:0; color:#152e58; font-size:1.06rem; font-weight:800; }
    .services-catalogue-head p { margin:4px 0 0; color:#71809a; font-size:.84rem; }
    .service-list { display:grid; gap:14px; margin-top:17px; }
    .service-card { display:grid; grid-template-columns:minmax(220px,1.45fr) minmax(215px,1fr) auto; gap:20px; align-items:center; padding:19px; border:1px solid #dfe8f5; border-radius:16px; background:#fff; transition:.18s ease; }
    .service-card:hover { border-color:#a9c8f5; box-shadow:0 8px 20px rgba(39,88,155,.08); }
    .service-name { display:flex; gap:12px; align-items:flex-start; }
    .service-icon { display:grid; place-items:center; flex:0 0 41px; width:41px; height:41px; border-radius:12px; color:#e51f4b; background:#fff0f3; font-size:1.25rem; }
    .service-name h3 { margin:0; color:#142f5d; font-size:1rem; font-weight:800; }
    .service-name p { margin:4px 0 0; color:#748099; font-size:.84rem; line-height:1.45; }
    .service-metrics { display:flex; gap:20px; }
    .service-metric span { display:block; color:#7e8ba3; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .service-metric strong { display:block; margin-top:3px; color:#17396e; font-size:.93rem; }
    .service-actions { display:flex; justify-content:flex-end; align-items:center; gap:8px; flex-wrap:wrap; }
    .service-btn { display:inline-flex; align-items:center; gap:5px; min-height:36px; padding:8px 10px; border:1px solid #cfdcf0; border-radius:9px; background:#fff; color:#244a82; font-size:.8rem; font-weight:700; text-decoration:none; }
    .service-btn:hover { border-color:#1d7ce2; background:#f2f8ff; color:#1359b4; }
    .service-btn--add { border-color:#bde3d0; color:#197144; background:#f4fcf7; }
    .service-btn--danger { border-color:#ffd0da; color:#c71e42; background:#fff7f9; }
    .service-addons { grid-column:1 / -1; margin:-2px 0 0 53px; border-top:1px dashed #dbe5f3; }
    .service-addons summary { padding:13px 0 2px; color:#3970b7; cursor:pointer; font-size:.84rem; font-weight:700; list-style:none; }
    .service-addons summary::-webkit-details-marker { display:none; }
    .service-addon-list { display:flex; flex-wrap:wrap; gap:9px; padding:10px 0 2px; }
    .service-addon { display:flex; align-items:center; gap:9px; padding:9px 10px; border:1px solid #e0e8f4; border-radius:10px; background:#f9fbff; }
    .service-addon strong { display:block; color:#314764; font-size:.82rem; }
    .service-addon small { color:#71809a; }
    .service-addon a { color:#1b6ecf; font-size:.8rem; font-weight:700; text-decoration:none; }
    .services-empty { padding:48px 24px; text-align:center; color:#71809a; }
    .services-empty i { display:block; margin-bottom:9px; color:#e51f4b; font-size:2rem; }
    @media (max-width:1050px) { .service-card { grid-template-columns:1fr auto; } .service-metrics { grid-column:1 / 2; } }
    @media (max-width:700px) { .services-page { padding:16px; } .services-hero { align-items:flex-start; flex-direction:column; padding:22px; } .services-new { width:100%; justify-content:center; } .services-stats { grid-template-columns:1fr; } .services-catalogue { padding:15px; } .service-card { grid-template-columns:1fr; gap:14px; } .service-metrics { grid-column:auto; } .service-actions { justify-content:flex-start; } .service-addons { margin-left:0; } }
</style>
@endsection

@section('body')
@php($addonCount = $services->sum(fn ($service) => $service->addons->count()))
<div class="page-wrapper">
    <main class="services-page">
        @if (session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
        @if (session('danger') || session('error'))<div class="alert alert-danger mb-3">{{ session('danger') ?? session('error') }}</div>@endif

        <section class="services-hero">
            <div>
                <p class="services-eyebrow">Xizmatlar katalogi</p>
                <h1>Xizmatlar va qo‘shimcha xizmatlar</h1>
                <p>Tarjima, apostil va boshqa xizmatlarning narxi, bajarilish muddati hamda qo‘shimcha variantlarini bir joyda boshqaring.</p>
            </div>
            <a href="{{ route('superadmin.service.create') }}" class="services-new"><i class='bx bx-plus'></i> Yangi xizmat</a>
        </section>

        <section class="services-stats" aria-label="Xizmatlar statistikasi">
            <article class="services-stat"><span>Asosiy xizmatlar</span><strong>{{ $services->count() }}</strong></article>
            <article class="services-stat"><span>Qo‘shimcha xizmatlar</span><strong>{{ $addonCount }}</strong></article>
            <article class="services-stat"><span>Katalog holati</span><strong>{{ $services->isEmpty() ? 'Bo‘sh' : 'Faol' }}</strong></article>
        </section>

        <section class="services-catalogue">
            <div class="services-catalogue-head">
                <div><h2>Xizmatlar ro‘yxati</h2><p>Qo‘shimcha xizmatlar asosiy xizmat ichida ochiladi.</p></div>
                <span class="text-muted small">{{ $services->count() }} ta xizmat</span>
            </div>

            <div class="service-list">
                @forelse ($services as $service)
                    <article class="service-card">
                        <div class="service-name">
                            <span class="service-icon"><i class='bx bx-briefcase-alt-2'></i></span>
                            <div><h3>{{ $service->name }}</h3><p>{{ $service->description ?: 'Xizmat uchun izoh kiritilmagan.' }}</p></div>
                        </div>
                        <div class="service-metrics">
                            <div class="service-metric"><span>Narxi</span><strong>{{ number_format((float) $service->price, 0, ',', ' ') }} so‘m</strong></div>
                            <div class="service-metric"><span>Bajarilish muddati</span><strong>{{ $service->deadline }} kun</strong></div>
                        </div>
                        <div class="service-actions">
                            <a class="service-btn service-btn--add" href="{{ route('superadmin.addon.create', $service->id) }}"><i class='bx bx-plus'></i> Qo‘shimcha</a>
                            <a class="service-btn" href="{{ route('superadmin.service.edit', ['service' => $service->id]) }}"><i class='bx bx-pencil'></i> Tahrirlash</a>
                            <form action="{{ route('superadmin.service.destroy', ['service' => $service->id]) }}" method="POST" onsubmit="return confirm('Bu xizmatni o‘chirasizmi?')">@csrf @method('DELETE')<button type="submit" class="service-btn service-btn--danger"><i class='bx bx-trash'></i> O‘chirish</button></form>
                        </div>
                        @if ($service->addons->isNotEmpty())
                            <details class="service-addons">
                                <summary><i class='bx bx-chevron-right'></i> {{ $service->addons->count() }} ta qo‘shimcha xizmatni ko‘rish</summary>
                                <div class="service-addon-list">
                                    @foreach ($service->addons as $addon)
                                        <div class="service-addon"><div><strong>{{ $addon->name }}</strong><small>{{ number_format((float) $addon->price, 0, ',', ' ') }} so‘m · {{ $addon->deadline }} kun</small></div><a href="{{ route('superadmin.addon.edit', [$service->id, $addon->id]) }}">Tahrirlash</a></div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </article>
                @empty
                    <div class="services-empty"><i class='bx bx-briefcase-alt'></i><strong>Hali xizmat yaratilmagan.</strong><br><span>Masalan, “Tarjima qilish” xizmatini qo‘shishdan boshlang.</span></div>
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection
