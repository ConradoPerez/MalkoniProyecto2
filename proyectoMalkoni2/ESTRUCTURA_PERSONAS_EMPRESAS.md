# Estructura Personas-Empresas-Cotizaciones

## Resumen de la Arquitectura

### 1. Modelo de Datos

#### Personas (Usuarios del Sistema)
- **Tabla**: `personas`
- **Campos clave**: `id_persona`, `id_empresa`, `foto`, `token_opt`
- **Relación**: Cada persona pertenece a una empresa (`belongsTo`)

#### Empresas (Clientes Corporativos)
- **Tabla**: `empresas`
- **Campos clave**: `id_empresa`, `nombre`, `cuit`, `foto`
- **Relación**: Una empresa puede tener muchas personas (`hasMany`)

#### Cotizaciones
- **Tabla**: `cotizaciones`
- **Campos clave**: 
  - `id_personas`: Persona que creó la cotización
  - `id_empresas`: Empresa a la que pertenece la cotización
  - `id_empleados`: Vendedor asignado
  - `precio_total`, `fecha_cotizado`, etc.

### 2. Flujo de Autenticación y Acceso

#### Cliente (Persona)
- **Acceso**: `?cliente_id={id_persona}`
- **Vista**: Solo ve las cotizaciones que **esa persona** creó
- **Filtro**: `where('id_personas', $cliente_id)`
- **Empresa mostrada**: La empresa asociada a la persona (`$persona->empresa->nombre`)

```php
// En ClienteDashboardController
$cotizaciones = Cotizacion::where('id_personas', $clienteId)->get();
$nombreEmpresa = $cliente->empresa->nombre;
```

#### Vendedor (Empleado)
- **Acceso**: `?empleado_id={id_empleado}`
- **Vista**: Ve todas las cotizaciones agrupadas por empresa
- **Filtro**: `where('id_empleados', $empleadoId)`
- **Agrupación**: Por `id_empresas` (muestra nombre de empresa, no de persona)

```php
// En VendedorCotizacionController
$cotizaciones = Cotizacion::where('id_empleados', $empleadoId)->get();
// Se muestra: $cotizacion->empresa->nombre
```

### 3. Ejemplo Práctico

#### Escenario:
- **Empresa**: EcoArq (id_empresa = 8)
- **Personas**:
  - Juan Pérez (id_persona = 10, id_empresa = 8)
  - María García (id_persona = 15, id_empresa = 8)
  - Roberto López (id_persona = 20, id_empresa = 8)

#### Cotizaciones creadas:
1. Cotización #001 → Creada por Juan Pérez (id_personas = 10, id_empresas = 8)
2. Cotización #002 → Creada por María García (id_personas = 15, id_empresas = 8)
3. Cotización #003 → Creada por Juan Pérez (id_personas = 10, id_empresas = 8)
4. Cotización #004 → Creada por Roberto López (id_personas = 20, id_empresas = 8)

#### Vistas:

**Juan Pérez accede con `?cliente_id=10`:**
- Ve: Cotización #001, #003
- Muestra: "EcoArq" en el banner
- Total: 2 cotizaciones

**María García accede con `?cliente_id=15`:**
- Ve: Cotización #002
- Muestra: "EcoArq" en el banner
- Total: 1 cotización

**Vendedor accede con `?empleado_id=3`:**
- Ve todas las cotizaciones agrupadas por empresa
- En la lista aparece: "EcoArq" con 4 cotizaciones (#001, #002, #003, #004)
- No distingue qué persona de EcoArq creó cada cotización

### 4. Estados y Precios

#### Regla de Negocio:
- **Estados "Nuevo" y "Abierto"**: NO tienen precio → Mostrar "Sin Cotizar"
- **Estados "Cotizado" y "En entrega"**: DEBEN tener precio → Mostrar monto

#### Implementación en Blade:
```php
@php
    $estadoActual = $cotizacion->estado_actual->nombre ?? 'Nuevo';
    $sinPrecio = in_array($estadoActual, ['Nuevo', 'Abierto']) 
                 || !$cotizacion->precio_total 
                 || $cotizacion->precio_total <= 0;
@endphp

@if($sinPrecio)
    <span class="text-gray-500">Sin Cotizar</span>
@else
    ${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}
@endif
```

### 5. Distribución de Estados (Seeder)

Por cada empresa se crean cotizaciones con la siguiente distribución:
- **1 cotización** en estado "Nuevo" (la más reciente)
- **2 cotizaciones** en estado "Abierto"
- **2 cotizaciones** en estado "Cotizado"
- **2 cotizaciones** en estado "En entrega" (las más antiguas)

### 6. Orden de Visualización

Las cotizaciones se ordenan por:
1. **Prioridad de estado** (Nuevo > Abierto > Cotizado > En entrega)
2. **Fecha descendente** dentro de cada estado

```php
$ordenEstados = ['Nuevo' => 1, 'Abierto' => 2, 'Cotizado' => 3, 'En entrega' => 4];
$cotizaciones = $cotizaciones->sortBy([
    fn($a, $b) => ($ordenEstados[$a->estado_actual->nombre ?? 'Nuevo'] ?? 5) <=> 
                  ($ordenEstados[$b->estado_actual->nombre ?? 'Nuevo'] ?? 5),
    fn($a, $b) => $b->fyh <=> $a->fyh
]);
```

### 7. Integración Futura con malkoni_online

Cuando se conecte con la base de datos de `online.malkoni.com.ar`:
- Las personas ya tendrán `id_empresa` asignado desde el registro
- El sistema de autenticación usará las tablas de `malkoni_online`
- Las cotizaciones seguirán usando `id_personas` e `id_empresas` de la misma forma
- No se requieren cambios en la lógica de negocio actual

---

**Fecha de documentación**: 18 de noviembre de 2025
