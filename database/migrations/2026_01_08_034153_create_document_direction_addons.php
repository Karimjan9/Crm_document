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
        Schema::create('document_direction_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('addon_id');
            $table->decimal('addon_price', 10, 2)->nullable();
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('addon_id')->references('id')->on('document_direction_addition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_direction_addons');
    }
};
