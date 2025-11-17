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
        Schema::create('document_addons', function (Blueprint $table) {
        $table->id();
        $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
        $table->foreignId('service_addon_id')->constrained('service_addons');
        $table->decimal('addon_price',10,2);
        $table->integer('addon_deadline')->default(0);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_addons');
    }
};
