<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use Illuminate\Http\Request;

class SecurityAuditController extends Controller
{
    public function logs(Request $request)
    {
        $data = $request->validate([
            'event' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'integer'],
            'ip' => ['nullable', 'ip'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return response()->json(AuditLog::query()
            ->when($data['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($data['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($data['ip'] ?? null, fn ($query, $ip) => $query->where('ip_address', $ip))
            ->when($data['from'] ?? null, fn ($query, $from) => $query->where('created_at', '>=', $from))
            ->when($data['to'] ?? null, fn ($query, $to) => $query->where('created_at', '<=', $to))
            ->latest('id')
            ->paginate(min(max($request->integer('per_page', 50), 1), 100)));
    }

    public function alerts(Request $request)
    {
        return response()->json(SecurityAlert::query()
            ->latest('last_seen_at')
            ->paginate(min(max($request->integer('per_page', 50), 1), 100)));
    }
}
