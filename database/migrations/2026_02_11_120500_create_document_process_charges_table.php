<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_process_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('charge_type', 32);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('days')->default(0);
            $table->string('name')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'charge_type']);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('document_process_charges', function (Blueprint $table) {
            $table->dropForeign(['document_id']);
            $table->dropIndex(['document_id', 'charge_type']);
        });
        Schema::dropIfExists('document_process_charges');
    }
};
