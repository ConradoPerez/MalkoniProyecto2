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
        // Migración para la tabla Productos
Schema::create('productos', function (Blueprint $table) {
    $table->id('id_producto'); // id_producto integer [primary key]
    $table->string('nombre', 255);
    $table->text('descripcion')->nullable();
    $table->integer('precio_base');
    $table->string('foto')->nullable();
    $table->integer('promocion')->default(0); // binario 0-1
    $table->integer('descuento')->default(0); // 0-100
    $table->integer('precio_final');

    // Clave Foránea a Categoria (id_categoria)
    // El DBML tiene una referencia: subdivision_servicio: Productos.id_categoria > Categoria.id_categoria
    $table->foreignId('id_categoria')->constrained('categorias', 'id_categoria');
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
