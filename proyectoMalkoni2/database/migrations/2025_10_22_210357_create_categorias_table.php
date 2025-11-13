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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id('id_categoria'); // Clave primaria
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            
            // Clave foránea a Subdivisiones (asumiendo que las categorías se relacionan a algo)
            // Aunque en el DBML es 'subdivision_categoria', lo mantengo simple.
            // $table->unsignedBigInteger('id_subdivision')->nullable(); 
            // $table->foreign('id_subdivision')->references('id_subdivision')->on('subdivisions');

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