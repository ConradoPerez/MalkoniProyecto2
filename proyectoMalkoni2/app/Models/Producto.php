<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_producto'; // Según tu migración
    protected $table = 'productos';
    
    // Asegúrate de que los fillable incluyan los nuevos IDs
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'foto',
        'promocion',
        'descuento',
        'precio_final',
        'cant_cotizaciones', // La nueva columna consolidada
        'id_subtipo',        // Nuevo ID de clasificación
        'id_subcategoria',   // Nuevo ID de clasificación
    ];

    // ==========================================================
    // NUEVAS RELACIONES DE CLASIFICACIÓN
    // ==========================================================

    /**
     * Relación con Subtipo (Nuevo)
     */
    public function subtipo()
    {
        return $this->belongsTo(Subtipo::class, 'id_subtipo', 'id_subtipo');
    }

    /**
     * Relación con Subcategoria (Nuevo)
     */
    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }

    // ==========================================================
    // RELACIONES EXISTENTES
    // ==========================================================

    /**
     * Relación con items (asumida de tu DBML)
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'id_Producto', 'id_producto');
    }

    /**
     * Scope para buscar por código
     */
    public function scopeBuscarPorCodigo($query, $codigo)
    {
        return $query->where('id_producto', 'like', "%$codigo%");
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('nombre', 'like', "%$nombre%");
    }
}