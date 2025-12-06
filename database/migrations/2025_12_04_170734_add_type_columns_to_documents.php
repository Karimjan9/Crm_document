<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {

            $table->unsignedBigInteger('document_type_id')->nullable()->after('service_id');
            $table->unsignedBigInteger('direction_type_id')->nullable()->after('document_type_id');
            $table->unsignedBigInteger('consulate_type_id')->nullable()->after('direction_type_id');

            $table->foreign('document_type_id')->references('id')->on('document_type');
            $table->foreign('direction_type_id')->references('id')->on('direction_type');
            $table->foreign('consulate_type_id')->references('id')->on('consulates_type');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {

            $table->dropForeign(['document_type_id']);
            $table->dropForeign(['direction_type_id']);
            $table->dropForeign(['consulate_type_id']);

            $table->dropColumn(['document_type_id','direction_type_id','consulate_type_id']);
        });
    }
};
