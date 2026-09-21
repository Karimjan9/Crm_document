<?php

namespace App\Http\Controllers;

use App\Models\TelegramMessage;
use App\Models\User;
use App\Services\WatermarkedDownloadService;
use Illuminate\Support\Facades\Storage;

class TelegramMessageFileController extends Controller
{
    public function show(TelegramMessage $telegramMessage, WatermarkedDownloadService $downloads)
    {
        $user = request()->user();
        abort_unless($this->canView($telegramMessage, $user), 403);
        abort_unless($telegramMessage->attachment_path && Storage::disk('private')->exists($telegramMessage->attachment_path), 404);
        return $downloads->download($telegramMessage->attachment_path, (string) data_get($telegramMessage->attachment_meta, 'file_name', 'telegram-document'), $user);
    }
    private function canView(TelegramMessage $message, User $user): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) return true;
        $message->loadMissing('lead');
        if (! $message->lead || ! $user->hasAnyRole(['employee', 'admin_filial'])) return false;
        if ((int) $user->filial_id !== (int) $message->lead->filial_id) return false;
        return ! $user->hasRole('employee') || (int) $message->lead->assigned_to_id === (int) $user->id;
    }
}
