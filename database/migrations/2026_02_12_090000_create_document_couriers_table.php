<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_couriers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->unique();
            $table->unsignedBigInteger('courier_id');
            $table->unsignedBigInteger('sent_by_id');
            $table->string('status', 20)->default('sent');
            $table->text('sent_comment')->nullable();
            $table->text('courier_comment')->nullable();
            $table->text('return_comment')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('users');
            $table->foreign('sent_by_id')->references('id')->on('users');
            $table->index(['courier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_couriers');
    }
};
