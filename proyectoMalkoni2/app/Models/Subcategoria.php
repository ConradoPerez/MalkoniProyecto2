<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_subcategoria';
    protected $table = 'subcategorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_categoria',
    ];

    // Relación con Categoria (Una Subcategoria pertenece a una Categoria)
    public function categoria()
    {
        // Asumiendo que ya tienes un modelo Categoria.php
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    // Relación con Productos (Una Subcategoria tiene muchos Productos)
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_subcategoria', 'id_subcategoria');
    }
}