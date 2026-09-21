<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    public const TYPES = [
        'university',
        'visa_agency',
        'hr_company',
        'travel_agency',
        'notary',
        'consulting',
        'employment_agency',
        'other',
    ];

    public const STATUSES = ['active', 'suspended'];

    protected $fillable = [
        'company_name',
        'code',
        'type',
        'contact_name',
        'account_manager_id',
        'email',
        'phone',
        'tax_id',
        'billing_email',
        'discount_percent',
        'credit_limit',
        'minimum_margin_percent',
        'payment_terms_days',
        'contract_starts_at',
        'contract_ends_at',
        'currency',
        'status',
        'brand_name',
        'brand_logo_url',
        'brand_primary_color',
        'brand_secondary_color',
        'tracking_title',
        'notes',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'minimum_margin_percent' => 'decimal:2',
        'contract_starts_at' => 'date',
        'contract_ends_at' => 'date',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id')->withTrashed();
    }

    public function filials()
    {
        return $this->belongsToMany(
            FilialModel::class,
            'partner_filials',
            'partner_id',
            'filial_id'
        )->withPivot('is_active')->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(PartnerInvoice::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(PartnerApiKey::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function canUseFilial(int $filialId): bool
    {
        return $this->filials()
            ->whereKey($filialId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function trackingPrimaryColor(): string
    {
        return $this->validColor($this->brand_primary_color, '#2563eb');
    }

    public function trackingSecondaryColor(): string
    {
        return $this->validColor($this->brand_secondary_color, '#0f172a');
    }

    public function trackingBrandName(): string
    {
        return trim((string) ($this->brand_name ?: $this->company_name));
    }

    private function validColor(?string $value, string $fallback): string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)
            ? $value
            : $fallback;
    }
}
