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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_opt_id')->nullable()->after('numero');
            $table->string('referencia_externa', 100)->nullable()->after('pedido_opt_id');
            $table->text('pdf_url')->nullable()->after('referencia_externa');
            $table->unsignedBigInteger('persona_external_id_snapshot')->nullable()->after('pdf_url');
            $table->unsignedBigInteger('empresa_external_id_snapshot')->nullable()->after('persona_external_id_snapshot');
            $table->string('origen_sistema', 40)->nullable()->after('empresa_external_id_snapshot');
            $table->string('integration_status', 20)->default('pendiente')->after('origen_sistema');
            $table->text('integration_error')->nullable()->after('integration_status');
            $table->json('payload_origen')->nullable()->after('integration_error');
            $table->timestamp('imported_at')->nullable()->after('payload_origen');

            $table->index('pedido_opt_id', 'cotizaciones_pedido_opt_id_idx');
            $table->index('referencia_externa', 'cotizaciones_referencia_externa_idx');
            $table->unique(
                ['pedido_opt_id', 'persona_external_id_snapshot', 'empresa_external_id_snapshot'],
                'cotizaciones_opt_persona_empresa_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropUnique('cotizaciones_opt_persona_empresa_unique');
            $table->dropIndex('cotizaciones_referencia_externa_idx');
            $table->dropIndex('cotizaciones_pedido_opt_id_idx');

            $table->dropColumn([
                'pedido_opt_id',
                'referencia_externa',
                'pdf_url',
                'persona_external_id_snapshot',
                'empresa_external_id_snapshot',
                'origen_sistema',
                'integration_status',
                'integration_error',
                'payload_origen',
                'imported_at',
            ]);
        });
    }
};
