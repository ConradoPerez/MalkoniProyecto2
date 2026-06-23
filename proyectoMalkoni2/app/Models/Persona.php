<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'id_empresa',
        'foto',
        'token_opt',
        'id_persona_externo',
        'nombre',
        'apellido',
        'email',
        'num_tel',
        'dni',
        'genero',
        'rol_origen',
        'estado_persona_origen',
        'empresa_activa_externa_id',
        'last_synced_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'id_persona_externo' => 'integer',
        'empresa_activa_externa_id' => 'integer',
        'rol_origen' => 'integer',
        'estado_persona_origen' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relación con empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Relacion N:N para compatibilidad con multiempresa de malkoni-online.
     */
    public function empresas()
    {
        return $this->belongsToMany(
            Empresa::class,
            'persona_empresa',
            'id_persona',
            'id_empresa',
            'id_persona',
            'id_empresa'
        )
        ->withPivot(['persona_external_id', 'empresa_external_id', 'estado', 'last_synced_at'])
        ->withTimestamps();
    }

    /**
     * Relación con cotizaciones
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'id_personas', 'id_persona');
    }
}