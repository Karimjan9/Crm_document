<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::create('document_type', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

   
    public function down()
    {
        Schema::dropIfExists('document_type');
    }
};
