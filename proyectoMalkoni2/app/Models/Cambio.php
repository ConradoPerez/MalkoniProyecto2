<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cambio extends Model
{
    use HasFactory;

    protected $table = 'cambios';
    protected $primaryKey = 'id_cambio';

    protected $fillable = [
        'fyH',
        'id_cotizaciones',
        'id_estado',
    ];

    protected $casts = [
        'fyH' => 'datetime',
    ];

    /**
     * Relación con cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizaciones', 'id');
    }

    /**
     * Relación con estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }
}