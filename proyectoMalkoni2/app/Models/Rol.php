<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación con empleados
     */
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_rol', 'id_rol');
    }
}