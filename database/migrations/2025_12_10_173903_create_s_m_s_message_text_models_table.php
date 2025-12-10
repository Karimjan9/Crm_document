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
        Schema::create('s_m_s_message_text', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->unique();
            $table->enum('type', ['xabarnoma', 'ogohlantirish', 'boshqa'])->default('boshqa');
            $table->text('message_text1')->nullable();
            $table->text('message_text2')->nullable();
            $table->text('message_text3')->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('s_m_s_message_text');
    }
};
