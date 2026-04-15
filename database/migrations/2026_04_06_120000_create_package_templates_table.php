<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('highlight')->nullable();
            $table->text('description')->nullable();
            $table->enum('process_mode', ['service', 'apostil', 'consul'])->default('service');
            $table->enum('selection_mode', ['consul', 'legalization', 'mixed'])->nullable();
            $table->foreignId('document_type_id')->constrained('document_type');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('direction_type_id')->nullable()->constrained('direction_type');
            $table->foreignId('apostil_group1_id')->nullable()->constrained('apostil_static');
            $table->foreignId('apostil_group2_id')->nullable()->constrained('apostil_static');
            $table->foreignId('consul_id')->nullable()->constrained('consul');
            $table->foreignId('consulate_type_id')->nullable()->constrained('consulates_type');
            $table->json('selected_addons')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('promo_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_templates');
    }
};
