<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_tipo';
    protected $table = 'tipos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con Subtipos (Un Tipo tiene muchos Subtipos)
    public function subtipos()
    {
        return $this->hasMany(Subtipo::class, 'id_tipo', 'id_tipo');
    }
}