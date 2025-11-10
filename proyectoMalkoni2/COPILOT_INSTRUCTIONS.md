# 🤖 Manual de Instrucciones para Copilot - Proyecto Malkoni

## 📖 LECTURA OBLIGATORIA ANTES DE CUALQUIER MODIFICACIÓN

Este documento debe ser consultado **SIEMPRE** antes de realizar cambios en el proyecto.

---

## 🎯 Información del Proyecto

### Descripción
- **Empresa**: Malkoni Hnos.
- **Tipo**: Sistema de pedidos online
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Base de datos**: MySQL/PostgreSQL con migraciones específicas

### Propósito
Sistema integral para gestión de cotizaciones, productos, servicios y clientes empresariales.

---

## 🏗️ Arquitectura y Convenciones

### Estructura de Base de Datos
El proyecto utiliza una estructura específica con **IDs personalizados**:

```php
// ❌ NO usar auto-increment genérico
$table->id();

// ✅ SÍ usar IDs específicos según la tabla
$table->id('id_producto');    // Para productos
$table->id('id_empresa');     // Para empresas  
$table->id('id_persona');     // Para personas
$table->id('id_empleado');    // Para empleados
```

### Nombres de Tablas y Campos
- **Tablas**: Plural en español (`productos`, `empresas`, `cotizaciones`)
- **Campos**: Snake_case en español (`precio_base`, `fecha_creacion`)
- **Foráneas**: Formato `id_[tabla_singular]` (`id_producto`, `id_empresa`)

### Migraciones Existentes
**⚠️ CRÍTICO**: Las siguientes migraciones ya existen y **NO deben modificarse**:

```
2025_10_22_205754_create_roles_table.php
2025_10_22_205803_create_rubros_table.php
2025_10_22_205811_create_estados_table.php
2025_10_22_205818_create_personas_table.php
2025_10_22_205826_create_empresas_table.php
2025_10_22_205835_create_servicios_table.php
2025_10_22_210313_create_empleados_table.php
2025_10_22_210319_create_grupos_table.php
2025_10_22_210330_create_subrubros_table.php
2025_10_22_210346_create_subdivisions_table.php
2025_10_22_210353_create_categorias_table.php
2025_10_22_210402_create_productos_table.php
2025_10_22_210421_create_cotizaciones_table.php
2025_10_22_210428_create_cambios_table.php
2025_10_22_210433_create_items_table.php
```

---

## 🔧 Reglas de Desarrollo

### 1. **Modelos Eloquent**
```php
// ✅ Definir primaryKey personalizada
class Producto extends Model
{
    protected $primaryKey = 'id_producto';
    protected $table = 'productos';
    
    // Definir relaciones usando las FK correctas
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
}
```

### 2. **Controladores**
- Usar Resource Controllers cuando sea posible
- Validación mediante Form Requests
- Nomenclatura: `ProductoController`, `EmpresaController`

### 3. **Rutas**
```php
// ✅ Agrupación lógica
Route::prefix('admin')->group(function () {
    Route::resource('productos', ProductoController::class);
    Route::resource('empresas', EmpresaController::class);
});
```

### 4. **Vistas**
- Usar Blade components para reutilización
- Estructura: `resources/views/[módulo]/[acción].blade.php`
- Ejemplo: `resources/views/productos/index.blade.php`

---

## 📋 Entidades y Relaciones Críticas

### Productos
```php
Schema::create('productos', function (Blueprint $table) {
    $table->id('id_producto');
    $table->string('nombre', 255);
    $table->text('descripcion')->nullable();
    $table->integer('precio_base');
    $table->string('foto')->nullable();
    $table->integer('promocion')->default(0); // 0-1
    $table->integer('descuento')->default(0); // 0-100
    $table->integer('precio_final');
    $table->foreignId('id_categoria')->constrained('categorias', 'id_categoria');
});
```

### Cotizaciones
```php
Schema::create('cotizaciones', function (Blueprint $table) {
    $table->id(); // ⚠️ Esta tabla usa ID genérico
    $table->string('titulo', 255);
    $table->integer('numero')->unique();
    $table->dateTime('fyh');
    $table->integer('precio_total');
    $table->foreignId('id_empleados')->constrained('empleados', 'id_empleado');
    $table->foreignId('id_empresas')->constrained('empresas', 'id_empresa');
    $table->foreignId('id_personas')->constrained('personas', 'id_persona');
});
```

### Items (Productos O Servicios)
```php
Schema::create('items', function (Blueprint $table) {
    $table->id('id_item');
    $table->integer('cantidad');
    $table->foreignId('id_cotizaciones')->constrained('cotizaciones', 'id');
    // ⚠️ Un item puede ser producto O servicio (nullable)
    $table->foreignId('id_producto')->nullable()->constrained('productos', 'id_producto');
    $table->foreignId('id_servicio')->nullable()->constrained('servicios', 'id_servicio');
});
```

---

## ⚠️ RESTRICCIONES CRÍTICAS

### ❌ NO HACER JAMÁS:
1. **Modificar migraciones existentes** - Crear nuevas si necesitas cambios
2. **Cambiar nombres de campos FK** - Respeta `id_producto`, `id_empresa`, etc.
3. **Usar IDs genéricos** - Cada tabla tiene su ID específico (excepto cotizaciones)
4. **Eliminar campos existentes** - Solo agregar nuevos
5. **Cambiar tipos de datos** - Los precios son `integer`, fechas `dateTime`, etc.

### ✅ SÍ HACER SIEMPRE:
1. **Leer este manual** antes de cualquier cambio
2. **Crear seeders** para datos de prueba
3. **Usar validaciones** en Form Requests
4. **Documentar cambios** en commits
5. **Mantener consistencia** con convenciones existentes

---

## 🧪 Testing y Calidad

### Comandos Importantes
```bash
# Setup completo del proyecto
composer run setup

# Desarrollo con todos los servicios
composer run dev

# Tests
composer run test

# Solo servidor
php artisan serve
```

### Datos de Prueba
- Usar factories para generar datos de test
- Respetar las relaciones FK al crear seeders
- Validar que los precios sean integers (centavos)

---

## 🚨 Casos Especiales

### 1. **Precios**
- Todos los precios se almacenan como **integers** (centavos)
- `precio_base`: 1500 = $15.00
- `precio_final`: calculado con descuentos

### 2. **Empresas**
- CUIT debe ser `bigInteger` y único
- Validar formato de CUIT argentino

### 3. **Estados y Cambios**
- Cada cambio de estado se registra en tabla `cambios`
- Incluye timestamp (`fyH`) y empleado responsable

### 4. **Items Flexibles**
- Un item puede tener `id_producto` O `id_servicio`
- Nunca ambos, nunca ninguno
- Validar en el modelo/controller

---

## 📞 Contacto y Soporte

Si tienes dudas sobre estas convenciones:
1. Consulta las migraciones existentes
2. Revisa los modelos ya creados
3. Mantén la consistencia con el código existente

---

**🔄 Última actualización**: 10 de Noviembre, 2025  
**📌 Versión del manual**: 1.0

---

## 📝 Checklist Pre-Modificación

Antes de hacer cualquier cambio, verifica:

- [ ] ¿Leí completamente este manual?
- [ ] ¿Entiendo la estructura de BD existente?
- [ ] ¿Mi cambio respeta las convenciones?
- [ ] ¿Necesito crear nueva migración o modificar existente?
- [ ] ¿Las relaciones FK están correctas?
- [ ] ¿Los nombres siguen el patrón establecido?
- [ ] ¿Agregué validaciones necesarias?
- [ ] ¿Documenté el cambio adecuadamente?

**🎯 Solo procede si TODAS las respuestas son afirmativas.**