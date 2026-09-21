<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->timestamp('bot_follow_up_sent_at')->nullable()->after('quoted_at');
            $table->index(['status', 'quoted_at', 'bot_follow_up_sent_at'], 'lead_bot_follow_up_index');
        });
    }
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex('lead_bot_follow_up_index');
            $table->dropColumn('bot_follow_up_sent_at');
        });
    }
};
