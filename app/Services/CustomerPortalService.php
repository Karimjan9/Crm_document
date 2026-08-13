<?php

namespace App\Services;

use App\Models\DocumentFileModel;
use App\Models\Order;
use App\Models\OrderSupportTicket;
use App\Services\DocumentWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerPortalService
{
    public function findByToken(string $token): Order
    {
        return Order::query()
            ->where('tracking_token', $token)
            ->with([
                'filial:id,name',
                'partner:id,company_name,code,brand_name,brand_logo_url,brand_primary_color,brand_secondary_color,tracking_title',
                'packageTemplate:id,name,product_code',
                'documents:id,order_id,document_code,status_doc,service_id',
                'documents.service:id,name',
                'documents.files:id,document_id,original_name,file_type,file_size',
                'checklists:id,order_id,title,is_required,is_completed,notes',
                'deliveries:id,order_id,status,tracking_code,scheduled_at,accepted_at,picked_up_at,delivered_at',
                'statusHistories:id,order_id,to_status,created_at',
            ])
            ->firstOrFail();
    }

    /**
     * Return items that still need an internal action before the order is ready.
     * This intentionally does not expose private document paths to the browser.
     */
    public function missingFiles(Order $order): array
    {
        $missing = [];

        foreach ($order->documents as $document) {
            if ($document->files->isEmpty()) {
                $missing[] = [
                    'document_code' => $document->document_code,
                    'title' => $document->service?->name ?: 'Hujjat fayli',
                    'reason' => 'Fayl yuklanmagan',
                ];
            }
        }

        foreach ($order->checklists->where('is_required', true)->where('is_completed', false) as $checklist) {
            $missing[] = [
                'document_code' => null,
                'title' => $checklist->title,
                'reason' => $checklist->notes ?: 'Tekshiruv tugallanmagan',
            ];
        }

        return $missing;
    }

    /**
     * Only completed documents can expose files through the portal.
     * The actual download is re-authorized in CustomerPortalController.
     */
    public function readyFiles(Order $order): array
    {
        return $order->documents
            ->filter(fn ($document) => app(DocumentWorkflowService::class)->isCompleted($document->status_doc))
            ->flatMap(fn ($document) => $document->files->map(fn (DocumentFileModel $file) => [
                'id' => $file->id,
                'document_code' => $document->document_code,
                'name' => $file->original_name,
                'url' => route('orders.portal.file', [
                    'trackingToken' => $order->tracking_token,
                    'documentFile' => $file->id,
                ]),
            ]))
            ->values()
            ->all();
    }

    public function submitSupportTicket(Order $order, array $data): OrderSupportTicket
    {
        return DB::transaction(function () use ($order, $data): OrderSupportTicket {
            $ticket = $order->supportTickets()->create([
                'subject' => $data['subject'] ?? 'Mijoz murojaati',
                'message' => $data['message'],
                'contact' => $data['contact'] ?? null,
                'status' => 'open',
                'metadata' => ['source' => 'customer_portal'],
            ]);

            $order->notifications()->create([
                'event' => 'support_ticket_created',
                'channel' => 'internal',
                'recipient' => null,
                'message' => 'Mijozdan yangi murojaat: ' . Str::limit($ticket->subject, 120),
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => ['ticket_id' => $ticket->id],
            ]);

            return $ticket;
        });
    }
}
