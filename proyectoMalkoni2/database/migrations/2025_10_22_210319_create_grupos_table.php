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
        // Migración para la tabla Grupos
Schema::create('grupos', function (Blueprint $table) {
    $table->id('id_grupo'); // id_grupo integer [primary key]
    $table->string('nombre_grupo', 255);
    $table->text('descripcion')->nullable();
    
    // Clave Foránea a Persona (id_personas)
    $table->foreignId('id_personas')->constrained('personas', 'id_persona');
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
