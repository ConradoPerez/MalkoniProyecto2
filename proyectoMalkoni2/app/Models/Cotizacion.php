<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'titulo',
        'numero',
        'fyh',
        'fecha_cotizado',
        'precio_total',
        'id_empleados',
        'id_empresas',
        'id_personas',
        'id_APIempl',
    ];

    protected $casts = [
        'fyh' => 'datetime',
        'fecha_cotizado' => 'datetime',
        'precio_total' => 'integer',
    ];

    /**
     * Boot del modelo - Event Listeners
     */
    protected static function boot()
    {
        parent::boot();
        
        // Crear cambio inicial automáticamente al crear una cotización
        static::created(function ($cotizacion) {
            // Verificar si ya existe un cambio para esta cotización (prevenir duplicados)
            $existeCambio = Cambio::where('id_cotizaciones', $cotizacion->id)->exists();
            
            if (!$existeCambio) {
                // Buscar el estado "Nuevo" (estado 1)
                $estadoNuevo = Estado::where('nombre', 'Nuevo')->first();
                $idEstadoNuevo = $estadoNuevo ? $estadoNuevo->id_estado : 1;
                
                Cambio::create([
                    'fyH' => now(),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $idEstadoNuevo, // Estado inicial: Nuevo
                ]);
            }
        });
    }

    /**
     * Relación con empleado (vendedor)
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleados', 'id_empleado');
    }

    /**
     * Relación con empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresas', 'id_empresa');
    }

    /**
     * Relación con persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_personas', 'id_persona');
    }

    /**
     * Relación con items
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'id_cotizaciones', 'id');
    }

    /**
     * Accessor para formatear el precio
     */
    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_total, 0, ',', '.');
    }

    /**
     * Accessor para el número formateado
     */
    public function getNumeroFormateadoAttribute()
    {
        return '#' . str_pad($this->numero, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Scope para cotizaciones del mes actual
     */
    public function scopeEsteMes($query)
    {
        return $query->whereMonth('fyh', Carbon::now()->month)
                    ->whereYear('fyh', Carbon::now()->year);
    }

    /**
     * Relación con cambios de estado
     */
    public function cambios()
    {
        return $this->hasMany(Cambio::class, 'id_cotizaciones', 'id');
    }

    /**
     * Relación para obtener el estado actual (último cambio)
     */
    public function estadoActual()
    {
        return $this->hasOneThrough(
            Estado::class,
            Cambio::class,
            'id_cotizaciones', // Foreign key on cambios table
            'id_estado',       // Foreign key on estados table
            'id',              // Local key on cotizaciones table
            'id_estado'        // Local key on cambios table
        )->latest('cambios.fyH');
    }

    /**
     * Scope para cotizaciones por estado
     */
    public function scopePorEstado($query, $nombreEstado)
    {
        return $query->whereHas('estadoActual', function($q) use ($nombreEstado) {
            $q->where('nombre', $nombreEstado);
        });
    }

    /**
     * Scope para cotizaciones en proceso
     */
    public function scopeEnProceso($query)
    {
        return $query->porEstado('En Proceso');
    }

    /**
     * Accessor para obtener el estado actual
     */
    public function getEstadoAttribute()
    {
        try {
            $estadoActual = $this->getEstadoActualDirecto();
            return $estadoActual ? $estadoActual->nombre : 'Pendiente';
        } catch (\Exception $e) {
            return 'Pendiente';
        }
    }

    /**
     * Accessor para obtener clase CSS del estado
     */
    public function getEstadoClaseAttribute()
    {
        try {
            $estadoActual = $this->getEstadoActualDirecto();
            return $estadoActual ? $estadoActual->estado_clase : 'bg-gray-400';
        } catch (\Exception $e) {
            return 'bg-gray-400';
        }
    }

    /**
     * Accessor para obtener estilo CSS del estado
     */
    public function getEstadoEstiloAttribute()
    {
        try {
            $estadoActual = $this->getEstadoActualDirecto();
            return $estadoActual ? $estadoActual->estado_estilo : 'background-color: #B1B7BB;';
        } catch (\Exception $e) {
            return 'background-color: #B1B7BB;';
        }
    }

    /**
     * Método para obtener el estado actual mediante consulta directa
     */
    public function getEstadoActualDirecto()
    {
        return \DB::table('cambios')
            ->join('estados', 'cambios.id_estado', '=', 'estados.id_estado')
            ->where('cambios.id_cotizaciones', $this->id)
            ->orderByDesc('cambios.fyH')
            ->select('estados.*')
            ->first();
    }

    /**
     * Accessor para obtener el nombre del cliente (empresa o persona)
     */
    public function getClienteNombreAttribute()
    {
        // Cargar relaciones si no están cargadas
        if (!$this->relationLoaded('empresa')) {
            $this->load('empresa');
        }
        if (!$this->relationLoaded('persona')) {
            $this->load('persona.empresa');
        }

        // Prioridad: si hay id_empresas, usar esa empresa directamente
        if ($this->id_empresas && $this->empresa) {
            return $this->empresa->nombre ?? 'Empresa sin nombre';
        } 
        // Si no hay empresa directa, buscar a través de la persona
        elseif ($this->id_personas && $this->persona && $this->persona->empresa) {
            return $this->persona->empresa->nombre ?? 'Cliente sin nombre';
        }
        
        return 'Sin cliente asignado';
    }
}