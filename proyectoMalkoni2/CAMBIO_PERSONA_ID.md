# Actualización: De cliente_id a persona_id

## Fecha: 18 de noviembre de 2025

## Cambios Realizados

### 1. Parámetro de URL
**ANTES**: `?cliente_id=1`
**AHORA**: `?persona_id=1`

### 2. Ejemplos de Acceso

#### Dashboard del Cliente
- **URL anterior**: `http://127.0.0.1:8000/cliente/dashboard?cliente_id=1`
- **URL nueva**: `http://127.0.0.1:8000/cliente/dashboard?persona_id=1`

#### Lista de Cotizaciones
- **URL anterior**: `http://127.0.0.1:8000/cliente/cotizaciones?cliente_id=1`
- **URL nueva**: `http://127.0.0.1:8000/cliente/cotizaciones?persona_id=1`

### 3. Banner del Cliente

**ANTES**:
```
┌─────────────────────────┐
│ 🏢 Constructora del Sur │
│    Cliente activo       │
└─────────────────────────┘
```

**AHORA**:
```
┌─────────────────────────┐
│ 👤 Nombre y Apellido    │
│    Constructora del Sur │
└─────────────────────────┘
```

- **Línea 1**: "Nombre y Apellido" (placeholder hasta conectar con malkoni_online)
- **Línea 2**: Nombre de la empresa asociada

### 4. Estructura de Personas

#### Antes:
- 1 persona por empresa (8 personas total)

#### Ahora:
- 2-3 personas por empresa (19 personas total)
- **Ejemplo**:
  - **Constructora del Sur S.A.** (id_empresa=1)
    - Persona 1 (id_persona=1)
    - Persona 2 (id_persona=2)
    - Persona 3 (id_persona=3)
  - **OPM Construcciones** (id_empresa=2)
    - Persona 4 (id_persona=4)
    - Persona 5 (id_persona=5)

### 5. Archivos Modificados

#### Controlador:
- `app/Http/Controllers/ClienteDashboardController.php`
  - Todos los métodos usan `$personaId` en lugar de `$clienteId`
  - Request parameter: `$request->get('persona_id', 1)`

#### Vistas:
- `resources/views/cliente/**/*.blade.php` (todas las vistas)
  - Todas las referencias a `cliente_id` → `persona_id`
  - Todas las variables `$clienteId` → `$personaId`

#### Seeder:
- `database/seeders/PersonaSeeder.php`
  - Genera 2-3 personas por empresa aleatoriamente
  - Mensaje: "✅ Personas creadas: 19 usuarios distribuidos en 8 empresas"

### 6. Cómo Probar

Para acceder como diferentes personas de la misma empresa:

```bash
# Persona 1 de Constructora del Sur
http://127.0.0.1:8000/cliente/dashboard?persona_id=1

# Persona 2 de Constructora del Sur
http://127.0.0.1:8000/cliente/dashboard?persona_id=2

# Persona 3 de Constructora del Sur
http://127.0.0.1:8000/cliente/dashboard?persona_id=3
```

Cada persona verá:
- **Banner**: "Nombre y Apellido" + "Constructora del Sur S.A."
- **Cotizaciones**: Solo las que esa persona específica creó

### 7. Integración Futura con malkoni_online

Cuando se conecte con `malkoni_online.sql`:
- Las personas tendrán nombres reales almacenados en la otra BD
- El banner mostrará el nombre real desde la tabla de usuarios
- La relación `id_empresa` ya está establecida y funcionando
- No se requieren cambios adicionales en la estructura actual

### 8. Verificación

Para verificar que funciona correctamente:

1. Acceder con diferentes `persona_id` (1, 2, 3, etc.)
2. El banner debe mostrar siempre "Nombre y Apellido"
3. El nombre de la empresa debe cambiar según la persona
4. Las cotizaciones mostradas deben ser diferentes para cada persona

---

**Nota importante**: El sistema ahora usa `persona_id` en todos los endpoints del cliente. Actualizar cualquier link o bookmark guardado con el nuevo parámetro.
