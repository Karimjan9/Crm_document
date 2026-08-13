<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\OrderDelivery;
use App\Services\OrderCaseService;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function __construct(private readonly OrderCaseService $orders)
    {
    }

    public function index()
    {
        $deliveries = OrderDelivery::query()
            ->where('courier_id', auth()->id())
            ->with(['order.client', 'order.filial'])
            ->whereIn('status', ['sent', 'accepted', 'picked_up'])
            ->latest('id')
            ->get();

        return view('courier.orders.index', compact('deliveries'));
    }

    public function accept(OrderDelivery $delivery)
    {
        $this->authorizeDelivery($delivery);
        abort_unless($delivery->status === 'sent', 422);

        $delivery->forceFill([
            'status' => 'accepted',
            'accepted_at' => now(),
        ])->save();

        return redirect()->back()->with('success', 'Order delivery qabul qilindi.');
    }

    public function deliver(Request $request, OrderDelivery $delivery)
    {
        $this->authorizeDelivery($delivery);
        abort_unless(in_array($delivery->status, ['accepted', 'picked_up'], true), 422);

        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        $delivery->forceFill([
            'status' => 'delivered',
            'delivered_at' => now(),
            'notes' => $request->input('notes', $delivery->notes),
        ])->save();

        $delivery->order->forceFill(['delivered_at' => now()])->save();

        $this->orders->updateStatus($delivery->order, 'delivered', auth()->user(), 'Kuryer buyurtmani yetkazdi.');

        return redirect()->back()->with('success', 'Buyurtma yetkazilgan deb belgilandi.');
    }

    public function returnDelivery(Request $request, OrderDelivery $delivery)
    {
        $this->authorizeDelivery($delivery);
        abort_unless(in_array($delivery->status, ['accepted', 'picked_up'], true), 422);

        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);
        $delivery->forceFill([
            'status' => 'returned',
            'returned_at' => now(),
            'notes' => $data['notes'],
        ])->save();

        $this->orders->recalculate($delivery->order);
        $this->orders->syncFromDocuments($delivery->order->fresh(), auth()->user());

        return redirect()->back()->with('success', 'Order delivery qaytarildi.');
    }

    private function authorizeDelivery(OrderDelivery $delivery): void
    {
        abort_unless((int) $delivery->courier_id === (int) auth()->id(), 403);
        $delivery->loadMissing('order');
    }
}
