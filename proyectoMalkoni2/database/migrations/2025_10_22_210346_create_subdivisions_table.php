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
        // Migración para la tabla Subdivision
Schema::create('subdivisions', function (Blueprint $table) {
    $table->id('id_subdivision'); // id_subdivision integer [primary key]
    $table->string('nombre', 255);
    $table->text('descripcion')->nullable();
    
    // Clave Foránea a Subrubro (id_subrubro)
    $table->foreignId('id_subrubro')->constrained('subrubros', 'id_subrubro');
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdivisions');
    }
};
