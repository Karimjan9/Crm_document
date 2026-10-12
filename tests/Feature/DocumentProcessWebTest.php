<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentFileModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\ServiceChecklistItem;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use App\Services\OrderCaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentProcessWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_finish_the_checklist_and_qa_chain_through_web_routes(): void
    {
        [$owner, $reviewer, $admin, $document] = $this->fixture();
        $order = app(OrderCaseService::class)->createForDocument($document);
        app(OrderCaseService::class)->recordPayment($order, 100, 'cash', $owner, $document->id);
        $this->actingAs($admin)->postJson(route('documents.process.assignment', $document), ['assigned_to_id' => $owner->id, 'qa_user_id' => $reviewer->id])->assertOk();
        $item = $document->checklists()->firstOrFail();
        $this->actingAs($owner)->get(route('documents.process.show', $document))->assertOk()->assertSee('Keyingi bosqich');
        $this->actingAs($reviewer)->postJson(route('documents.process.qa', $document), ['result' => 'passed'])->assertUnprocessable();
        $this->actingAs($owner)->postJson(route('documents.process.checklist', [$document, $item]), ['is_completed' => true])->assertOk();
        $this->actingAs($owner)->postJson(route('documents.process.status', $document), ['status' => 'waiting_review'])->assertOk();
        $this->actingAs($reviewer)->get(route('documents.process.show', $document))->assertOk()->assertSee('QA natijasini saqlash');
        $this->actingAs($owner)->postJson(route('documents.process.qa', $document), ['result' => 'passed'])->assertForbidden();
        $this->actingAs($reviewer)->postJson(route('documents.process.qa', $document), ['result' => 'passed'])->assertOk();
        $this->actingAs($owner)->postJson(route('documents.process.status', $document), ['status' => 'ready_for_delivery'])->assertOk();
        $this->assertSame('ready_for_delivery', $document->fresh()->workflow_status);
        $this->assertSame('ready_for_delivery', $order->fresh()->status);
        $this->actingAs($reviewer)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($reviewer)->postJson(route('orders.payments.store', $order), ['amount' => 1, 'payment_type' => 'cash'])->assertForbidden();
    }

    public function test_pending_margin_approval_rolls_back_document_and_order_progress_together(): void
    {
        [$owner, $reviewer, $admin, $document] = $this->fixture();
        $orders = app(OrderCaseService::class);
        $order = $orders->createForDocument($document);
        $orders->recordPayment($order, 100, 'cash', $owner, $document->id);
        $document->forceFill(['qa_user_id' => $reviewer->id])->save();
        $item = $document->checklists()->firstOrFail();
        $this->actingAs($owner)->postJson(route('documents.process.checklist', [$document, $item]), ['is_completed' => true])->assertOk();
        $this->postJson(route('documents.process.status', $document), ['status' => 'waiting_review'])->assertOk();
        $this->actingAs($reviewer)->postJson(route('documents.process.qa', $document), ['result' => 'passed'])->assertOk();
        $order->approvals()->create(['filial_id' => $order->filial_id, 'type' => 'margin_exception', 'status' => 'pending', 'required_role' => 'admin_manager', 'requested_by_id' => $owner->id, 'reason' => 'Low margin']);
        $this->actingAs($owner)->postJson(route('documents.process.status', $document), ['status' => 'ready_for_delivery'])->assertUnprocessable();
        $this->assertSame('waiting_review', $document->fresh()->workflow_status);
        $this->assertNotSame('ready_for_delivery', $order->fresh()->status);
    }

    public function test_qa_files_are_private_and_process_controls_stay_in_the_document_branch(): void
    {
        [$owner, $reviewer, $admin, $document] = $this->fixture();
        $document->forceFill(['qa_user_id' => $reviewer->id])->save();
        Storage::fake('private');
        Storage::disk('private')->put('test.pdf', '%PDF-test');
        $file = $document->files()->firstOrFail();
        $this->actingAs($reviewer)->get(route('document-files.show', $file))->assertOk();
        $outside = $this->user('employee', FilialModel::create(['name' => 'Outside', 'code' => 'OUT'])->id);
        $this->actingAs($outside)->get(route('documents.process.show', $document))->assertForbidden();
        $this->actingAs($outside)->get(route('document-files.show', $file))->assertForbidden();
        $this->actingAs($admin)->postJson(route('documents.process.assignment', $document), ['assigned_to_id' => $outside->id])->assertUnprocessable();
        $super = $this->user('super_admin', $document->filial_id);
        $this->actingAs($super)->postJson(route('documents.process.status', $document), ['status' => 'completed'])->assertNotFound();
    }

    public function test_a_missing_file_added_later_can_advance_the_waiting_document(): void
    {
        [$owner, $reviewer, $admin, $document] = $this->fixture();
        $document->checklists()->delete();
        ServiceChecklistItem::where('service_id', $document->service_id)->delete();
        $document->forceFill(['status_doc' => 'waiting_documents', 'paid_amount' => 0])->save();
        $result = app(DocumentWorkflowService::class)->syncDerivedStatus($document, $owner);
        $this->assertSame('awaiting_payment', $result->workflow_status);
        $this->assertDatabaseHas('document_status_histories', ['document_id' => $document->id, 'from_status' => 'waiting_documents', 'to_status' => 'received']);
    }

    public function test_superadmin_can_open_order_details_without_a_missing_create_route(): void
    {
        [$owner, $reviewer, $admin, $document] = $this->fixture();
        $order = app(OrderCaseService::class)->createForDocument($document);
        $super = $this->user('super_admin', $document->filial_id);
        $this->actingAs($super)->get(route('orders.show', $order))->assertOk()->assertDontSee('Hujjat qo‘shish');
    }

    private function fixture(): array
    {
        $branch = FilialModel::create(['name' => 'Web process', 'code' => 'PRC']);
        $owner = $this->user('employee', $branch->id);
        $reviewer = $this->user('employee', $branch->id);
        $admin = $this->user('admin_filial', $branch->id);
        $client = ClientsModel::create(['name' => 'Web client', 'phone_number' => '901234789', 'filial_id' => $branch->id]);
        $service = ServicesModel::create(['name' => 'Process service', 'price' => 100, 'deadline' => 1]);
        ServiceChecklistItem::create(['service_id' => $service->id, 'code' => 'application', 'title' => 'Application', 'is_required' => true, 'requires_file' => false, 'is_active' => true]);
        $document = DocumentsModel::create(['client_id' => $client->id, 'service_id' => $service->id, 'filial_id' => $branch->id, 'user_id' => $owner->id, 'assigned_to_id' => $owner->id, 'document_code' => 'PRC-1', 'status_doc' => 'received', 'service_price' => 100, 'final_price' => 100, 'paid_amount' => 100, 'deadline_time' => 1]);
        app(DocumentWorkflowService::class)->initialize($document, $owner);
        app(\App\Services\DocumentChecklistService::class)->syncForService($document);
        DocumentFileModel::create(['document_id' => $document->id, 'original_name' => 'test.pdf', 'file_path' => 'test.pdf', 'file_type' => 'application/pdf', 'file_size' => 10]);
        return [$owner, $reviewer, $admin, $document];
    }

    private function user(string $role, int $branch): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['filial_id' => $branch]);
        $user->assignRole($role);
        return $user;
    }
}
