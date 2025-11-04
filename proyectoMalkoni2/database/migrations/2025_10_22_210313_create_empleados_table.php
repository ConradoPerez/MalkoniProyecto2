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
    // Migración para la tabla Empleados
    Schema::create('empleados', function (Blueprint $table) {
        $table->id('id_empleado');
        $table->string('nombre', 255);
        $table->string('foto')->nullable();
        $table->string('email', 255)->unique();
        
        // --- AÑADIR ESTAS DOS LÍNEAS ---
        $table->string('password');
        $table->rememberToken(); // Para la función "Recordarme"
        // ---------------------------------

        $table->integer('telefono')->nullable();
        $table->integer('dni')->unique();

        // Clave Foránea a Roles (id_rol)
        $table->foreignId('id_rol')->constrained('roles', 'id_rol'); 
        
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
