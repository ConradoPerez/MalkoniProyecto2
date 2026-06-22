# 🌱 Guía de Seeders - Malkoni Hnos

## ¿Qué son los Seeders?
Los seeders son archivos que **populan automáticamente la base de datos** con datos de prueba consistentes. Todos en el equipo pueden tener exactamente los mismos datos.

## 📋 Datos que se Crean

### 👥 Empleados (7 personas)
- **Supervisores**: Carlos Alberto Malkoni, María Elena Rodriguez  
- **Vendedores**: Juan Carlos Pérez, Ana Sofía González, Roberto Daniel López, Carmen Isabel Torres
- **Admin**: Luis Eduardo Malkoni

### 🏢 Empresas Clientes (8 empresas)
- Constructora del Sur S.A.
- OPM Construcciones
- DIN Propiedades
- CIR Maderas
- MAO Muebles
- RIC Construcciones
- Premium Aberturas
- EcoArq

### 📦 Productos (15 productos específicos del rubro)
- **Maderas**: Tabla de Pino 2x4x3m, Listón de Eucalipto, Viga de Cedro
- **Herrajes**: Cerradura Multipunto, Bisagras Piano, Manijas de Bronce
- **Aberturas**: Puertas Placa, Ventanas Aluminio, Portones
- **Sistemas Corredizos**: Rieles, Guías
- **Accesorios**: Tornillería, Burletes
- **Vidrios**: Vidrio Templado 6mm

### 📊 Cotizaciones (20 cotizaciones)
- Distribuidas en los últimos 8 meses
- Asignadas a diferentes vendedores
- Con títulos realistas como "Reforma integral oficina comercial"
- Items específicos para cada cotización

## 🚀 Comandos para Usar

### 1️⃣ **Limpiar y Crear Base de Datos Fresca**
```bash
# Resetear migraciones y ejecutar seeders
php artisan migrate:fresh --seed
```

### 2️⃣ **Solo Ejecutar Seeders (sin borrar datos existentes)**
```bash
# Ejecutar todos los seeders
php artisan db:seed
```

### 3️⃣ **Ejecutar Seeder Específico**
```bash
# Solo productos
php artisan db:seed --class=ProductoSeeder

# Solo empleados  
php artisan db:seed --class=EmpleadoSeeder
```

## ✅ **Verificar que Funcionó**
```bash
# Verificar datos creados
php artisan tinker --execute="echo 'Productos: ' . App\Models\Producto::count();"
```

## 🎯 **URLs de Prueba**
- **Dashboard Vendedor**: `http://localhost:8000/vendedor/dashboard?empleado_id=3`
- **Dashboard Supervisor**: `http://localhost:8000/supervisor/dashboard`

## 📝 **Credenciales de Prueba**
- **Vendedores**: `vendedor123`
- **Supervisores**: `supervisor123` 
- **Admin**: `admin123`

## ⚠️ **Importante**
- Ejecutar `migrate:fresh --seed` **BORRA todos los datos existentes**
- Usar solo en desarrollo, nunca en producción
- Todo el equipo tendrá exactamente los mismos datos

---
*Datos creados específicamente para Malkoni Hnos - Empresa de Maderas, Herrajes y Aberturas*