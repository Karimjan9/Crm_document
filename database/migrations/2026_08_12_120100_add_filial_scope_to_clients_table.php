<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clients') || Schema::hasColumn('clients', 'filial_id')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedBigInteger('filial_id')->nullable()->after('id');
            $table->index('filial_id', 'clients_filial_id_index');
            $table->foreign('filial_id', 'clients_filial_id_foreign')
                ->references('id')
                ->on('filial')
                ->nullOnDelete();
        });

        // Existing clients that were used by exactly one branch can be safely
        // assigned to that branch. Ambiguous legacy records remain global
        // legacy records and are intentionally unavailable to branch users
        // until an administrator assigns them explicitly.
        if (Schema::hasTable('documents')) {
            $clientFilials = DB::table('documents')
                ->select('client_id', DB::raw('MIN(filial_id) as filial_id'), DB::raw('COUNT(DISTINCT filial_id) as filial_count'))
                ->whereNotNull('client_id')
                ->whereNotNull('filial_id')
                ->groupBy('client_id')
                ->havingRaw('COUNT(DISTINCT filial_id) = 1')
                ->get();

            foreach ($clientFilials as $clientFilial) {
                DB::table('clients')
                    ->where('id', $clientFilial->client_id)
                    ->whereNull('filial_id')
                    ->update(['filial_id' => $clientFilial->filial_id]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients') || ! Schema::hasColumn('clients', 'filial_id')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropForeign('clients_filial_id_foreign');
            $table->dropIndex('clients_filial_id_index');
            $table->dropColumn('filial_id');
        });
    }
};
