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

        $table->unsignedBigInteger('client_id');
        $table->unsignedBigInteger('service_id');

        $table->decimal('service_price', 10, 2)->nullable();
        $table->decimal('addons_total_price', 10, 2)->default(0);

        $table->integer('deadline_time')->nullable();

        $table->decimal('final_price', 10, 2)->nullable();
        $table->decimal('paid_amount', 10, 2)->default(0);

        $table->decimal('discount', 10, 2)->default(0);

        $table->unsignedBigInteger('user_id');
        $table->text('description')->nullable();
        $table->bigInteger('filial_id');
        $table->timestamps();

        // Foreign Keys
        $table->foreign('client_id')->references('id')->on('clients');
        $table->foreign('service_id')->references('id')->on('services');
        $table->foreign('user_id')->references('id')->on('users');

    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('documents', function (Blueprint $table) {
       $table->dropForeign('documents_client_id_foreign');
        $table->dropForeign('documents_service_id_foreign');
        $table->dropForeign('documents_user_id_foreign');
    });
//   Schema::dropIfExists('document_addons');

    Schema::dropIfExists('documents');
}
};
