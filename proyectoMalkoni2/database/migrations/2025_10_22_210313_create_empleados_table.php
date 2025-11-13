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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('id_empleado'); // Clave primaria
            $table->string('nombre', 100);
            $table->string('foto')->nullable();
            $table->string('email', 100)->unique();
            $table->unsignedBigInteger('telefono')->nullable();
            $table->unsignedBigInteger('dni')->unique();
            
            // Clave foránea a Roles
            $table->unsignedBigInteger('id_rol');
            $table->foreign('id_rol')->references('id_rol')->on('roles');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};