<?php

namespace App\Services;

use App\Models\ClientsModel;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function save(array $data, User $actor, ?Lead $lead = null): Lead
    {
        $lead ??= new Lead;
        $data['filial_id'] ??= $actor->filial_id;
        $data['assigned_to_id'] ??= $actor->id;
        if (($data['status'] ?? $lead->status) !== 'new' && ! $lead->first_responded_at) {
            $data['first_responded_at'] = now();
        }
        if (($data['status'] ?? $lead->status) === 'quoted') {
            $data['quoted_at'] = $lead->quoted_at ?: now();
        }
        if (($data['status'] ?? $lead->status) === 'lost') {
            $data['lost_at'] = $lead->lost_at ?: now();
        }
        $lead->fill($data)->save();
        $this->syncFollowUpTask($lead, $actor);

        return $lead->fresh();
    }

    public function addActivity(Lead $lead, User $actor, string $type, string $body): void
    {
        $lead->activities()->create(['user_id' => $actor->id, 'type' => $type, 'body' => $body, 'happened_at' => now()]);
        if (! $lead->first_responded_at) {
            $lead->forceFill([
                'first_responded_at' => now(),
                'status' => $lead->status === 'new' ? 'contacted' : $lead->status,
            ])->save();
        }
    }

    public function convert(Lead $lead, User $actor, array $orderData): Order
    {
        return DB::transaction(function () use ($lead, $actor, $orderData) {
            $client = $lead->client ?: ClientsModel::query()->firstOrCreate(
                ['phone_number' => $lead->phone],
                ['name' => $lead->name, 'filial_id' => $lead->filial_id]
            );
            $order = app(OrderCaseService::class)->createForClient($client, (int) $lead->filial_id, $actor->id, [
                'title' => $orderData['title'] ?? ($lead->interested_service ?: 'Lead dan order'),
                'source' => $lead->source, 'customer_source' => $lead->source, 'responsible_user_id' => $lead->assigned_to_id ?: $actor->id,
                'priority' => $orderData['priority'] ?? 'normal', 'promised_at' => $orderData['promised_at'] ?? null,
            ]);
            $lead->forceFill(['client_id' => $client->id, 'converted_order_id' => $order->id, 'status' => 'won', 'won_at' => now()])->save();
            $this->addActivity($lead, $actor, 'conversion', 'Lead orderga aylantirildi: '.$order->order_code);
            $lead->workItems()->whereIn('status', ['open', 'in_progress', 'blocked'])->update(['status' => 'done', 'completed_at' => now()]);

            return $order;
        });
    }

    private function syncFollowUpTask(Lead $lead, User $actor): void
    {
        $query = $lead->workItems()->where('type', 'lead_follow_up')->whereIn('status', ['open', 'in_progress', 'blocked']);
        if (in_array($lead->status, ['won', 'lost'], true) || ! $lead->next_follow_up_at) {
            $query->update(['status' => 'done', 'completed_at' => now()]);

            return;
        }
        $query->update(['status' => 'cancelled']);
        $lead->workItems()->create(['filial_id' => $lead->filial_id, 'assigned_to_id' => $lead->assigned_to_id, 'created_by_id' => $actor->id, 'type' => 'lead_follow_up', 'title' => 'Lead bilan qayta bog‘lanish: '.$lead->name, 'status' => 'open', 'priority' => $lead->next_follow_up_at->isPast() ? 'high' : 'normal', 'due_at' => $lead->next_follow_up_at]);
    }
}
