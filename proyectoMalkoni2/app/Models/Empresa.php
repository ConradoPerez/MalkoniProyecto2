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
        'id_empresa_externo',
        'razon_social',
        'cod_cond_iva',
        'email',
        'num_tel',
        'estado_origen',
        'validado_origen',
        'baja_origen',
        'last_synced_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'id_empresa_externo' => 'integer',
        'estado_origen' => 'integer',
        'validado_origen' => 'boolean',
        'baja_origen' => 'boolean',
        'last_synced_at' => 'datetime',
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
     * Relacion N:N para compatibilidad con multiempresa de malkoni-online.
     */
    public function personasVinculadas()
    {
        return $this->belongsToMany(
            Persona::class,
            'persona_empresa',
            'id_empresa',
            'id_persona',
            'id_empresa',
            'id_persona'
        )
        ->withPivot(['persona_external_id', 'empresa_external_id', 'estado', 'last_synced_at'])
        ->withTimestamps();
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