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
        // Migración para la tabla Estados
        Schema::create('estados', function (Blueprint $table) {
            $table->id('id_estado'); // id_estado integer [primary key]
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_hora');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
