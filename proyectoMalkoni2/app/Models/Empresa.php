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