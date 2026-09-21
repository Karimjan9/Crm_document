@extends('template')

@section('body')
<div class="page-wrapper"><div class="page-content"><div class="card radius-10"><div class="card-header"><h5 class="mb-0">Buyurtma yetkazish</h5></div><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Order</th><th>Mijoz</th><th>Manzil</th><th>Tracking</th><th>Status</th><th>Harakat</th></tr></thead><tbody>
@forelse($deliveries as $delivery)<tr><td><a href="{{ route('orders.show',$delivery->order) }}">{{ $delivery->order->order_code }}</a></td><td>{{ $delivery->recipient_name ?: $delivery->order->client?->name }}<small class="d-block text-muted">{{ $delivery->recipient_phone ?: $delivery->order->client?->phone_number }}</small></td><td>{{ $delivery->address }}</td><td>{{ $delivery->tracking_code }}</td><td>{{ $delivery->status }}</td><td>
@if($delivery->status === 'sent')<form method="POST" action="{{ route('courier.orders.accept',$delivery) }}">@csrf<button class="btn btn-sm btn-success">Qabul qilish</button></form>
@elseif(in_array($delivery->status,['accepted','picked_up'],true))<details><summary class="btn btn-sm btn-primary">Yetkazildi</summary><form method="POST" enctype="multipart/form-data" action="{{ route('courier.orders.deliver',$delivery) }}" class="mt-2 p-2 border rounded bg-white delivery-proof-form">@csrf<input name="received_by" class="form-control form-control-sm mb-1" placeholder="Qabul qilgan shaxs" required><input name="delivery_otp" inputmode="numeric" pattern="[0-9]{6}" class="form-control form-control-sm mb-1" placeholder="Mijozning 6 xonali OTP kodi" required><input name="proof" type="file" accept="image/*,.pdf" class="form-control form-control-sm mb-1" required><textarea name="notes" class="form-control form-control-sm mb-1" placeholder="Izoh"></textarea><input type="hidden" name="latitude"><input type="hidden" name="longitude"><button class="btn btn-sm btn-success">Tasdiqlash</button></form></details>@endif
</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-4">Faol delivery yo‘q.</td></tr>@endforelse
</tbody></table></div></div></div></div></div>
@endsection

@section('script')
<script>document.querySelectorAll('.delivery-proof-form').forEach(form=>{if(!navigator.geolocation)return;navigator.geolocation.getCurrentPosition(p=>{form.latitude.value=p.coords.latitude;form.longitude.value=p.coords.longitude},{enableHighAccuracy:true,timeout:5000});});</script>
@endsection
