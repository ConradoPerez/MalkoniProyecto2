<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'foto',
        'promocion',
        'descuento',
        'precio_final',
        'cant_cotizaciones',
        'id_categoria',
    ];

    protected $casts = [
        'promocion' => 'boolean',
        'precio_base' => 'integer',
        'precio_final' => 'integer',
        'descuento' => 'integer',
    ];

    /**
     * Relación con categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Scope para buscar por código/nombre
     */
    public function scopeBuscarPorCodigo($query, $codigo)
    {
        return $query->where('id_producto', 'like', '%' . $codigo . '%');
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('nombre', 'like', '%' . $nombre . '%');
    }

    /**
     * Accessor para formatear el precio
     */
    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_final, 0, ',', '.');
    }

    /**
     * Accessor para el código del producto (usando el ID directamente)
     */
    public function getCodigoAttribute()
    {
        return $this->id_producto;
    }
}