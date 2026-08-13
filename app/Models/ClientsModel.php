<?php

namespace App\Models;

use App\Models\DocumentsModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ClientsModel extends Model
{
    use HasFactory;
    protected $table = 'clients';

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'telegram_chat_id',
        'whatsapp_phone',
        'description',
        'filial_id',
    ];

    public function documents()
    {
        return $this->hasMany(DocumentsModel::class, 'client_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    /**
     * Limit client data to the authenticated user's tenant.
     * Super-admin and manager roles intentionally have global visibility.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereKey(-1);
        }

        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return $query;
        }

        if ($user->filial_id === null) {
            return $query->whereKey(-1);
        }

        return $query->where('filial_id', $user->filial_id);
    }

    public function isVisibleTo(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return true;
        }

        return $user->filial_id !== null && (int) $this->filial_id === (int) $user->filial_id;
    }
}
