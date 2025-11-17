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
       Schema::create('documents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
        $table->foreignId('service_id')->constrained('services');
        $table->decimal('service_price',10,2);
        $table->decimal('addons_total_price',10,2)->default(0);
        $table->integer('deadline_time')->default(0);
        $table->decimal('final_price',10,2);
        $table->decimal('paid_amount',10,2)->default(0);
        $table->decimal('discount',10,2)->default(0);
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
        Schema::dropIfExists('documents');
    }
};
