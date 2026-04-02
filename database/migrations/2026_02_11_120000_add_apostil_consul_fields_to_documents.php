<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('process_mode', ['apostil', 'consul'])
                ->nullable()
                ->after('consulate_type_id');

            $table->unsignedBigInteger('apostil_group1_id')
                ->nullable()
                ->after('process_mode');

            $table->unsignedBigInteger('apostil_group2_id')
                ->nullable()
                ->after('apostil_group1_id');

            $table->unsignedBigInteger('consul_id')
                ->nullable()
                ->after('apostil_group2_id');

            $table->index('process_mode');

            $table->foreign('apostil_group1_id')
                ->references('id')->on('apostil_static')
                ->onDelete('set null');

            $table->foreign('apostil_group2_id')
                ->references('id')->on('apostil_static')
                ->onDelete('set null');

            $table->foreign('consul_id')
                ->references('id')->on('consul')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['apostil_group1_id']);
            $table->dropForeign(['apostil_group2_id']);
            $table->dropForeign(['consul_id']);
            $table->dropIndex(['process_mode']);
            $table->dropColumn([
                'process_mode',
                'apostil_group1_id',
                'apostil_group2_id',
                'consul_id',
            ]);
        });
    }
};
