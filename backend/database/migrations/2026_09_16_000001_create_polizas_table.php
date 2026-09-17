<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polizas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asegurado_id')->constrained('asegurados')->cascadeOnDelete();
            $table->string('pais_destino');
            $table->string('codigo_pais', 2);
            $table->string('region');
            $table->date('fecha_salida');
            $table->date('fecha_regreso');
            $table->unsignedInteger('dias_viaje');
            $table->decimal('tarifa_base', 10, 2);
            $table->decimal('porcentaje_recargo', 5, 2);
            $table->decimal('valor_total', 10, 2);
            $table->enum('estado', ['cotizado', 'contratado'])->default('cotizado');
            $table->timestamp('fecha_contratacion')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_salida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polizas');
    }
};
