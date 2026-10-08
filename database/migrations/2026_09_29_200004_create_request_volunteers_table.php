<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Participação de voluntários em solicitações (N:N requests ↔ volunteers),
 * com o status do fluxo de interesse (App\Enums\RequestVolunteerStatus).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('volunteer_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('interested');
            $table->text('message')->nullable();
            $table->timestamps();

            // Um voluntário possui no máximo um vínculo por solicitação.
            $table->unique(['request_id', 'volunteer_id']);
            $table->index(['volunteer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_volunteers');
    }
};
