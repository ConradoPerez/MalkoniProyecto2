<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtipo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_subtipo';
    protected $table = 'subtipos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_tipo',
    ];

    // Relación con Tipo (Un Subtipo pertenece a un Tipo)
    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'id_tipo', 'id_tipo');
    }

    // Relación con Productos (Un Subtipo tiene muchos Productos)
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_subtipo', 'id_subtipo');
    }
}