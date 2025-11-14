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
        Schema::table('empleados', function (Blueprint $table) {
            // Agregar columna id_personas
            $table->unsignedBigInteger('id_personas')->nullable()->after('id_empleado');
            $table->foreign('id_personas')->references('id_persona')->on('personas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['id_personas']);
            $table->dropColumn('id_personas');
        });
    }
};
