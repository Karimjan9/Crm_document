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
        Schema::create('expense_admin', function (Blueprint $table) {
            //   Schema::dropIfExists('expense_admin');
            $table->id();
            
            // User ID bilan foreign key
            $table->foreignId('user_id')
                  ->constrained('users')   // 'users' jadvali bilan bog‘laydi
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->decimal('amount', 15, 2);

            // Filial ID bilan foreign key
            $table->foreignId('filial_id')
                  ->constrained('filial') // 'filials' jadvali bilan bog‘laydi
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->text('description')->nullable();
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
        Schema::dropIfExists('expense_admin');
    }
};
