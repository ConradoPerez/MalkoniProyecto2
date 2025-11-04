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
        // Migración para la tabla items
Schema::create('items', function (Blueprint $table) {
    $table->id('id_item'); // id_item integer [primary key]
    $table->integer('cantidad');

    // Claves Foráneas
    $table->foreignId('id_cotizaciones')->constrained('cotizaciones', 'id');
    // id_Producto e id_servicio son opcionales según el DBML (un item es Producto O Servicio)
    $table->foreignId('id_producto')->nullable()->constrained('productos', 'id_producto');
    $table->foreignId('id_servicio')->nullable()->constrained('servicios', 'id_servicio');
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
