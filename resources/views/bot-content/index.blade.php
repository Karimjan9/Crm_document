@extends('template')
@section('body')
<div class="page-wrapper"><div class="page-content">
<h4 class="mb-3">Telegram bot matnlari</h4>
<a href="{{ route('bot-useful-information.edit') }}" class="btn btn-outline-primary mb-3"><i class="bx bx-book-open me-1"></i>Foydali ma’lumotlar kutubxonasi</a>
<p class="text-muted">Foydali ma’lumotlar va status xabarlarini shu yerda yangilang. Status kaliti: <code>notification.status.ready_for_delivery</code>.</p>
<div class="alert alert-info">Manzil, telefon, ish vaqti va ish kunlari «Filiallar» bo‘limidan avtomatik olinadi. Ish vaqti kiritilmagan bo‘lsa, bot 09:00–18:00 ko‘rsatadi.</div>
<div class="card shadow-sm"><div class="card-body">
@foreach($items as $item)
@if($item->key === 'useful-information')
<div class="border-bottom pb-3 mb-3"><h6>Foydali ma’lumotlar</h6><p class="text-muted">{{ $item->text }}</p><a href="{{ route('bot-useful-information.edit') }}">Mavzularni tahrirlash</a></div>
@else
<form method="POST" action="{{ route('bot-content.store') }}" class="border-bottom pb-3 mb-3">@csrf
<input type="hidden" name="key" value="{{ $item->key }}"><label class="form-label fw-bold">{{ $item->key }}</label>
<textarea name="text" class="form-control mb-2" rows="3" required>{{ $item->text }}</textarea><button class="btn btn-primary btn-sm">Saqlash</button>
</form>
@endif
@endforeach
<form method="POST" action="{{ route('bot-content.store') }}">@csrf
<h6>Yangi matn</h6><input name="key" class="form-control mb-2" placeholder="Masalan: notification.status.in_processing" required>
<textarea name="text" class="form-control mb-2" rows="3" placeholder="Mijozga yuboriladigan matn" required></textarea><button class="btn btn-outline-primary btn-sm">Qo‘shish</button>
</form>
</div></div></div></div>
@endsection
