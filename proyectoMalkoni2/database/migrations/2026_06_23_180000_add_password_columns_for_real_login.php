<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            if (!Schema::hasColumn('personas', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
        });

        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            if (Schema::hasColumn('personas', 'password')) {
                $table->dropColumn('password');
            }
        });

        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
