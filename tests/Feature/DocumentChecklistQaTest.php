<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentChecklist;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\ServiceChecklistItem;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\DocumentChecklistService;
use App\Services\DocumentWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DocumentChecklistQaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_required_checklist_blocks_processing_until_completed(): void
    {
        [$employee, $document] = $this->fixture();
        $checklist = ServiceChecklistItem::create([
            'service_id' => $document->service_id,
            'code' => 'passport',
            'title' => 'Passport',
            'is_required' => true,
            'requires_file' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $workflow = app(DocumentWorkflowService::class);
        $checklists = app(DocumentChecklistService::class);

        $this->expectException(ValidationException::class);
        try {
            $workflow->transition($document, 'in_processing', $employee);
        } finally {
            $snapshot = DocumentChecklist::query()->where('document_id', $document->id)->firstOrFail();
            $this->assertFalse($snapshot->is_completed);
            $checklists->toggle($snapshot, true, $employee);
            $workflow->transition($document->refresh(), 'in_processing', $employee);
            $this->assertSame('in_processing', $document->refresh()->workflow_status);
            $this->assertDatabaseHas('document_checklists', [
                'document_id' => $document->id,
                'service_checklist_item_id' => $checklist->id,
                'is_completed' => true,
            ]);
        }
    }

    public function test_qa_pass_is_required_before_ready_and_failure_is_recorded(): void
    {
        [$employee, $document] = $this->fixture();
        ServiceChecklistItem::create([
            'service_id' => $document->service_id,
            'code' => 'application_form',
            'title' => 'Application form',
            'is_required' => true,
            'requires_file' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $checklists = app(DocumentChecklistService::class);
        $workflow = app(DocumentWorkflowService::class);
        $snapshot = $checklists->syncForService($document)->checklists->firstOrFail();
        $checklists->toggle($snapshot, true, $employee);
        $document->forceFill([
            'paid_amount' => 100,
            'qa_user_id' => $employee->id,
        ])->save();

        $workflow->transition($document->refresh(), 'in_processing', $employee);
        $workflow->transition($document->refresh(), 'waiting_review', $employee);
        $checklists->review($document->refresh(), 'failed', $employee, 'Imzo noaniq.', 'Qayta skanerlang.');

        $this->assertSame('qa_failed', $document->refresh()->workflow_status);
        $this->assertDatabaseHas('document_qa_reviews', [
            'document_id' => $document->id,
            'result' => 'failed',
            'reason' => 'Imzo noaniq.',
        ]);

        $checklists->review($document->refresh(), 'passed', $employee, 'Tuzatildi.');
        $this->assertSame('passed', $checklists->qaStatus($document->refresh()));

        $workflow->transition($document->refresh(), 'in_processing', $employee);
        $workflow->transition($document->refresh(), 'waiting_review', $employee);
        $workflow->transition($document->refresh(), 'ready_for_delivery', $employee);
        $this->assertSame('ready_for_delivery', $document->refresh()->workflow_status);
    }

    private function fixture(): array
    {
        $filial = FilialModel::create(['name' => 'Checklist', 'code' => 'CHK']);
        Role::findOrCreate('employee', 'web');
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $employee->assignRole('employee');
        $client = ClientsModel::create([
            'name' => 'Checklist Client',
            'phone_number' => '901234599',
            'filial_id' => $filial->id,
        ]);
        $service = ServicesModel::create([
            'name' => 'Checklist Service',
            'price' => 100,
            'deadline' => 2,
        ]);
        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'filial_id' => $filial->id,
            'user_id' => $employee->id,
            'document_code' => 'CHK-' . random_int(1000, 9999),
            'service_price' => 100,
            'final_price' => 100,
            'paid_amount' => 0,
            'status_doc' => 'received',
            'process_mode' => 'service',
            'deadline_time' => 2,
        ]);
        app(DocumentWorkflowService::class)->initialize($document, $employee);

        return [$employee, $document];
    }
}
