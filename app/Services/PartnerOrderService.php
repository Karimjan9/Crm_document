<?php

namespace App\Services;

use App\Models\ClientsModel;
use App\Models\Order;
use App\Models\PackageTemplate;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartnerOrderService
{
    public function __construct(
        private readonly OrderCaseService $orders,
        private readonly PackageProductService $products,
    ) {
    }

    public function create(Partner $partner, array $data, ?User $actor = null): Order
    {
        if ($partner->status !== 'active') {
            throw ValidationException::withMessages(['partner' => 'Partner kabineti vaqtincha to\'xtatilgan.']);
        }

        $filialId = (int) ($data['filial_id'] ?? 0);
        if ($filialId <= 0 || ! $partner->canUseFilial($filialId)) {
            throw ValidationException::withMessages([
                'filial_id' => 'Bu filial partner uchun ruxsat etilmagan.',
            ]);
        }

        $phone = trim((string) ($data['client_phone'] ?? ''));
        $name = trim((string) ($data['client_name'] ?? ''));
        if ($phone === '' || $name === '') {
            throw ValidationException::withMessages([
                'client_phone' => 'Mijoz nomi va telefoni majburiy.',
            ]);
        }

        return DB::transaction(function () use ($partner, $data, $actor, $filialId, $phone, $name): Order {
            $client = ClientsModel::query()->where('phone_number', $phone)->first();

            if ($client && $client->filial_id !== null && (int) $client->filial_id !== $filialId) {
                throw ValidationException::withMessages([
                    'client_phone' => 'Bu telefon raqami boshqa filialdagi mijozga tegishli.',
                ]);
            }

            if (! $client) {
                $client = ClientsModel::create([
                    'name' => $name,
                    'phone_number' => $phone,
                    'email' => $data['client_email'] ?? null,
                    'filial_id' => $filialId,
                    'description' => 'B2B partner: ' . $partner->company_name,
                ]);
            } else {
                $client->forceFill([
                    'name' => $name ?: $client->name,
                    'email' => $data['client_email'] ?? $client->email,
                    'filial_id' => $client->filial_id ?: $filialId,
                ])->save();
            }

            $product = null;
            $quote = null;
            if (! empty($data['package_template_id'])) {
                $product = PackageTemplate::query()->findOrFail((int) $data['package_template_id']);
                $quote = $this->products->quote($product, $filialId, $data['package_variant'] ?? 'standard');
            }

            $order = $this->orders->createForClient($client, $filialId, $actor?->id, [
                'partner_id' => $partner->id,
                'partner_reference' => $data['partner_reference'] ?? null,
                'partner_discount_percent' => (float) $partner->discount_percent,
                'billing_status' => 'unbilled',
                'partner_metadata' => [
                    'partner_code' => $partner->code,
                    'channel' => 'b2b',
                ],
                'title' => ($data['title'] ?? null) ?: ($product?->name ?: 'B2B buyurtma'),
                'description' => $data['description'] ?? null,
                'source' => 'b2b_partner',
                'customer_source' => $partner->company_name,
                'priority' => $data['priority'] ?? 'normal',
                'delivery_type' => $data['delivery_type'] ?? ($quote['delivery_type'] ?? 'pickup'),
                'promised_at' => $data['promised_at'] ?? ($quote ? now()->addDays($quote['deadline_days']) : null),
            ]);

            if ($product) {
                $order = $this->products->attachToOrder(
                    $order,
                    (int) $product->id,
                    $data['package_variant'] ?? 'standard',
                    $data['package_addon_ids'] ?? []
                );
            }

            return $order->fresh(['partner', 'client', 'filial', 'packageTemplate']);
        });
    }

    public function ownedOrder(Partner $partner, int $orderId): Order
    {
        return $partner->orders()
            ->with([
                'client:id,name,phone_number,email',
                'filial:id,name',
                'partner:id,company_name,code',
                'packageTemplate:id,name,product_code',
                'priceLines:id,order_id,line_type,name,quantity,unit_price,total_price',
                'payments:id,order_id,amount,payment_type,created_at',
                'statusHistories:id,order_id,to_status,created_at',
            ])
            ->findOrFail($orderId);
    }
}
