<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'nombre',
        'foto',
        'email',
        'password',
        'telefono',
        'dni',
        'id_rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relación con rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * Scope para filtrar solo vendedores
     */
    public function scopeVendedores($query)
    {
        return $query->whereHas('rol', function($q) {
            $q->where('nombre', 'vendedor');
        });
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('nombre', 'like', '%' . $nombre . '%');
    }

    /**
     * Scope para buscar por DNI
     */
    public function scopeBuscarPorDni($query, $dni)
    {
        return $query->where('dni', 'like', '%' . $dni . '%');
    }

    /**
     * Relación con cotizaciones
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'id_empleados', 'id_empleado');
    }
}