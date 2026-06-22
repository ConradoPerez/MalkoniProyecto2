# Documentación - Sistema de Cotización del Vendedor

## Resumen de Cambios

Se ha implementado un flujo completo para que los vendedores puedan cotizar pedidos de clientes. El sistema ahora diferencia entre cotizaciones sin precio (estados Nuevo/Abierto) y cotizaciones ya cotizadas (estado Cotizado).

## Flujo de Estados

### Estados de Cotización

1. **Nuevo** → Cotización recién creada por el cliente, sin revisar
2. **Abierto** → Cotización revisada por el vendedor, en proceso de cotización
3. **Cotizado** → Cotización con precios asignados
4. **En entrega** → Pedido en proceso de entrega

### Transiciones Automáticas

- **Nuevo → Abierto**: Cuando el vendedor abre una cotización en estado "Nuevo" (con "Ver detalle" o "Cotizar")
- **Abierto → Cotizado**: Cuando el vendedor guarda los precios de una cotización

## Componentes Implementados

### 1. Vista de Listado (index.blade.php)

**Ubicación**: `resources/views/vendedor/cotizaciones/index.blade.php`

**Cambios principales**:
- Columna de "Monto" ahora muestra "Sin cotizar" si no tiene precio
- Nueva columna "Acciones" con botones dinámicos:
  - **Botón "Cotizar"**: Para cotizaciones sin precio (estados Nuevo/Abierto)
  - **Botón "Ver detalle"**: Para todas las cotizaciones
- Filtros abiertos por defecto

### 2. Vista de Detalle (detalle.blade.php)

**Ubicación**: `resources/views/vendedor/cotizaciones/detalle.blade.php`

**Características**:
- Muestra información completa de la cotización
- Tarjetas con métricas (fecha, items, monto)
- Tabla de items con campos editables para precios unitarios
- Cálculo automático de subtotales con JavaScript
- Botón "Guardar cotización" (solo visible si la cotización no está cotizada)
- Historial de cambios de estado
- Mensajes de éxito/error

### 3. Controlador (VendedorCotizacionController.php)

**Ubicación**: `app/Http/Controllers/VendedorCotizacionController.php`

**Nuevos métodos**:

#### `detalle($id)`
- Muestra el detalle de una cotización específica
- Cambia automáticamente estado Nuevo → Abierto al abrir
- Carga items, estados y relaciones necesarias

#### `guardar($id)`
- Guarda los precios unitarios de los items
- Calcula el precio total de la cotización
- Cambia el estado a "Cotizado"
- Manejo de transacciones para integridad de datos

### 4. Rutas

**Archivo**: `routes/web.php`

```php
Route::prefix('vendedor')->name('vendedor.app.')->group(function () {
    Route::get('/cotizaciones', [VendedorCotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/{id}', [VendedorCotizacionController::class, 'detalle'])->name('cotizaciones.detalle');
    Route::put('/cotizaciones/{id}', [VendedorCotizacionController::class, 'guardar'])->name('cotizaciones.guardar');
});
```

## Base de Datos

### Nueva Migración

**Archivo**: `database/migrations/2025_11_14_165401_add_precio_unitario_to_items_table.php`

**Cambios**:
- Agregada columna `precio_unitario` (decimal, nullable)
- Agregada columna `descripcion` (text, nullable)

### Modelo Item Actualizado

**Cambios**:
```php
protected $fillable = [
    'cantidad',
    'precio_unitario',
    'descripcion',
    'id_cotizaciones',
    'id_Producto',
    'id_servicio',
];
```

## Seeders Actualizados

### CotizacionSeeder
- 30% de cotizaciones sin precio (Nuevo/Abierto)
- 70% de cotizaciones con precio (Cotizado/En entrega)

### CambioSeeder
- Estados coherentes según si la cotización tiene precio
- Cotizaciones sin precio: solo estados Nuevo o Abierto
- Cotizaciones con precio: progresión completa (Nuevo → Abierto → Cotizado → En entrega)

### ItemSeeder
- Items tienen `precio_unitario` solo si la cotización tiene `precio_total`
- Precios aleatorios realistas entre 1000 y 50000

## Validación y Seguridad

1. **Verificación de vendedor**: Solo puede ver/editar sus propias cotizaciones
2. **Transacciones DB**: Uso de `DB::beginTransaction()` para garantizar integridad
3. **Validación de permisos**: Solo cotizaciones en estados Nuevo/Abierto son editables
4. **Manejo de errores**: Try-catch con rollback en caso de fallo

## Uso

### Para el Vendedor

1. **Ver cotizaciones**:
   - Navegar a "Cotizaciones" en el menú lateral
   - Ver listado con filtros (abiertos por defecto)

2. **Cotizar un pedido**:
   - Hacer clic en "Cotizar" o "Ver detalle"
   - Ingresar precios unitarios para cada item
   - Hacer clic en "Guardar cotización"
   - El sistema calculará automáticamente el total

3. **Ver cotización existente**:
   - Hacer clic en "Ver detalle"
   - Ver información completa y historial de estados

## Testing

Para probar el sistema:

```bash
# Recrear base de datos con datos de prueba
php artisan migrate:fresh --seed

# Acceder a cotizaciones del vendedor
http://localhost:8000/vendedor/cotizaciones?empleado_id=3
```

## Notas Técnicas

- Los subtotales se calculan dinámicamente con JavaScript
- El precio total se calcula en el backend al guardar
- Los estados se manejan mediante la tabla `cambios`
- La relación con productos usa `id_Producto` (con P mayúscula por convención de la BD)

## Mejoras Futuras

- [ ] Agregar validación de precios mínimos/máximos
- [ ] Permitir agregar/quitar items desde el detalle
- [ ] Agregar notas/observaciones a la cotización
- [ ] Notificaciones al cliente cuando se cotiza un pedido
- [ ] Historial de modificaciones de precios
- [ ] Exportar cotización a PDF
