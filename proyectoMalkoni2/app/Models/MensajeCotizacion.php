<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeCotizacion extends Model
{
    protected $table = 'mensajes_cotizacion';

    protected $fillable = [
        'id_cotizacion',
        'sender_type',
        'sender_id',
        'mensaje',
        'leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }

    public function getSenderNombreAttribute(): string
    {
        if ($this->sender_type === 'vendedor') {
            $empleado = Empleado::find($this->sender_id);
            return $empleado ? $empleado->nombre : 'Vendedor';
        }

        $persona = Persona::find($this->sender_id);
        return $persona ? trim($persona->nombre . ' ' . $persona->apellido) : 'Cliente';
    }
}
