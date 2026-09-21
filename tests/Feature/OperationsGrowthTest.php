<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Models\WorkItem;
use App\Services\BusinessApprovalService;
use App\Services\DocumentWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationsGrowthTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_create_and_convert_a_lead_to_order(): void
    {
        $filial = FilialModel::create(['name' => 'Growth', 'code' => 'GRO']);
        $employee = $this->user('employee', $filial->id);

        $this->actingAs($employee)->post(route('leads.store'), [
            'name' => 'Lead Customer', 'phone' => '901112233', 'source' => 'instagram',
            'status' => 'new', 'next_follow_up_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('leads.index'));

        $lead = Lead::query()->firstOrFail();
        $this->assertDatabaseHas('work_items', ['lead_id' => $lead->id, 'type' => 'lead_follow_up', 'status' => 'open']);
        $this->actingAs($employee)->post(route('leads.convert', $lead))->assertRedirect();
        $lead->refresh();
        $this->assertSame('won', $lead->status);
        $this->assertNotNull($lead->converted_order_id);
    }

    public function test_super_admin_can_only_monitor_leads(): void
    {
        $filial = FilialModel::create(['name' => 'Lead monitoring', 'code' => 'LDM']);
        $superAdmin = $this->user('super_admin', $filial->id);
        $lead = Lead::create([
            'filial_id' => $filial->id,
            'assigned_to_id' => $superAdmin->id,
            'name' => 'Monitoring lead',
            'status' => 'new',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Faqat kuzatuv');

        $this->actingAs($superAdmin)
            ->post(route('leads.store'), ['name' => 'Blocked lead', 'status' => 'new'])
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->put(route('leads.update', $lead), ['name' => 'Changed lead', 'status' => 'contacted'])
            ->assertForbidden();
    }

    public function test_workflow_transition_creates_role_responsibility_work_item(): void
    {
        $filial = FilialModel::create(['name' => 'Workflow', 'code' => 'WRK']);
        $employee = $this->user('employee', $filial->id);
        $client = ClientsModel::create(['name' => 'Workflow Customer', 'phone_number' => '901112234', 'filial_id' => $filial->id]);
        $service = ServicesModel::create(['name' => 'Translation', 'price' => 100, 'deadline' => 2]);
        $document = DocumentsModel::create(['client_id' => $client->id, 'service_id' => $service->id, 'filial_id' => $filial->id, 'user_id' => $employee->id, 'assigned_to_id' => $employee->id, 'status_doc' => 'received', 'deadline_time' => 2, 'document_code' => 'WRK-1']);

        app(DocumentWorkflowService::class)->transition($document, 'waiting_documents', $employee);

        $this->assertDatabaseHas('work_items', ['document_id' => $document->id, 'assigned_to_id' => $employee->id, 'type' => 'workflow', 'status' => 'open']);
    }

    public function test_employee_refund_is_sent_for_manager_approval(): void
    {
        $filial = FilialModel::create(['name' => 'Approval', 'code' => 'APR']);
        $employee = $this->user('employee', $filial->id);
        $client = ClientsModel::create(['name' => 'Approval Customer', 'phone_number' => '901112235', 'filial_id' => $filial->id]);
        $order = Order::create(['client_id' => $client->id, 'filial_id' => $filial->id, 'created_by_id' => $employee->id, 'order_code' => 'ORD-APR-1', 'tracking_token' => str_repeat('a', 64), 'status' => 'paid', 'total_amount' => 100, 'paid_amount' => 100]);
        $payment = PaymentsModel::create(['order_id' => $order->id, 'filial_id' => $filial->id, 'amount' => 100, 'payment_type' => 'cash', 'status' => 'confirmed', 'confirmation_status' => 'confirmed', 'paid_by_admin_id' => $employee->id]);

        $approval = app(BusinessApprovalService::class)->requestRefund($payment, $employee, 20, 'Mijoz qaytarishni so‘radi');

        $this->assertNotNull($approval);
        $this->assertDatabaseHas('business_approvals', ['type' => 'refund', 'status' => 'pending', 'subject_id' => $payment->id]);
    }

    private function user(string $role, int $filialId): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);
        return $user;
    }
}
