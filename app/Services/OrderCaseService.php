<?php

namespace App\Services;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\Order;
use App\Models\OrderChecklist;
use App\Models\OrderNotification;
use App\Models\OrderPriceLine;
use App\Models\OrderStatusHistory;
use App\Models\OrderDelivery;
use App\Models\PackageTemplateItem;
use App\Models\DocumentCourier;
use App\Models\PaymentsModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderCaseService
{
    public function __construct(
        private readonly CustomerNotificationService $notifications,
        private readonly PaymentService $payments,
        private readonly PaymentLedgerService $ledger,
        private readonly StatusTransitionService $statusTransitions,
    ) {
    }

    public function createForDocument(DocumentsModel $document): Order
    {
        return DB::transaction(function () use ($document): Order {
            if ($document->order_id) {
                return $document->order()->firstOrFail();
            }

            $order = Order::create([
                'client_id' => $document->client_id,
                'filial_id' => $document->filial_id,
                'created_by_id' => $document->user_id,
                'responsible_user_id' => $document->assigned_to_id ?: $document->user_id,
                'delivery_type' => 'pickup',
                'order_code' => $this->nextOrderCode($document->filial_id),
                'tracking_token' => Str::random(64),
                'status' => 'received',
                'title' => $document->service?->name ?: 'Hujjat buyurtmasi',
                'description' => $document->description,
                'currency' => 'UZS',
                'subtotal_amount' => $this->documentSubtotal($document),
                'discount_amount' => $this->documentDiscount($document),
                'total_amount' => (float) ($document->final_price ?? 0),
                'paid_amount' => (float) ($document->paid_amount ?? 0),
                'cost_amount' => 0,
                'profit_amount' => (float) ($document->final_price ?? 0),
                'completed_at' => null,
            ]);

            $document->forceFill(['order_id' => $order->id])->save();
            $this->syncDocumentFinancials($order, $document);
            $this->seedChecklist($order, $document);
            app(DocumentChecklistService::class)->syncForService($document);
            app(DocumentWorkflowService::class)->initialize($document, $document->user);
            $this->recordStatus($order, null, $order->status, $document->user_id, 'Order avtomatik yaratildi');
            $this->recalculate($order);
            if (in_array($document->workflow_status, ['ready_for_delivery', 'delivered', 'completed'], true)) {
                $this->syncFromDocuments($order->fresh(), $document->user);
            }

            return $order->fresh();
        });
    }

    public function createForClient(ClientsModel $client, int $filialId, ?int $createdById = null, array $data = []): Order
    {
        if ($client->filial_id !== null && (int) $client->filial_id !== $filialId) {
            throw ValidationException::withMessages([
                'filial_id' => 'Mijoz va order bir xil filialga tegishli bo‘lishi kerak.',
            ]);
        }

        return DB::transaction(function () use ($client, $filialId, $createdById, $data): Order {
            $order = Order::create([
                'client_id' => $client->id,
                'filial_id' => $filialId,
                'created_by_id' => $createdById,
                'responsible_user_id' => ($data['responsible_user_id'] ?? $createdById) ?: null,
                'order_code' => $this->nextOrderCode($filialId),
                'tracking_token' => Str::random(64),
                'status' => $data['status'] ?? 'received',
                'priority' => $data['priority'] ?? 'normal',
                'source' => $data['source'] ?? null,
                'customer_source' => $data['customer_source'] ?? ($data['source'] ?? null),
                'partner_id' => $data['partner_id'] ?? null,
                'partner_reference' => $data['partner_reference'] ?? null,
                'partner_discount_percent' => min(max((float) ($data['partner_discount_percent'] ?? 0), 0), 100),
                'partner_discount_amount' => 0,
                'billing_status' => $data['billing_status'] ?? 'unbilled',
                'partner_metadata' => $data['partner_metadata'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'pickup',
                'title' => $data['title'] ?? 'Yangi buyurtma',
                'description' => $data['description'] ?? null,
                'currency' => 'UZS',
                'promised_at' => $data['promised_at'] ?? null,
            ]);

            $this->recordStatus($order, null, $order->status, $createdById, 'Order yaratildi');
            $this->createNotification($order, 'order_created', 'Buyurtma yaratildi.');
            $this->notifications->queueEvent($order, 'order_received');

            return $order;
        });
    }

    public function attachDocument(Order $order, DocumentsModel $document): Order
    {
        return DB::transaction(function () use ($order, $document): Order {
            if ((int) $order->client_id !== (int) $document->client_id
                || (int) $order->filial_id !== (int) $document->filial_id) {
                throw ValidationException::withMessages([
                    'order_id' => 'Hujjat va order bir xil mijoz/filialga tegishli bo‘lishi kerak.',
                ]);
            }

            $document->forceFill(['order_id' => $order->id])->save();
            $this->syncDocumentFinancials($order, $document);
            $this->seedChecklist($order, $document);
            app(DocumentChecklistService::class)->syncForService($document);
            app(DocumentWorkflowService::class)->initialize($document, $document->user);
            $this->recalculate($order);

            return $order->fresh();
        });
    }

    public function recalculate(Order $order): Order
    {
        $order->loadMissing('documents', 'costs');

        $priceLines = $order->priceLines()->get();
        $subtotal = (float) $priceLines
            ->filter(fn (OrderPriceLine $line): bool => $line->line_type !== 'discount'
                && ! (bool) data_get($line->metadata, 'included_in_package', false))
            ->sum('total_price');
        $paid = $this->ledger->effectiveSum($order->payments());
        $directCosts = (float) $priceLines
            ->filter(fn (OrderPriceLine $line): bool => ! (bool) data_get($line->metadata, 'included_in_package', false))
            ->sum('cost_amount');
        $recordedCosts = (float) $order->costs()->sum('amount');
        $costs = $directCosts + $recordedCosts;
        $documentDiscount = (float) $order->documents
            ->filter(fn (DocumentsModel $document): bool => ! $this->documentIsIncludedInPackage($order, $document))
            ->sum(function (DocumentsModel $document): float {
                return $this->documentDiscount($document);
            });

        $baseAfterDocumentDiscount = max($subtotal - $documentDiscount, 0);
        $partnerDiscountPercent = min(max((float) $order->partner_discount_percent, 0), 100);
        $partnerDiscount = round($baseAfterDocumentDiscount * ($partnerDiscountPercent / 100), 2);
        $total = round(max($baseAfterDocumentDiscount - $partnerDiscount, 0), 2);

        $order->forceFill([
            'subtotal_amount' => round($subtotal, 2),
            'discount_amount' => round($documentDiscount + $partnerDiscount, 2),
            'partner_discount_amount' => $partnerDiscount,
            'total_amount' => $total,
            'paid_amount' => round($paid, 2),
            'cost_amount' => round($costs, 2),
            'profit_amount' => round($total - $costs, 2),
        ])->save();

        $this->syncPaymentStatus($order->fresh());

        return $order->fresh();
    }

    public function recordPayment(
        Order $order,
        float $amount,
        string $paymentType,
        ?User $actor = null,
        ?int $documentId = null,
        array $options = []
    ): PaymentsModel {
        return DB::transaction(function () use ($order, $amount, $paymentType, $actor, $documentId, $options): PaymentsModel {
            $order = Order::query()->findOrFail($order->id);
            $amount = round($amount, 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'To‘lov summasi 0 dan katta bo‘lishi kerak.']);
            }

            if (! in_array($paymentType, PaymentsModel::TYPES, true)) {
                throw ValidationException::withMessages(['payment_type' => 'To‘lov turi noto‘g‘ri.']);
            }

            // Document and order payment endpoints use the same lock order.
            // This prevents a document payment and an order payment from
            // racing against each other.
            $document = $documentId === null
                ? null
                : $order->documents()->lockForUpdate()->findOrFail($documentId);
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $order = $this->recalculate($order);
            $paid = $this->ledger->effectiveSum(PaymentsModel::query()->where('order_id', $order->id));
            $balance = round((float) $order->total_amount - $paid, 2);

            if ($balance <= 0 || $amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'To‘lov order qoldig‘idan oshib ketmasligi kerak.',
                ]);
            }

            if ($documentId !== null) {
                $documentPaid = $this->ledger->effectiveSum(PaymentsModel::query()->where('document_id', $document->id));
                $documentBalance = round((float) $document->final_price - $documentPaid, 2);
                if ($amount > $documentBalance) {
                    throw ValidationException::withMessages([
                        'amount' => 'To‘lov tanlangan hujjat qoldig‘idan oshib ketdi.',
                    ]);
                }
            }

            $payment = $this->payments->record($order, $amount, $paymentType, $actor, $documentId, $options);

            $this->recalculate($order);

            if ($documentId !== null) {
                app(DocumentWorkflowService::class)->syncDerivedStatus(
                    DocumentsModel::query()->findOrFail($documentId),
                    $actor,
                );
            }

            $this->notifications->queueEvent($order->fresh(), 'payment_received', "Buyurtma {$order->order_code} uchun to‘lov qabul qilindi.");

            return $payment->fresh();
        });
    }

    public function updateStatus(Order $order, string $status, ?User $actor = null, ?string $reason = null): Order
    {
        if (! in_array($status, Order::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Order statusi noto‘g‘ri.']);
        }

        $order->loadMissing(['documents', 'checklists', 'deliveries']);

        if ($status === 'ready_for_delivery') {
            $allFinished = $order->documents->isNotEmpty()
                && $order->documents->every(fn (DocumentsModel $document): bool => in_array($document->workflow_status, ['ready_for_delivery', 'delivered', 'completed'], true));
            if (! $allFinished || $order->balance_amount > 0) {
                throw ValidationException::withMessages([
                    'status' => 'Order barcha hujjatlar tugagach va to‘lov to‘liq bo‘lgach topshirishga tayyor bo‘ladi.',
                ]);
            }
        }

        if ($status === 'courier_sent' && ! $order->deliveries->contains(
            fn (OrderDelivery $delivery): bool => in_array($delivery->status, ['sent', 'accepted', 'picked_up'], true)
        )) {
            throw ValidationException::withMessages([
                'status' => 'Courier delivery avval biriktirilishi kerak.',
            ]);
        }

        if ($status === 'delivered' && ! $order->deliveries->contains(
            fn (OrderDelivery $delivery): bool => $delivery->status === 'delivered'
        )) {
            throw ValidationException::withMessages([
                'status' => 'Delivery yetkazilgan deb belgilanmagan.',
            ]);
        }

        if ($status === 'completed') {
            $hasIncompleteRequired = $order->checklists->contains(
                fn (OrderChecklist $item): bool => $item->is_required && ! $item->is_completed
            );
            $hasIncompleteDocumentQa = $order->documents->contains(function (DocumentsModel $document): bool {
                $checklists = app(DocumentChecklistService::class);
                $progress = $checklists->progress($document);
                return ! in_array($document->workflow_status, ['delivered', 'completed'], true)
                    || ! $progress['complete']
                    || ($progress['total'] > 0 && $checklists->qaStatus($document) !== 'passed');
            });
            if ($order->documents->isEmpty() || $order->balance_amount > 0 || $hasIncompleteRequired || $hasIncompleteDocumentQa) {
                throw ValidationException::withMessages([
                    'status' => 'Orderni yakunlashdan oldin hujjat, QA, to‘lov va majburiy checklistni tugating.',
                ]);
            }
        }

        if ($order->status === $status) {
            return $order;
        }

        return DB::transaction(function () use ($order, $status, $actor, $reason): Order {
            $order = $this->statusTransitions->transition($order, $status, $actor, $reason);
            $this->createNotification($order, 'status_changed', 'Buyurtma holati: ' . $order->status_label);

            $event = match ($status) {
                'received' => 'order_received',
                'ready_for_delivery' => 'document_ready',
                'courier_sent' => 'courier_sent',
                'delivered', 'completed' => 'order_delivered',
                default => null,
            };
            if ($event) {
                $this->notifications->queueEvent($order, $event);
            }

            return $order->fresh();
        });
    }

    public function syncFromDocuments(Order $order, ?User $actor = null): Order
    {
        $order->load('documents');

        if ($order->documents->isEmpty() || in_array($order->status, ['cancelled', 'delivered', 'completed'], true)) {
            return $order;
        }

        $allFinished = $order->documents->every(fn (DocumentsModel $document): bool => in_array($document->workflow_status, ['ready_for_delivery', 'delivered', 'completed'], true));
        $target = $allFinished
            ? ($order->balance_amount > 0 ? 'awaiting_payment' : 'ready_for_delivery')
            : 'in_processing';

        if ($order->status !== $target) {
            return $this->updateStatus($order, $target, $actor, 'Hujjatlar holatidan avtomatik yangilandi.');
        }

        return $order;
    }

    public function toggleChecklist(OrderChecklist $item, bool $completed, ?User $actor = null, ?string $notes = null): OrderChecklist
    {
        $item->forceFill([
            'is_completed' => $completed,
            'completed_by_id' => $completed ? $actor?->id : null,
            'completed_at' => $completed ? now() : null,
            'notes' => $notes ?? $item->notes,
        ])->save();

        if (! $completed) {
            $this->notifications->queueEvent($item->order, 'missing_files');
        }

        return $item->fresh();
    }

    public function createNotification(Order $order, string $event, string $message, string $channel = 'internal'): OrderNotification
    {
        return $this->notifications->queueMessage($order, $channel, $message, null, $event);
    }

    public function queueCustomerNotification(
        Order $order,
        string $channel,
        string $message,
        ?string $recipient = null
    ): OrderNotification {
        if (! in_array($channel, ['sms', 'telegram', 'email', 'whatsapp', 'internal'], true)) {
            throw ValidationException::withMessages(['channel' => 'Notification kanali noto‘g‘ri.']);
        }

        return $this->notifications->queueMessage($order, $channel, $message, $recipient);
    }

    public function syncDocumentCourier(DocumentCourier $assignment): ?OrderDelivery
    {
        $assignment->loadMissing(['document.order', 'courier', 'sentBy']);
        $document = $assignment->document;
        $order = $document?->order;

        if (! $order) {
            return null;
        }

        $delivery = $order->deliveries()->firstOrNew([
            'tracking_code' => 'DLV-DOC-' . $document->id,
        ]);

        $delivery->forceFill([
            'courier_id' => $assignment->courier_id,
            'assigned_by_id' => $assignment->sent_by_id,
            'status' => match ($assignment->status) {
                'accepted' => 'accepted',
                'returned', 'rejected' => 'returned',
                default => 'sent',
            },
            'recipient_name' => $order->client?->name,
            'recipient_phone' => $order->client?->phone_number,
            'notes' => $assignment->return_comment ?: $assignment->courier_comment ?: $assignment->sent_comment,
            'accepted_at' => $assignment->accepted_at,
            'returned_at' => $assignment->returned_at ?: $assignment->rejected_at,
        ])->save();

        $workflow = app(DocumentWorkflowService::class);
        if (in_array($assignment->status, ['sent', 'accepted'], true)) {
            if ($workflow->normalizeStatus($document->status_doc) !== 'courier_sent') {
                $workflow->transition($document, 'courier_sent', $assignment->sentBy, 'Hujjat courierga topshirildi.');
            }
        } elseif ($workflow->normalizeStatus($document->status_doc) === 'courier_sent') {
            $workflow->transition($document, 'ready_for_delivery', $assignment->sentBy, 'Courier hujjatni qaytardi.');
        }

        $targetStatus = in_array($assignment->status, ['sent', 'accepted'], true)
            ? 'courier_sent'
            : 'ready_for_delivery';

        if ($order->status !== $targetStatus && ! in_array($order->status, ['completed', 'cancelled', 'delivered'], true)) {
            if ($targetStatus === 'ready_for_delivery') {
                $this->recalculate($order);
                $this->syncFromDocuments($order->fresh(), $assignment->sentBy);
            } else {
                $this->updateStatus($order, $targetStatus, $assignment->sentBy, 'Legacy document courier oqimi orderga sinxronlandi.');
            }
        }

        return $delivery;
    }

    public function nextOrderCode(int $filialId): string
    {
        $prefix = DB::table('filial')->where('id', $filialId)->value('code') ?: $filialId;
        $base = 'ORD-' . strtoupper((string) $prefix) . '-' . now()->format('ymd');
        $count = Order::query()->where('order_code', 'like', $base . '-%')->count() + 1;

        do {
            $code = $base . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }

    private function syncDocumentFinancials(Order $order, DocumentsModel $document): void
    {
        $order->priceLines()->where('document_id', $document->id)->delete();
        $includedInPackage = $this->documentIsIncludedInPackage($order, $document);

        $snapshotLines = data_get($document->pricing_snapshot, 'line_items', []);
        if (is_array($snapshotLines) && $snapshotLines !== []) {
            foreach ($snapshotLines as $snapshotLine) {
                $pricingLineType = (string) ($snapshotLine['line_type'] ?? 'base_service');
                $legacyLineType = match ($pricingLineType) {
                    'base_service' => 'service',
                    default => $pricingLineType,
                };
                $included = $pricingLineType === 'discount' ? false : $includedInPackage;

                $order->priceLines()->create([
                    'document_id' => $document->id,
                    'line_type' => $legacyLineType,
                    'pricing_line_type' => $pricingLineType,
                    'source_id' => $snapshotLine['source_id'] ?? null,
                    'price_tariff_id' => $snapshotLine['price_tariff_id'] ?? null,
                    'name' => $snapshotLine['name'] ?? 'Narx satri',
                    'quantity' => $snapshotLine['quantity'] ?? 1,
                    'unit_price' => $snapshotLine['unit_price'] ?? 0,
                    'total_price' => $snapshotLine['total_price'] ?? 0,
                    'cost_amount' => $snapshotLine['cost_amount'] ?? 0,
                    'metadata' => [
                        'document_code' => $document->document_code,
                        'included_in_package' => $included,
                        'pricing_source' => $snapshotLine['price_source'] ?? (!empty($snapshotLine['price_tariff_id']) ? 'tariff' : 'legacy'),
                        'variant_applied' => $snapshotLine['variant_applied'] ?? null,
                        'price_key' => $snapshotLine['price_key'] ?? null,
                    ],
                    'pricing_context' => $snapshotLine['pricing_context'] ?? $document->pricing_context,
                    'effective_from' => $snapshotLine['effective_from'] ?? null,
                ]);
            }

            foreach ($document->payments as $payment) {
                if (! $payment->order_id) {
                    $payment->forceFill(['order_id' => $order->id])->save();
                }
            }

            return;
        }

        $serviceName = $document->service?->name ?: 'Xizmat';
        $servicePrice = (float) ($document->service_price ?? 0);
        $order->priceLines()->create([
            'document_id' => $document->id,
            'line_type' => 'service',
            'source_id' => $document->service_id,
            'name' => $serviceName,
            'quantity' => 1,
            'unit_price' => $servicePrice,
            'total_price' => $servicePrice,
            'metadata' => [
                'document_code' => $document->document_code,
                'included_in_package' => $includedInPackage,
            ],
        ]);

        foreach ($document->processCharges as $charge) {
            $order->priceLines()->create([
                'document_id' => $document->id,
                'line_type' => $charge->charge_type ?: 'process_charge',
                'pricing_line_type' => match (true) {
                    str_starts_with((string) $charge->charge_type, 'apostil') => 'apostille',
                    in_array($charge->charge_type, ['consul', 'consulate'], true) => 'consulate',
                    default => $charge->charge_type ?: 'process_charge',
                },
                'source_id' => $charge->source_id,
                'name' => $charge->name ?: 'Qo‘shimcha jarayon',
                'quantity' => 1,
                'unit_price' => (float) $charge->price,
                'total_price' => (float) $charge->price,
                'metadata' => ['included_in_package' => $includedInPackage],
            ]);
        }

        $document->loadMissing(['addons', 'document_type_addons', 'document_direction_addons']);
        foreach ([$document->addons, $document->document_type_addons, $document->document_direction_addons] as $addons) {
            foreach ($addons as $addon) {
                $price = (float) ($addon->pivot->addon_price ?? $addon->price ?? $addon->amount ?? 0);
                $order->priceLines()->create([
                    'document_id' => $document->id,
                    'line_type' => 'addon',
                    'source_id' => $addon->id,
                    'name' => $addon->name,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'total_price' => $price,
                    'metadata' => ['included_in_package' => $includedInPackage],
                ]);
            }
        }

        $discount = $this->documentDiscount($document);
        if ($discount > 0) {
            $order->priceLines()->create([
                'document_id' => $document->id,
                'line_type' => 'discount',
                'name' => 'Chegirma',
                'quantity' => 1,
                'unit_price' => -$discount,
                'total_price' => -$discount,
            ]);
        }

        foreach ($document->payments as $payment) {
            if (! $payment->order_id) {
                $payment->forceFill(['order_id' => $order->id])->save();
            }
        }
    }

    private function seedChecklist(Order $order, DocumentsModel $document): void
    {
        if ($order->checklists()->where('document_id', $document->id)->exists()) {
            return;
        }

        $order->checklists()->create([
            'document_id' => $document->id,
            'title' => 'Hujjat ma’lumotlari va fayllarini tekshirish',
            'is_required' => true,
            'is_completed' => in_array($document->workflow_status, ['ready_for_delivery', 'delivered', 'completed'], true),
            'sort_order' => 1,
            'completed_at' => in_array($document->workflow_status, ['ready_for_delivery', 'delivered', 'completed'], true) ? $document->updated_at : null,
        ]);
    }

    private function recordStatus(Order $order, ?string $from, string $to, ?int $actorId, ?string $reason): void
    {
        $order->statusHistories()->create([
            'from_status' => $from,
            'to_status' => $to,
            'changed_by_id' => $actorId,
            'reason' => $reason,
        ]);
    }

    private function syncPaymentStatus(Order $order): void
    {
        if (in_array($order->status, ['completed', 'cancelled', 'delivered'], true)) {
            return;
        }

        $total = (float) $order->total_amount;
        $paid = (float) $order->paid_amount;
        $status = $total <= 0
            ? 'paid'
            : ($paid <= 0 ? 'awaiting_payment' : ($paid < $total ? 'partially_paid' : 'paid'));

        if ($order->status === 'received' || in_array($order->status, ['awaiting_payment', 'partially_paid', 'paid'], true)) {
            $this->updateStatus($order, $status, null, 'To‘lov holati avtomatik yangilandi');
        }
    }

    private function documentSubtotal(DocumentsModel $document): float
    {
        return app(DocumentPricingService::class)->subtotal($document);
    }

    private function documentDiscount(DocumentsModel $document): float
    {
        return app(DocumentPricingService::class)->discount($document);
    }

    private function documentIsIncludedInPackage(Order $order, DocumentsModel $document): bool
    {
        if (! $order->package_template_id) {
            return false;
        }

        return PackageTemplateItem::query()
            ->where('package_template_id', $order->package_template_id)
            ->where('service_id', $document->service_id)
            ->where(function ($query) use ($document): void {
                $query->whereNull('document_type_id')
                    ->orWhere('document_type_id', $document->document_type_id);
            })
            ->exists();
    }
}
