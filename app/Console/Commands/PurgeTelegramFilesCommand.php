<?php

namespace App\Console\Commands;

use App\Models\TelegramMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeTelegramFilesCommand extends Command
{
    protected $signature = 'bot:purge-files';
    protected $description = 'Remove expired private Telegram attachment copies.';
    public function handle(): int
    {
        $count = 0;
        TelegramMessage::query()->whereNotNull('attachment_path')->whereNotNull('file_expires_at')->where('file_expires_at', '<=', now())->eachById(function (TelegramMessage $message) use (&$count): void {
            Storage::disk('private')->delete($message->attachment_path);
            $message->forceFill(['attachment_path' => null, 'attachment_meta' => [...($message->attachment_meta ?: []), 'purged_at' => now()->toIso8601String()]])->save(); $count++;
        });
        $this->info("{$count} ta Telegram fayli o‘chirildi.");
        return self::SUCCESS;
    }
}
