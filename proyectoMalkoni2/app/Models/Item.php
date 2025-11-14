<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $primaryKey = 'id_item';

    protected $fillable = [
        'cantidad',
        'precio_unitario',
        'descripcion',
        'id_cotizaciones',
        'id_Producto',
        'id_servicio',
    ];

    /**
     * Relación con cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizaciones', 'id');
    }

    /**
     * Relación con producto (opcional)
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_Producto', 'id_producto');
    }

    /**
     * Relación con servicio (opcional) - Cuando se implemente
     */
    // public function servicio()
    // {
    //     return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    // }

    /**
     * Scope para items que son productos (no servicios)
     */
    public function scopeProductos($query)
    {
        return $query->whereNotNull('id_Producto');
    }

    /**
     * Scope para items que son servicios (no productos)
     */
    public function scopeServicios($query)
    {
        return $query->whereNotNull('id_servicio');
    }
}