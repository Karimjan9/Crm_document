<?php

namespace App\Http\Controllers;

use App\Models\OrderPaymentLink;
use App\Services\OrderCaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly OrderCaseService $orders)
    {
    }

    public function handle(Request $request, string $provider)
    {
        $secret = (string) config('services.payment.webhook_secret');
        $signature = (string) $request->header('X-Payment-Signature');
        $expected = $secret ? hash_hmac('sha256', $request->getContent(), $secret) : '';
        abort_unless($secret !== '' && $signature !== '' && hash_equals($expected, $signature), 401, 'Invalid payment webhook signature.');

        $data = $request->validate([
            'token' => ['required', 'string', 'exists:order_payment_links,token'],
            'status' => ['required', 'string', 'in:paid,success'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'external_id' => ['nullable', 'string', 'max:180'],
        ]);

        $result = DB::transaction(function () use ($data, $provider): OrderPaymentLink {
            $link = OrderPaymentLink::query()
                ->where('token', $data['token'])
                ->lockForUpdate()
                ->with('order')
                ->firstOrFail();

            if ($link->status === 'paid') {
                return $link;
            }
            abort_if($link->status !== 'pending' || ($link->expires_at && $link->expires_at->isPast()), 410, 'Payment link is not active.');
            abort_unless(abs((float) $link->amount - (float) $data['amount']) < 0.01, 422, 'Payment amount mismatch.');

            $this->orders->recordPayment($link->order, (float) $data['amount'], 'online');
            $link->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'provider' => $provider,
                'metadata' => array_merge($link->metadata ?: [], ['external_id' => $data['external_id'] ?? null]),
            ])->save();

            return $link->fresh();
        });

        return response()->json(['success' => true, 'data' => ['payment_link_id' => $result->id, 'status' => $result->status]]);
    }
}
