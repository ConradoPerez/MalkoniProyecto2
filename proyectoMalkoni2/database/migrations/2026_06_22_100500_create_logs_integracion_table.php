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
        Schema::create('logs_integracion', function (Blueprint $table) {
            $table->id();
            $table->string('source_system', 50);
            $table->string('metodo', 10)->nullable();
            $table->string('endpoint', 150)->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('idempotency_key', 120)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('status', 20)->default('pendiente');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('source_system', 'logs_integracion_source_system_idx');
            $table->index('status', 'logs_integracion_status_idx');
            $table->index('request_id', 'logs_integracion_request_id_idx');
            $table->index('idempotency_key', 'logs_integracion_idempotency_key_idx');
            $table->index('created_at', 'logs_integracion_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_integracion');
    }
};
