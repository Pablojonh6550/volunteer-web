<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dados institucionais da ONG (relação 1:1 com users).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            // CNPJ somente com dígitos. Opcional no cadastro inicial.
            $table->string('document', 14)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();
            $table->string('zip_code', 8)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            // Suporte ao pré-filtro geográfico (bounding box) da busca por proximidade.
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
