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
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_persona_externo')->nullable()->after('id_persona');
            $table->string('nombre', 100)->nullable()->after('token_opt');
            $table->string('apellido', 100)->nullable()->after('nombre');
            $table->string('email', 150)->nullable()->after('apellido');
            $table->string('num_tel', 50)->nullable()->after('email');
            $table->string('dni', 20)->nullable()->after('num_tel');
            $table->string('genero', 15)->nullable()->after('dni');
            $table->unsignedTinyInteger('rol_origen')->nullable()->after('genero');
            $table->unsignedTinyInteger('estado_persona_origen')->nullable()->after('rol_origen');
            $table->unsignedBigInteger('empresa_activa_externa_id')->nullable()->after('estado_persona_origen');
            $table->timestamp('last_synced_at')->nullable()->after('empresa_activa_externa_id');
            $table->string('sync_status', 20)->default('pending')->after('last_synced_at');
            $table->text('sync_error')->nullable()->after('sync_status');

            $table->unique('id_persona_externo', 'personas_id_persona_externo_unique');
            $table->index('email', 'personas_email_idx');
            $table->index('last_synced_at', 'personas_last_synced_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('personas_last_synced_at_idx');
            $table->dropIndex('personas_email_idx');
            $table->dropUnique('personas_id_persona_externo_unique');

            $table->dropColumn([
                'id_persona_externo',
                'nombre',
                'apellido',
                'email',
                'num_tel',
                'dni',
                'genero',
                'rol_origen',
                'estado_persona_origen',
                'empresa_activa_externa_id',
                'last_synced_at',
                'sync_status',
                'sync_error',
            ]);
        });
    }
};
