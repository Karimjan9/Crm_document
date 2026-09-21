@extends('template')
@section('body')
<div class="page-wrapper"><div class="page-content">
<h4 class="mb-3">Telegram bot matnlari</h4>
<p class="text-muted">Manzil, foydali ma’lumotlar va status xabarlarini shu yerda yangilang. Status kaliti: <code>notification.status.ready_for_delivery</code>.</p>
<div class="card shadow-sm"><div class="card-body">
@foreach($items as $item)
<form method="POST" action="{{ route('bot-content.store') }}" class="border-bottom pb-3 mb-3">@csrf
<input type="hidden" name="key" value="{{ $item->key }}"><label class="form-label fw-bold">{{ $item->key }}</label>
<textarea name="text" class="form-control mb-2" rows="3" required>{{ $item->text }}</textarea><button class="btn btn-primary btn-sm">Saqlash</button>
</form>
@endforeach
<form method="POST" action="{{ route('bot-content.store') }}">@csrf
<h6>Yangi matn</h6><input name="key" class="form-control mb-2" placeholder="Masalan: notification.status.in_processing" required>
<textarea name="text" class="form-control mb-2" rows="3" placeholder="Mijozga yuboriladigan matn" required></textarea><button class="btn btn-outline-primary btn-sm">Qo‘shish</button>
</form>
</div></div></div></div>
@endsection
