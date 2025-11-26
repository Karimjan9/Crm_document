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
        Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
        $table->decimal('amount',10,2);
        $table->enum('payment_type', ['cash', 'card', 'online', 'admin_entry'])
                  ->default('admin_entry'); // cash, card, online, admin_entry
        $table->foreignId('paid_by_admin_id')->nullable()->constrained('users');
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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['document_id']);
            $table->dropForeign(['paid_by_admin_id']);
        });
        Schema::dropIfExists('payments');
    }
};
