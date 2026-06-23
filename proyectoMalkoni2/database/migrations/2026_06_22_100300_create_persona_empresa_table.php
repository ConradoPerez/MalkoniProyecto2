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
        Schema::create('persona_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('persona_external_id')->nullable();
            $table->unsignedBigInteger('empresa_external_id')->nullable();
            $table->string('estado', 20)->default('activa');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('personas')->onDelete('cascade');
            $table->foreign('id_empresa')->references('id_empresa')->on('empresas')->onDelete('cascade');

            $table->unique(['id_persona', 'id_empresa'], 'persona_empresa_unique');
            $table->index(['persona_external_id', 'empresa_external_id'], 'persona_empresa_external_ids_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_empresa');
    }
};
