<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';
    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'nombre',
        'cuit',
        'foto',
    ];

    /**
     * Relación con cotizaciones
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'id_empresas', 'id_empresa');
    }

    /**
     * Relación con personas
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Relación muchos a muchos con grupos
     */
    public function grupos()
    {
        return $this->belongsToMany(
            Grupo::class,
            'grupo_empresa',
            'id_empresas',
            'id_grupo',
            'id_empresa',
            'id_grupo'
        );
    }

    /**
     * Accessor para formatear CUIT
     */
    public function getCuitFormateadoAttribute()
    {
        $cuit = (string) $this->cuit;
        if (strlen($cuit) === 11) {
            return substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, -1);
        }
        return $cuit;
    }
}