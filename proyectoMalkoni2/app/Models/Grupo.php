<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';
    
    protected $fillable = [
        'nombre_grupo',
        'descripcion',
        'id_empleado'
    ];

    /**
     * Relación con el empleado/vendedor que creó el grupo
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

    /**
     * Relación muchos a muchos con empresas
     */
    public function empresas()
    {
        return $this->belongsToMany(
            Empresa::class,
            'grupo_empresa',
            'id_grupo',
            'id_empresa'
        );
    }

    /**
     * Obtener empresas del grupo que tienen cotizaciones del vendedor
     */
    public function empresasConCotizaciones()
    {
        if (!$this->empleado) {
            return collect();
        }

        return $this->empresas()
            ->whereHas('cotizaciones', function ($query) {
                $query->where('id_personas', $this->empleado->id_personas);
            })
            ->withCount(['cotizaciones' => function ($query) {
                $query->where('id_personas', $this->empleado->id_personas);
            }]);
    }
}