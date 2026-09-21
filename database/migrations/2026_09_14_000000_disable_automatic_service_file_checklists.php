<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Services are a price/catalogue concept. They must not automatically
     * impose document or file requirements on a client order.
     */
    public function up(): void
    {
        if (Schema::hasTable('service_checklist_items')) {
            DB::table('service_checklist_items')->update([
                'is_required' => false,
                'requires_file' => false,
                'is_active' => false,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('document_checklists')) {
            DB::table('document_checklists')->update([
                'is_required' => false,
                'requires_file' => false,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Requirements cannot be restored safely: a service may have had a
        // custom checklist before this catalogue behaviour was removed.
    }
};
