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
        // Migración para la tabla Empresa
Schema::create('empresas', function (Blueprint $table) {
    $table->id('id_empresa'); // id_empresa integer [primary key]
    $table->string('nombre', 255);
    $table->bigInteger('cuit')->unique(); // bigInteger para CUIT
    $table->string('foto')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
