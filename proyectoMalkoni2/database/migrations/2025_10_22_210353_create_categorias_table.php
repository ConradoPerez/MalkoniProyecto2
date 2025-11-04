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
        // Migración para la tabla Categoria
Schema::create('categorias', function (Blueprint $table) {
    $table->id('id_categoria'); // id_categoria integer [primary key]
    $table->string('nombre', 255);
    $table->text('descripcion')->nullable();
    
    // Clave Foránea a Subdivision (id_subdivision)
    $table->foreignId('id_subdivision')->constrained('subdivisions', 'id_subdivision');
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
