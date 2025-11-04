<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migración para la tabla cambios
Schema::create('cambios', function (Blueprint $table) {
    $table->id('id_cambio'); // id_cambio integer [primary key]
    $table->dateTime('fyH');

    // Claves Foráneas
    $table->foreignId('id_cotizaciones')->constrained('cotizaciones', 'id');
    $table->foreignId('id_estado')->constrained('estados', 'id_estado'); // Corregido 'id_Esatdo' a 'id_estado'
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cambios');
    }
};
