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
        Schema::table('grupo_empresa', function (Blueprint $table) {
            // Agregar las columnas de clave foránea que faltaban
            $table->unsignedBigInteger('id_grupo')->after('id');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos')->onDelete('cascade');
            
            $table->unsignedBigInteger('id_empresa')->after('id_grupo');
            $table->foreign('id_empresa')->references('id_empresa')->on('empresas')->onDelete('cascade');
            
            // Evitar duplicados
            $table->unique(['id_grupo', 'id_empresa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupo_empresa', function (Blueprint $table) {
            // Solo eliminar columnas si existen
            if (Schema::hasColumn('grupo_empresa', 'id_grupo')) {
                $table->dropColumn('id_grupo');
            }
            if (Schema::hasColumn('grupo_empresa', 'id_empresa')) {
                $table->dropColumn('id_empresa');
            }
        });
    }
};
