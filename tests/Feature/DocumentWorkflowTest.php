<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_document_status_transition_records_history_and_rework_count(): void
    {
        [$filial, $employee, $document] = $this->documentFixture();
        $workflow = app(DocumentWorkflowService::class);

        $workflow->transition($document, 'in_processing', $employee, 'Ish boshlandi.');
        $workflow->transition($document->refresh(), 'waiting_review', $employee, 'Tekshiruvga yuborildi.');
        $workflow->transition($document->refresh(), 'qa_failed', $employee, 'Fayl tuzatilsin.', 'QA izohi');

        $this->assertSame('qa_failed', $document->refresh()->workflow_status);
        $this->assertSame(1, (int) $document->rework_count);
        $this->assertDatabaseHas('document_status_histories', [
            'document_id' => $document->id,
            'from_status' => 'waiting_review',
            'to_status' => 'qa_failed',
            'changed_by_id' => $employee->id,
            'reason' => 'Fayl tuzatilsin.',
            'comment' => 'QA izohi',
        ]);
    }

    public function test_workflow_rejects_invalid_transition_and_cross_branch_assignment(): void
    {
        [$filial, $employee, $document] = $this->documentFixture();
        $otherFilial = FilialModel::create(['name' => 'Other', 'code' => 'OTH']);
        $otherEmployee = $this->user('employee', $otherFilial->id);
        $workflow = app(DocumentWorkflowService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            $workflow->transition($document, 'completed', $employee);
        } finally {
            try {
                $workflow->assign($document, $otherEmployee->id, null, 'processing', 'normal', 60, $employee);
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('assigned_to_id', $exception->errors());
            }
        }
    }

    public function test_auto_assign_uses_lightest_worker_and_dashboard_is_scoped(): void
    {
        [$filial, $employee, $document] = $this->documentFixture();
        $second = $this->user('employee', $filial->id);
        $workflow = app(DocumentWorkflowService::class);
        $workflow->assign($document, $employee->id, null, 'processing', 'normal', 240, $employee);

        $secondDocument = $this->documentFixture($filial, $employee)[2];
        $workflow->autoAssign($secondDocument, $employee);

        $this->assertSame($second->id, (int) $secondDocument->refresh()->assigned_to_id);
        $dashboard = $workflow->dashboard($employee);
        $this->assertSame(2, $dashboard['metrics']['total']);
        $this->assertNotEmpty($dashboard['workers']);
        $this->assertNotEmpty(collect($dashboard['columns'])->firstWhere('key', 'waiting_documents'));
    }

    public function test_workflow_monitor_is_available_only_to_super_admin(): void
    {
        [$filial, $employee, $document] = $this->documentFixture();

        $this->actingAs($employee)
            ->get(route('documents.workflow.index'))
            ->assertNotFound();

        $this->actingAs($employee)
            ->getJson(route('documents.workflow.data'))
            ->assertNotFound();

        $superAdmin = $this->user('super_admin', $filial->id);

        $this->actingAs($superAdmin)
            ->get(route('documents.workflow.index'))
            ->assertOk()
            ->assertSee('Kanban doska');

        $this->actingAs($superAdmin)
            ->getJson(route('documents.workflow.data'))
            ->assertOk()
            ->assertJsonStructure(['data' => ['metrics', 'columns', 'workers', 'statuses']]);

        $this->actingAs($superAdmin)
            ->post("/documents/{$document->id}/workflow-status", ['status' => 'in_processing'])
            ->assertNotFound();
    }

    private function documentFixture(?FilialModel $filial = null, ?User $employee = null): array
    {
        $filial ??= FilialModel::create(['name' => 'Workflow', 'code' => 'WFL']);
        $employee ??= $this->user('employee', $filial->id);
        $client = ClientsModel::create(['name' => 'Workflow Client ' . uniqid(), 'phone_number' => '90' . random_int(1000000, 9999999), 'filial_id' => $filial->id]);
        $service = ServicesModel::create(['name' => 'Workflow Service ' . uniqid(), 'price' => 100, 'deadline' => 2]);
        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'filial_id' => $filial->id,
            'user_id' => $employee->id,
            'document_code' => 'WFL-' . random_int(1000, 9999),
            'service_price' => 100,
            'final_price' => 100,
            'paid_amount' => 0,
            'status_doc' => 'received',
            'process_mode' => 'service',
            'deadline_time' => 2,
        ]);
        app(DocumentWorkflowService::class)->initialize($document, $employee);

        return [$filial, $employee, $document];
    }

    private function user(string $role, int $filialId): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);
        return $user;
    }
}
