<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion');
            $table->enum('sender_type', ['cliente', 'vendedor']);
            $table->unsignedBigInteger('sender_id');
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->timestamps();

            $table->foreign('id_cotizacion')->references('id')->on('cotizaciones')->onDelete('cascade');
            $table->index(['id_cotizacion', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_cotizacion');
    }
};
