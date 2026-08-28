<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_category_id')->constrained()->cascadeOnDelete();
            // Тип в рамките на категорията, напр. „2–5 тона“.
            $table->string('type')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->boolean('operator_available')->default(true);
            $table->boolean('operator_only')->default(false);
            $table->decimal('price_from', 10, 2)->nullable();
            $table->string('price_unit', 20)->default('hour');
            $table->string('min_duration')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['equipment_category_id', 'active']);
            $table->index('provider_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
