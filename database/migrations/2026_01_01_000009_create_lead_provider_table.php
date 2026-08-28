<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 20)->default('sent');
            $table->decimal('price', 8, 2)->nullable();
            $table->unsignedSmallInteger('match_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['lead_id', 'provider_profile_id']);
            $table->index(['provider_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_provider');
    }
};
