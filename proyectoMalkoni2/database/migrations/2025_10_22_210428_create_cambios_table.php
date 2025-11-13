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
        Schema::create('cambios', function (Blueprint $table) {
            $table->id('id_cambio'); // Clave primaria
            $table->dateTime('fyH');
            
            // Claves foráneas
            $table->unsignedBigInteger('id_cotizaciones');
            $table->foreign('id_cotizaciones')->references('id')->on('cotizaciones')->onDelete('cascade');
            
            // **CORRECCIÓN DE NOMBRE:** Usamos id_estado (singular) en lugar de id_Esatdo (sic)
            $table->unsignedBigInteger('id_estado');
            $table->foreign('id_estado')->references('id_estado')->on('estados');

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