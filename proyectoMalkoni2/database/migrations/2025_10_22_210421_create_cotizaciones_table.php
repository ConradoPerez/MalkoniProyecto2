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
        // Migración para la tabla Cotizaciones
Schema::create('cotizaciones', function (Blueprint $table) {
    $table->id(); // id integer [primary key]
    $table->string('titulo', 255);
    $table->integer('numero')->unique();
    $table->dateTime('fyh');
    $table->integer('precio_total');

    // Claves Foráneas
    $table->foreignId('id_empleados')->constrained('empleados', 'id_empleado');
    $table->foreignId('id_empresas')->constrained('empresas', 'id_empresa');
    $table->foreignId('id_personas')->constrained('personas', 'id_persona');
    // id_APIempl no tiene tabla de origen definida, se deja como columna simple
    $table->integer('id_APIempl')->nullable(); 
    
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
