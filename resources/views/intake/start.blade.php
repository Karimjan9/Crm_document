<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Smart intake wizard</title>
    <style>
        body{font-family:Arial;background:#f4f7fb;color:#172033}.box{max-width:800px;margin:35px auto;background:#fff;padding:24px;border-radius:16px;box-shadow:0 10px 25px #0000000d}h1{margin-top:0}label{display:block;font-size:13px;font-weight:700;margin:12px 0 5px}input,select,textarea{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:8px}.addons{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.addon{border:1px solid #e2e8f0;border-radius:8px;padding:9px;font-size:13px}.addon input{width:auto}.btn{margin-top:18px;background:#2563eb;color:#fff;border:0;border-radius:8px;padding:11px 15px;font-weight:700;cursor:pointer}.notice{padding:10px;border-radius:8px;background:#ecfdf5;color:#047857}.muted{color:#64748b;font-size:13px}.ocr{margin-top:24px;border-top:1px solid #e2e8f0;padding-top:18px}.ocr-row{display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid #eef2f7}@media(max-width:600px){.addons{grid-template-columns:1fr}.ocr-row{display:block}}
    </style>
</head>
<body>
<div class="box">
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="notice" style="background:#fef2f2;color:#b91c1c">{{ $errors->first() }}</div>@endif

    <h1>Smart intake wizard</h1>
    <p class="muted">Savollarga javob bering: xizmat, taxminiy narx, deadline, addon va kerakli fayllar aniqlanadi.</p>

    <form method="POST" action="{{ route('intake.analyze') }}">
        @csrf
        <input type="hidden" name="session" value="{{ $session->token }}">
        <label>Qaysi filial?</label>
        <select name="filial_id">
            <option value="">Keyinroq tanlayman</option>
            @foreach($filials as $filial)
                <option value="{{ $filial->id }}" @selected($session->filial_id == $filial->id)>{{ $filial->name }}</option>
            @endforeach
        </select>

        <label>Xizmatni tanlang yoki izoh yozing</label>
        <select name="service_id">
            <option value="">Avtomatik aniqlash</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}">{{ $service->name }} — {{ number_format($service->price,0,',',' ') }} UZS</option>
            @endforeach
        </select>
        <input name="query" placeholder="Masalan: tarjima, apostil, konsul">

        <label>Foydali addonlar</label>
        <div class="addons">
            @foreach($services as $service)
                @foreach($service->addons as $addon)
                    <label class="addon"><input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}"> {{ $addon->name }} (+{{ number_format($addon->price,0,',',' ') }})</label>
                @endforeach
            @endforeach
        </div>
        <label><input type="checkbox" name="needs_original" value="1" style="width:auto"> Original hujjat topshiraman</label>
        <label><input type="checkbox" name="needs_translation" value="1" style="width:auto"> Tarjima kerak</label>
        <button class="btn">Tavsiyani hisoblash</button>
    </form>

    @if($session->status === 'analyzed')
        <div class="ocr">
            <h2>Tavsiya</h2>
            <p><strong>Xizmat:</strong> {{ $session->recommendedService?->name ?: 'Aniqlanmadi' }}</p>
            <p><strong>Taxminiy narx:</strong> {{ number_format($session->estimated_price,0,',',' ') }} UZS</p>
            <p><strong>Deadline:</strong> {{ $session->estimated_deadline_days ?: '—' }} kun</p>
            <p><strong>Kerakli fayllar:</strong></p>
            <ul>@foreach($session->required_files ?: [] as $file)<li>{{ $file }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="ocr">
        <h2>OCR hujjat tahlili</h2>
        <p class="muted">OCR faqat tavsiya beradi. Xizmatga qo‘llashdan oldin xodim tasdig‘i majburiy.</p>
        <form method="POST" enctype="multipart/form-data" action="{{ route('intake.ocr.upload', ['token' => $session->token]) }}">
            @csrf
            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
            <button class="btn" type="submit">OCR ga yuborish</button>
        </form>
        @forelse($session->ocrDocuments as $ocr)
            <div class="ocr-row">
                <span>{{ $ocr->original_name }} <small class="muted">{{ $ocr->provider }}</small></span>
                <strong>{{ $ocr->status }}</strong>
            </div>
        @empty
            <p class="muted">Hali OCR fayl yuborilmagan.</p>
        @endforelse
    </div>
</div>
</body>
</html>
