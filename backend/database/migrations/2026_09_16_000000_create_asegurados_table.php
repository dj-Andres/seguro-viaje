<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asegurados', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('numero_identificacion')->unique();
            $table->string('correo_electronico');
            $table->date('fecha_nacimiento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asegurados');
    }
};
