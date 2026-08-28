<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('population')->nullable();
            $table->boolean('active')->default(true);
            // Дали за града се генерират локални SEO страници.
            $table->boolean('landing_enabled')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->text('seo_content')->nullable();
            $table->timestamps();

            $table->index(['active', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
