<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeDuplicateLogins();
        $this->convertPhoneToString();
        $this->addUniqueLogin();
        $this->addFilialForeignKeyToDocuments();
        $this->deduplicateAndConstrainPivots();
        $this->addNonNegativeChecks();
    }

    public function down(): void
    {
        $this->dropNonNegativeChecks();

        if (Schema::hasTable('document_direction_addons')) {
            Schema::table('document_direction_addons', function (Blueprint $table): void {
                $table->dropUnique('document_direction_addons_document_id_addon_id_unique');
            });
        }

        if (Schema::hasTable('document_type_addons')) {
            Schema::table('document_type_addons', function (Blueprint $table): void {
                $table->dropUnique('document_type_addons_document_id_addon_id_unique');
            });
        }

        if (Schema::hasTable('document_addons')) {
            Schema::table('document_addons', function (Blueprint $table): void {
                $table->dropUnique('document_addons_document_id_addon_id_unique');
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table): void {
                $table->dropForeign('documents_filial_id_foreign');
                $table->bigInteger('filial_id')->change();
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_login_unique');
                $table->bigInteger('phone')->change();
            });
        }
    }

    protected function normalizeDuplicateLogins(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $duplicateLogins = DB::table('users')
            ->select('login')
            ->whereNotNull('login')
            ->groupBy('login')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('login');

        foreach ($duplicateLogins as $login) {
            $users = DB::table('users')
                ->where('login', $login)
                ->orderBy('id')
                ->get(['id', 'login']);

            foreach ($users->skip(1) as $user) {
                $candidate = Str::limit((string) $login, 230, '') . '_' . $user->id;
                $suffix = 1;

                while (DB::table('users')->where('login', $candidate)->exists()) {
                    $candidate = Str::limit((string) $login, 220, '') . '_' . $user->id . '_' . $suffix++;
                }

                DB::table('users')->where('id', $user->id)->update(['login' => $candidate]);
            }
        }
    }

    protected function convertPhoneToString(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone', 20)->change();
            });
        }
    }

    protected function addUniqueLogin(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'login')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('login', 'users_login_unique');
            });
        }
    }

    protected function addFilialForeignKeyToDocuments(): void
    {
        if (!Schema::hasTable('documents') || !Schema::hasTable('filial')) {
            return;
        }

        $hasOrphans = DB::table('documents as documents')
            ->leftJoin('filial', 'filial.id', '=', 'documents.filial_id')
            ->whereNull('filial.id')
            ->exists();

        if ($hasOrphans) {
            throw new RuntimeException('documents.filial_id contains references to missing filial records.');
        }

        Schema::table('documents', function (Blueprint $table): void {
            $table->unsignedBigInteger('filial_id')->change();
            $table->foreign('filial_id', 'documents_filial_id_foreign')
                ->references('id')
                ->on('filial')
                ->restrictOnDelete();
        });
    }

    protected function deduplicateAndConstrainPivots(): void
    {
        foreach (['document_addons', 'document_type_addons', 'document_direction_addons'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            DB::table($tableName)
                ->select(['document_id', 'addon_id', DB::raw('MIN(id) as keep_id')])
                ->groupBy(['document_id', 'addon_id'])
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->each(function (object $duplicate) use ($tableName): void {
                    DB::table($tableName)
                        ->where('document_id', $duplicate->document_id)
                        ->where('addon_id', $duplicate->addon_id)
                        ->where('id', '<>', $duplicate->keep_id)
                        ->delete();
                });

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->unique(
                    ['document_id', 'addon_id'],
                    $tableName . '_document_id_addon_id_unique'
                );
            });
        }
    }

    protected function addNonNegativeChecks(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE documents ADD CONSTRAINT documents_non_negative_amounts '
            .'CHECK (COALESCE(service_price, 0) >= 0 '
            .'AND COALESCE(addons_total_price, 0) >= 0 '
            .'AND COALESCE(final_price, 0) >= 0 '
            .'AND COALESCE(paid_amount, 0) >= 0 '
            .'AND COALESCE(discount, 0) >= 0 '
            .'AND COALESCE(deadline_time, 0) >= 0)'
        );
        DB::statement(
            'ALTER TABLE documents ADD CONSTRAINT documents_paid_not_above_final '
            .'CHECK (final_price IS NULL OR COALESCE(paid_amount, 0) <= final_price)'
        );
        DB::statement(
            'ALTER TABLE payments ADD CONSTRAINT payments_amount_positive CHECK (amount > 0)'
        );
    }

    protected function dropNonNegativeChecks(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE payments DROP CHECK payments_amount_positive');
        DB::statement('ALTER TABLE documents DROP CHECK documents_paid_not_above_final');
        DB::statement('ALTER TABLE documents DROP CHECK documents_non_negative_amounts');
    }
};
