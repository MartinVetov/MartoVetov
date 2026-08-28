<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();

            // Категорията липсва, когато клиентът е избрал „Не знам каква техника ми трябва“.
            $table->foreignId('equipment_category_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('category_unknown')->default(false);
            $table->string('equipment_type')->nullable();

            $table->text('description');

            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('district')->nullable();
            $table->string('address')->nullable();

            $table->date('requested_date')->nullable();
            $table->string('requested_time', 32)->nullable();
            $table->boolean('date_flexible')->default(false);
            $table->string('duration', 32)->default('unknown');

            $table->string('operator_required', 32)->default('unknown');

            // Отговорите на динамичните полета според категорията.
            // Името е `details`, а не `attributes` — последното се засича с вътрешното
            // свойство $attributes на Eloquent модела.
            $table->json('details')->nullable();

            $table->string('contact_name');
            $table->string('contact_phone', 32);
            $table->string('contact_email');

            $table->string('status', 20)->default('new');
            $table->text('admin_notes')->nullable();
            $table->decimal('price', 8, 2)->nullable();

            $table->timestamp('consent_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('source', 50)->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->timestamp('anonymized_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['equipment_category_id', 'city_id']);
            $table->index('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
