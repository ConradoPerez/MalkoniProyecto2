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
        Schema::create('items', function (Blueprint $table) {
            $table->id('id_item'); // Clave primaria
            $table->integer('cantidad');
            
            // Claves foráneas
            $table->unsignedBigInteger('id_cotizaciones');
            $table->foreign('id_cotizaciones')->references('id')->on('cotizaciones')->onDelete('cascade');
            
            // Producto O Servicio (uno de los dos debe ser null)
            $table->unsignedBigInteger('id_Producto')->nullable();
            $table->foreign('id_Producto')->references('id_producto')->on('productos');
            
            $table->unsignedBigInteger('id_servicio')->nullable();
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios');

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