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
        // Migración para la tabla Subrubro
Schema::create('subrubros', function (Blueprint $table) {
    $table->id('id_subrubro'); // id_subrubro integer [primary key]
    $table->string('nombre', 255);
    $table->text('descripcion')->nullable();
    
    // Clave Foránea a Rubro (id_rubro)
    $table->foreignId('id_rubro')->constrained('rubros', 'id_rubro');
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subrubros');
    }
};
