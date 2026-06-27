<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todas las cotizaciones con pedido_opt_id
        $cotizaciones = DB::table('cotizaciones')->whereNotNull('pedido_opt_id')->get();

        foreach ($cotizaciones as $cotizacion) {
            // Verificar si ya tiene el item del OPT (id_Producto is null and id_servicio is null)
            $existeItem = DB::table('items')
                ->where('id_cotizaciones', $cotizacion->id)
                ->whereNull('id_Producto')
                ->whereNull('id_servicio')
                ->exists();

            if (!$existeItem) {
                DB::table('items')->insert([
                    'id_cotizaciones' => $cotizacion->id,
                    'cantidad' => 1,
                    'precio_unitario' => 0,
                    'descripcion' => 'Plano de Optimización de Cortes (OPT #' . $cotizacion->pedido_opt_id . ')',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar items creados por esta migración
        DB::table('items')
            ->whereNull('id_Producto')
            ->whereNull('id_servicio')
            ->where('descripcion', 'like', 'Plano de Optimización de Cortes (OPT #%')
            ->delete();
    }
};
