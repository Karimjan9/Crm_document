<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_direction_addition', function (Blueprint $table) {
                // $table->decimal('amount',10,2)->after('name');
                $table->bigInteger('amount')->after('name');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_direction_addition', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
