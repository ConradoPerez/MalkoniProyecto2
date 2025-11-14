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
        // Verificar y agregar columnas
        Schema::table('grupo_empresa', function (Blueprint $table) {
            if (!Schema::hasColumn('grupo_empresa', 'id_grupo')) {
                $table->unsignedBigInteger('id_grupo')->after('id');
            }
        });
        
        Schema::table('grupo_empresa', function (Blueprint $table) {
            if (!Schema::hasColumn('grupo_empresa', 'id_empresas')) {
                $table->unsignedBigInteger('id_empresas')->after('id_grupo');
            }
        });
        
        // Agregar foreign keys
        Schema::table('grupo_empresa', function (Blueprint $table) {
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos')->onDelete('cascade');
            $table->foreign('id_empresas')->references('id_empresa')->on('empresas')->onDelete('cascade');
            $table->unique(['id_grupo', 'id_empresas']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupo_empresa', function (Blueprint $table) {
            $table->dropForeign(['id_grupo']);
            $table->dropForeign(['id_empresas']);
            $table->dropUnique(['id_grupo', 'id_empresas']);
            $table->dropColumn(['id_grupo', 'id_empresas']);
        });
    }
};
