<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perfil pessoal do voluntário (relação 1:1 com users).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('bio')->nullable();
            $table->string('address')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();
            $table->string('zip_code', 8)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // LGPD: momento em que o voluntário consentiu com o uso da sua localização.
            // Sem consentimento a localização não é armazenada.
            $table->timestamp('location_consent_at')->nullable();
            $table->boolean('available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};
