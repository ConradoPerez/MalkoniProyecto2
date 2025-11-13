<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación con subcategorías
     */
    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria', 'id_categoria');
    }
    
    /**
     * Relación indirecta con productos a través de subcategorías
     */
    public function productos()
    {
        return $this->hasManyThrough(
            Producto::class,
            Subcategoria::class,
            'id_categoria',      // Foreign key on subcategorias table
            'id_subcategoria',   // Foreign key on productos table
            'id_categoria',      // Local key on categorias table
            'id_subcategoria'    // Local key on subcategorias table
        );
    }
}