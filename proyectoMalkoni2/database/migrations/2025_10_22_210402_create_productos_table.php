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
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto'); // Clave primaria
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('precio_base');
            $table->string('foto')->nullable();
            $table->boolean('promocion')->default(false); // binario 0-1
            $table->unsignedSmallInteger('descuento')->default(0); // 0-100
            $table->integer('precio_final');

            // Columna consolidada para estadísticas
            $table->unsignedBigInteger('cant_cotizaciones')->default(0);

            // Claves foráneas para la nueva clasificación
            
            // Relación con Subtipos
            $table->unsignedBigInteger('id_subtipo')->nullable();
            $table->foreign('id_subtipo')->references('id_subtipo')->on('subtipos');
            
            // Relación con Subcategorías
            $table->unsignedBigInteger('id_subcategoria')->nullable();
            $table->foreign('id_subcategoria')->references('id_subcategoria')->on('subcategorias');

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