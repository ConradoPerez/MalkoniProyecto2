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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('titulo', 150);
            $table->unsignedBigInteger('numero')->unique();
            $table->dateTime('fyh');
            $table->integer('precio_total');
            $table->unsignedBigInteger('id_APIempl')->nullable(); // ID de API

            // Claves foráneas (Clientes y Vendedor)
            $table->unsignedBigInteger('id_empleados');
            $table->foreign('id_empleados')->references('id_empleado')->on('empleados');
            
            // Un cotización es de una Empresa O una Persona (puedes necesitar hacer una de las dos nullable)
            $table->unsignedBigInteger('id_empresas')->nullable();
            $table->foreign('id_empresas')->references('id_empresa')->on('empresas');

            $table->unsignedBigInteger('id_personas')->nullable();
            $table->foreign('id_personas')->references('id_persona')->on('personas');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};