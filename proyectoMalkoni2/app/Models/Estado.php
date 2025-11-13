<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estados';
    protected $primaryKey = 'id_estado';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    /**
     * Relación con cambios
     */
    public function cambios()
    {
        return $this->hasMany(Cambio::class, 'id_estado', 'id_estado');
    }

    /**
     * Método para obtener clase CSS del estado
     */
    public function getEstadoClaseAttribute()
    {
        return match(strtolower($this->nombre)) {
            'en proceso' => 'bg-blue-600',
            'aprobado' => 'bg-green-100 text-green-800',
            'rechazado' => 'bg-gray-400',
            'pendiente' => 'bg-orange-500',
            default => 'bg-gray-400'
        };
    }

    /**
     * Método para obtener estilo CSS del estado
     */
    public function getEstadoEstiloAttribute()
    {
        return match(strtolower($this->nombre)) {
            'en proceso' => 'background-color: #166379;',
            'aprobado' => '',
            'rechazado' => 'background-color: #B1B7BB;',
            'pendiente' => 'background-color: #D88429;',
            default => 'background-color: #B1B7BB;'
        };
    }
}