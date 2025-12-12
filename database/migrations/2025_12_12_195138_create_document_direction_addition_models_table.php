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
        Schema::create('document_direction_addition', function (Blueprint $table) {
        Schema::dropIfExists('document_direction_addition');

            $table->id();
            $table->unsignedBigInteger('document_direction_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreign('document_direction_id')->references('id')->on('direction_type')->onDelete('cascade');
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
        Schema::dropIfExists('document_direction_addition');
    }
};
