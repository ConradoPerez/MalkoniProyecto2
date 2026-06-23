<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('personas', 'password')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }

        if (Schema::hasColumn('personas', 'contrasena')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('contrasena');
            });
        }

        if (Schema::hasColumn('personas', 'contraseña')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('contraseña');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('personas', 'password')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
            });
        }
    }
};