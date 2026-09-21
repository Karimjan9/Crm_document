<?php

namespace App\Http\Controllers;

use App\Models\DocumentFileModel;
use App\Models\Order;
use App\Models\OrderPaymentLink;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Services\CustomerPortalService;
use App\Services\DeadlineRadarService;
use App\Services\DocumentFileService;
use App\Services\DocumentWorkflowService;
use App\Services\OrderCaseService;
use App\Services\OrderPaymentLinkService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPortalController extends Controller
{
    public function __construct(
        private readonly CustomerPortalService $portal,
        private readonly OrderPaymentLinkService $paymentLinks,
        private readonly DeadlineRadarService $deadlineRadar,
        private readonly DocumentFileService $files,
    ) {}

    public function show(string $trackingToken)
    {
        $order = $this->portal->findByToken($trackingToken);

        return view('orders.portal', [
            'order' => $order,
            'missingFiles' => $this->portal->missingFiles($order),
            'readyFiles' => $this->portal->readyFiles($order),
            'paymentLink' => $this->paymentLinks->activeFor($order),
            'radar' => $this->deadlineRadar->assess($order),
        ]);
    }

    public function partnerTrack(string $partnerCode, string $trackingToken)
    {
        $order = $this->portal->findByToken($trackingToken);
        abort_unless($order->partner && strcasecmp($order->partner->code, $partnerCode) === 0, 404);

        return view('orders.portal', [
            'order' => $order,
            'missingFiles' => $this->portal->missingFiles($order),
            'readyFiles' => $this->portal->readyFiles($order),
            'paymentLink' => $this->paymentLinks->activeFor($order),
            'radar' => $this->deadlineRadar->assess($order),
        ]);
    }

    public function invoice(string $trackingToken)
    {
        $order = $this->portal->findByToken($trackingToken);
        $order->load(['client', 'partner', 'priceLines.document', 'payments', 'invoice']);

        return view('orders.invoice', ['order' => $order, 'publicPortal' => true]);
    }

    public function payment(string $paymentToken)
    {
        $paymentLink = OrderPaymentLink::query()
            ->where('token', $paymentToken)
            ->with('order:id,order_code,tracking_token,total_amount,paid_amount,currency,status')
            ->firstOrFail();

        abort_if($paymentLink->status !== 'pending' || ($paymentLink->expires_at && $paymentLink->expires_at->isPast()), 410, 'Payment link eskirgan.');

        return view('orders.payment', compact('paymentLink'));
    }

    public function qr(string $trackingToken)
    {
        $order = $this->portal->findByToken($trackingToken);
        $writer = new Writer(new ImageRenderer(new RendererStyle(300, 12), new SvgImageBackEnd));
        $trackingUrl = $order->partner
            ? route('orders.partner-track', [
                'partnerCode' => $order->partner->code,
                'trackingToken' => $order->tracking_token,
            ])
            : route('orders.portal', ['trackingToken' => $order->tracking_token]);
        $svg = $writer->writeString($trackingUrl);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function receipt(string $trackingToken)
    {
        $order = $this->portal->findByToken($trackingToken);
        $order->load(['priceLines.document']);

        return view('orders.receipt', compact('order'));
    }

    public function file(string $trackingToken, DocumentFileModel $documentFile)
    {
        $order = $this->portal->findByToken($trackingToken);
        $documentFile->loadMissing('document.order');
        $document = $documentFile->document;

        abort_unless($document && (int) $document->order_id === (int) $order->id, 404);
        abort_unless(app(DocumentWorkflowService::class)->isCompleted($document->status_doc), 404);

        return $this->files->download($documentFile);
    }

    public function support(Request $request, string $trackingToken)
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:4000'],
            'contact' => ['nullable', 'string', 'max:180'],
        ]);
        $order = $this->portal->findByToken($trackingToken);
        $this->portal->submitSupportTicket($order, $data);

        return redirect()->route('orders.portal', ['trackingToken' => $trackingToken])
            ->with('success', 'Murojaatingiz qabul qilindi. Tez orada bog\'lanamiz.');
    }

    public function repeat(Request $request, string $trackingToken, OrderCaseService $orders)
    {
        $data = $request->validate([
            'promised_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $source = $this->portal->findByToken($trackingToken);
        abort_if($source->status === 'cancelled', 422, 'Bekor qilingan buyurtmani takrorlab bo\'lmaydi.');
        $source->load([
            'client',
            'priceLines.document.service',
            'checklists',
            'documents:id,order_id,service_id,deadline_time',
        ]);
        $deadlineDays = max(1, (int) ($source->documents->max('deadline_time') ?: 3));
        $promisedAt = $data['promised_at'] ?? now()->addDays($deadlineDays);

        $repeat = DB::transaction(function () use ($source, $orders, $promisedAt): Order {
            $repeat = $orders->createForClient($source->client, (int) $source->filial_id, null, [
                'title' => $source->title ?: 'Takroriy buyurtma',
                'description' => $source->description,
                'source' => 'repeat_order',
                'priority' => $source->priority,
                'promised_at' => $promisedAt,
            ]);
            $repeat->forceFill(['repeat_of_order_id' => $source->id])->save();

            foreach ($source->priceLines->where('line_type', '<>', 'discount') as $line) {
                [$name, $unitPrice] = $this->currentRepeatPrice($line);
                $quantity = (float) $line->quantity;
                $repeat->priceLines()->create([
                    'line_type' => $line->line_type,
                    'source_id' => $line->source_id,
                    'name' => $name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => round($quantity * $unitPrice, 2),
                    'metadata' => array_merge($line->metadata ?: [], [
                        'repeated_from_order_id' => $source->id,
                        'repriced_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            if ($source->checklists->isEmpty()) {
                $repeat->checklists()->create([
                    'title' => 'Oldingi buyurtma fayllari va ma\'lumotlarini tasdiqlash',
                    'is_required' => true,
                    'is_completed' => false,
                    'sort_order' => 1,
                ]);
            } else {
                foreach ($source->checklists as $checklist) {
                    $repeat->checklists()->create([
                        'title' => $checklist->title,
                        'is_required' => $checklist->is_required,
                        'is_completed' => false,
                        'sort_order' => $checklist->sort_order,
                        'notes' => $checklist->notes,
                    ]);
                }
            }

            $orders->recalculate($repeat);

            return $repeat->fresh();
        });

        return redirect()->route('orders.portal', ['trackingToken' => $repeat->tracking_token])
            ->with('success', 'Takroriy buyurtma yaratildi. Yangi deadline va narx tekshirildi.');
    }

    private function currentRepeatPrice($line): array
    {
        if ($line->line_type === 'service' && $line->source_id) {
            $service = ServicesModel::query()->find($line->source_id);
            if ($service) {
                return [$service->name, (float) $service->price];
            }
        }

        if ($line->line_type === 'addon' && $line->source_id) {
            $serviceId = $line->document?->service_id;
            $addon = ServiceAddonModel::query()
                ->whereKey($line->source_id)
                ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
                ->first();
            if ($addon) {
                return [$addon->name, (float) $addon->price];
            }
        }

        return [$line->name, (float) $line->unit_price];
    }
}
