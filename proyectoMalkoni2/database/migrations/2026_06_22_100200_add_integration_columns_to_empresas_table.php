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
        Schema::table('empresas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_empresa_externo')->nullable()->after('id_empresa');
            $table->string('razon_social', 150)->nullable()->after('nombre');
            $table->string('cod_cond_iva', 5)->nullable()->after('cuit');
            $table->string('email', 150)->nullable()->after('cod_cond_iva');
            $table->string('num_tel', 50)->nullable()->after('email');
            $table->unsignedTinyInteger('estado_origen')->nullable()->after('num_tel');
            $table->boolean('validado_origen')->nullable()->after('estado_origen');
            $table->boolean('baja_origen')->nullable()->after('validado_origen');
            $table->timestamp('last_synced_at')->nullable()->after('baja_origen');
            $table->string('sync_status', 20)->default('pending')->after('last_synced_at');
            $table->text('sync_error')->nullable()->after('sync_status');

            $table->unique('id_empresa_externo', 'empresas_id_empresa_externo_unique');
            $table->index('cuit', 'empresas_cuit_idx');
            $table->index('last_synced_at', 'empresas_last_synced_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropIndex('empresas_last_synced_at_idx');
            $table->dropIndex('empresas_cuit_idx');
            $table->dropUnique('empresas_id_empresa_externo_unique');

            $table->dropColumn([
                'id_empresa_externo',
                'razon_social',
                'cod_cond_iva',
                'email',
                'num_tel',
                'estado_origen',
                'validado_origen',
                'baja_origen',
                'last_synced_at',
                'sync_status',
                'sync_error',
            ]);
        });
    }
};
