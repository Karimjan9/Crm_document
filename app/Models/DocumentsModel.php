<?php

namespace App\Models;


use Carbon\Carbon;
use App\Support\WorkdayCalendar;
use App\Models\ClientsModel;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\ServiceAddonModel;
use App\Models\DocumentTypeAdditionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentsModel extends Model
{
    protected $table='documents';
    use HasFactory;

    public const STATUSES = [
        'draft',
        'waiting_documents',
        'received',
        'priced',
        'awaiting_payment',
        'partially_paid',
        'in_processing',
        'waiting_review',
        'qa_failed',
        'ready_for_delivery',
        'courier_sent',
        'delivered',
        'completed',
        'cancelled',
        'refunded',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Qoralama',
        'waiting_documents' => 'Hujjatlar kutilmoqda',
        'received' => 'Qabul qilindi',
        'priced' => 'Narx belgilandi',
        'awaiting_payment' => 'To‘lov kutilmoqda',
        'partially_paid' => 'Qisman to‘langan',
        'in_processing' => 'Jarayonda',
        'waiting_review' => 'Tekshiruv kutilmoqda',
        'qa_failed' => 'Tuzatish kerak',
        'ready_for_delivery' => 'Topshirishga tayyor',
        'courier_sent' => 'Kuryerga berildi',
        'delivered' => 'Yetkazildi',
        'completed' => 'Yakunlandi',
        'cancelled' => 'Bekor qilindi',
        'refunded' => 'Qaytarildi',
        // Legacy values are kept for old reports and integrations.
        'process' => 'Jarayonda (legacy)',
        'finish' => 'Yakunlangan (legacy)',
    ];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public const QUEUES = [
        'intake',
        'pricing',
        'processing',
        'review',
        'correction',
        'delivery',
        'general',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'client_id',
        'order_id',
        'service_id',
        'document_type_id',
        'direction_type_id',
        'consulate_type_id',
        'service_price',
        'addons_total_price',
        'deadline_time',
        'final_price',
        'paid_amount',
        'discount',
        'pricing_snapshot',
        'pricing_context',
        'pricing_locked_at',
        'discount_approval_status',
        'user_id',
        'assigned_to_id',
        'qa_user_id',
        'description',
        'filial_id',
        'document_code',
        'status_doc',
        'priority',
        'queue',
        'estimated_workload_minutes',
        'rework_count',
        'assignment_source',
        'last_status_changed_at',
        'process_mode',
        'apostil_group1_id',
        'apostil_group2_id',
        'consul_id',
    ];

    protected $casts = [
        'service_price' => 'decimal:2',
        'addons_total_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'pricing_snapshot' => 'array',
        'pricing_context' => 'array',
        'pricing_locked_at' => 'datetime',
        'estimated_workload_minutes' => 'integer',
        'rework_count' => 'integer',
        'last_status_changed_at' => 'datetime',
    ];


    public function client() {
        return $this->belongsTo(ClientsModel::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function service() {
        return $this->belongsTo(ServicesModel::class);
    }

   public function addons()
    {
        return $this->belongsToMany(
            ServiceAddonModel::class,
            'document_addons',
            'document_id',
            'addon_id'
        )->withPivot('addon_price', 'addon_deadline');
    }

    public function document_type_addons()
    {
        return $this->belongsToMany(
            DocumentTypeAdditionModel::class,
            'document_type_addons',
            'document_id',
            'addon_id'
        )->withPivot('addon_price');
    }

    public function document_direction_addons()
    {
        return $this->belongsToMany(
            DocumentDirectionAdditionModel::class,
            'document_direction_addons',
            'document_id',
            'addon_id'
        )->withPivot('addon_price');
    }

    public function payments() {
        return $this->hasMany(PaymentsModel::class,'document_id','id');
    }
    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id')->withTrashed();
    }

    public function qaUser()
    {
        return $this->belongsTo(User::class, 'qa_user_id')->withTrashed();
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }



public function getDeadlineRemainingAttribute()
{
    return WorkdayCalendar::formatRemaining($this->deadline_due_at);
}

public function getDeadlineDueAtAttribute(): Carbon
{
    return WorkdayCalendar::resolveDueAt($this->created_at, $this->deadline_time);
}

public function documentType()
{
    return $this->belongsTo(DocumentTypeModel::class, 'document_type_id');
}

public function directionType()
{
    return $this->belongsTo(DirectionTypeModel::class, 'direction_type_id');
}

public function consulateType()
{
    return $this->belongsTo(ConsulationTypeModel::class, 'consulate_type_id');
}

    public function files()
    {
        return $this->hasMany(DocumentFileModel::class, 'document_id');
    }

    public function processCharges()
    {
        return $this->hasMany(DocumentProcessChargeModel::class, 'document_id');
    }

    public function pricingApprovals()
    {
        return $this->hasMany(PricingApproval::class, 'document_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(DocumentStatusHistory::class, 'document_id')->latest();
    }

    public function assignmentHistories()
    {
        return $this->hasMany(DocumentAssignmentHistory::class, 'document_id')->latest();
    }

    public function checklists()
    {
        return $this->hasMany(DocumentChecklist::class, 'document_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function qaReviews()
    {
        return $this->hasMany(DocumentQaReview::class, 'document_id')->latest();
    }

    public function latestQaReview()
    {
        return $this->hasOne(DocumentQaReview::class, 'document_id')->latestOfMany();
    }

    public function getStatusLabelAttribute(): string
    {
        $status = app(\App\Services\DocumentWorkflowService::class)->normalizeStatus($this->status_doc);

        return self::STATUS_LABELS[$status] ?? self::STATUS_LABELS[$this->status_doc] ?? (string) $this->status_doc;
    }

    public function getWorkflowStatusAttribute(): string
    {
        return app(\App\Services\DocumentWorkflowService::class)->normalizeStatus($this->status_doc);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->workflow_status) {
            'draft', 'waiting_documents' => 'secondary',
            'priced', 'awaiting_payment', 'partially_paid' => 'warning',
            'in_processing' => 'primary',
            'waiting_review' => 'info',
            'qa_failed', 'cancelled', 'refunded' => 'danger',
            'ready_for_delivery', 'courier_sent', 'delivered', 'completed' => 'success',
            default => 'secondary',
        };
    }

    public function courierAssignment()
    {
        return $this->hasOne(DocumentCourier::class, 'document_id');
    }

    public function custodyEvents()
    {
        return $this->hasMany(DocumentCustodyEvent::class, 'document_id')->latest('event_at');
    }

}
