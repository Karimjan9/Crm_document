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
        Schema::dropIfExists('holidays');

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->enum('type', ['national', 'company', 'regional', 'religious', 'other'])
                  ->default('national');
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('type');
            $table->index('is_recurring');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holidays');
    }
};
