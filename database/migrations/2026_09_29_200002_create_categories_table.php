<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categorias das solicitações (ex.: Alimentos, Aulas de reforço).
 * `type` segue App\Enums\RequestType.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->index();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'type']);
        });

        // Áreas de interesse do voluntário (N:N volunteers ↔ categories).
        Schema::create('category_volunteer', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('volunteer_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'volunteer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_volunteer');
        Schema::dropIfExists('categories');
    }
};
