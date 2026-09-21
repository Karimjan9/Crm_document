<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void { Schema::table('leads', function (Blueprint $table): void { $table->uuid('bot_follow_up_event_id')->nullable()->after('bot_follow_up_sent_at'); $table->index('bot_follow_up_event_id'); }); }
    public function down(): void { Schema::table('leads', function (Blueprint $table): void { $table->dropIndex(['bot_follow_up_event_id']); $table->dropColumn('bot_follow_up_event_id'); }); }
};
