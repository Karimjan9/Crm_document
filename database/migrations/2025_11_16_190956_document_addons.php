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
              Schema::dropIfExists('document_addons');
        $table->id();

        // Pivot FK-lar
        $table->unsignedBigInteger('document_id');
        $table->unsignedBigInteger('addon_id');

        // Qo‘shimcha ma’lumotlar
        $table->decimal('addon_price', 10, 2)->nullable();
        $table->integer('addon_deadline')->nullable();

        $table->timestamps();

        // Foreign keys
        $table->foreign('document_id')->references('id')->on('documents');
        $table->foreign('addon_id')->references('id')->on('service_addons');

    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
     
        Schema::table('document_addons', function (Blueprint $table) {
            $table->dropForeign(['document_id']);
            $table->dropForeign(['addon_id']);
          
        });
         Schema::dropIfExists('document_addons');
       
    
    }
};
