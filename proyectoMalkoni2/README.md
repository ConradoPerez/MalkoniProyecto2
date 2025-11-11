# 🏢 Sistema de Pedidos Online - Malkoni Hnos.

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## 📋 Descripción del Proyecto

Sistema web integral para la gestión de pedidos online de **Malkoni Hnos.**, desarrollado con Laravel 12. La plataforma permite la gestión completa de cotizaciones, productos, servicios, empleados y clientes empresariales.

### ✨ Características Principales

- 🛒 **Gestión de Productos y Servicios**: Catálogo completo con categorías, subcategorías y precios dinámicos
- 📊 **Sistema de Cotizaciones**: Creación, seguimiento y gestión de cotizaciones para empresas
- 👥 **Gestión de Usuarios**: Sistema de roles (empleados, clientes, administradores)
- 🏢 **Clientes Empresariales**: Registro y gestión de empresas con CUIT
- 📈 **Seguimiento de Estados**: Control de cambios y estados de cotizaciones
- 🎯 **Sistema de Items**: Gestión detallada de productos y servicios en cotizaciones

## 🗄️ Estructura de Base de Datos

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `roles` | Gestión de roles de usuario (Admin, Empleado, Cliente) |
| `rubros` | Categorización principal de productos/servicios |
| `subrubros` | Subcategorización específica |
| `estados` | Estados de las cotizaciones (Pendiente, Aprobado, etc.) |
| `personas` | Datos personales de usuarios |
| `empresas` | Información de empresas clientes (CUIT, nombre, etc.) |
| `empleados` | Empleados del sistema |
| `grupos` | Agrupación de empleados |
| `servicios` | Catálogo de servicios ofrecidos |
| `subdivisions` | Divisiones organizacionales |
| `categorias` | Categorías de productos |
| `productos` | Catálogo de productos con precios y promociones |
| `cotizaciones` | Cotizaciones generadas para clientes |
| `items` | Items específicos de cada cotización |
| `cambios` | Historial de cambios de estado |

### 🔗 Relaciones Clave

- **Productos ↔ Categorías**: Cada producto pertenece a una categoría
- **Cotizaciones ↔ Empresas/Personas**: Vinculación con clientes
- **Items ↔ Productos/Servicios**: Items pueden ser productos O servicios
- **Cambios ↔ Estados**: Seguimiento de evolución de cotizaciones

## 🚀 Instalación y Configuración

### Prerrequisitos

- PHP 8.2 o superior
- Composer
- Node.js 18+ y npm
- MySQL/PostgreSQL/SQLite
- Git

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/ConradoPerez/MalkoniProyecto2.git
cd MalkoniProyecto2/proyectoMalkoni2
```

2. **Instalar dependencias de PHP**
```bash
composer install
```

3. **Instalar dependencias de Node.js**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos en `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malkoni_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

6. **Ejecutar migraciones**
```bash
php artisan migrate
```

7. **Compilar assets**
```bash
npm run build
```

### 🔧 Configuración Rápida

Usar el script de setup automatizado:
```bash
composer run setup
```

## 🎯 Comandos de Desarrollo

### Servidor de Desarrollo
```bash
# Servidor completo con queue, logs y Vite
composer run dev

# Solo servidor Laravel
php artisan serve
```

### Base de Datos
```bash
# Ejecutar migraciones
php artisan migrate

# Rollback migraciones
php artisan migrate:rollback

# Refresh migraciones
php artisan migrate:refresh
```

### Tests
```bash
composer run test
```

## 📁 Estructura del Proyecto

```
proyectoMalkoni2/
├── app/
│   ├── Http/Controllers/     # Controladores organizados por tipo de usuario
│   │   ├── SupervisorDashboardController.php
│   │   ├── SupervisorVendedorController.php
│   │   └── SupervisorProductoController.php
│   ├── Models/              # Modelos Eloquent
│   └── Providers/           # Service Providers
├── database/
│   ├── migrations/          # 18 migraciones para estructura completa
│   ├── seeders/            # Seeders
│   └── factories/          # Model Factories
├── resources/
│   ├── views/              # Vistas Blade organizadas
│   │   ├── layouts/        # Layouts principales
│   │   └── supervisor/     # Vistas del supervisor
│   │       ├── components/ # Componentes reutilizables
│   │       ├── vendedores/ # Gestión de vendedores
│   │       └── productos/  # Gestión de productos
│   ├── js/                 # JavaScript con Chart.js
│   └── css/                # Tailwind CSS v4 + Malkoni branding
├── routes/
│   ├── web.php             # Rutas organizadas por prefijo
│   └── console.php         # Comandos Artisan
└── public/                 # Assets compilados por Vite
```

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade Templates, Vite, Tailwind CSS v4.1.17
- **Base de Datos**: SQLite (desarrollo), MySQL/PostgreSQL (producción)
- **Visualización**: Chart.js para dashboards y estadísticas
- **Iconografía**: Heroicons para interfaz consistente
- **Tipografías**: Syncopate (títulos), Satoshi (textos)
- **Tools**: Composer, NPM, Laravel Pail, Laravel Sail

## 📝 Funcionalidades Implementadas

### 🎛️ **Dashboard Supervisor** ✅
- Panel de control principal con métricas clave
- Gráficos de rendimiento (Chart.js)
- Navegación lateral organizada
- Diseño responsivo con branding Malkoni

### 👥 **Gestión de Vendedores** ✅
- Lista completa de vendedores
- Búsqueda por nombre y DNI
- Vista de clientes por vendedor
- Seguimiento de cotizaciones por vendedor
- Arquitectura MVC con SupervisorVendedorController

### 🛍️ **Gestión de Productos** ✅
- Catálogo completo de productos
- Búsqueda por código y nombre
- Vista detallada de productos individuales
- Estadísticas avanzadas de ventas por producto
- Arquitectura MVC con SupervisorProductoController

### 🎨 **Sistema de Diseño Malkoni** ✅
- Paleta de colores corporativa (#E1DFD9, #D88429, #166379, #B1B7BB)
- Tipografías Syncopate y Satoshi
- Componentes reutilizables (sidebar, header, métricas)
- Tailwind CSS v4 con configuración personalizada

### 📊 **Funcionalidades Futuras**
- Sistema de autenticación y roles
- CRUD completo de productos y servicios
- Sistema de cotizaciones integrado
- Reportes y analytics avanzados
- Panel de cliente y vendedor

## � Changelog Reciente

### 📅 **10 Noviembre 2025**
- ✅ **Reorganización de Controladores**: Renombrados con prefijo "Supervisor" para mejor organización
  - `DashboardController` → `SupervisorDashboardController`
  - `VendedorController` → `SupervisorVendedorController`
  - `ProductoController` → `SupervisorProductoController`
- ✅ **Implementación MVC Completa**: Eliminadas closures, uso exclusivo de controladores
- ✅ **Sistema de Productos**: Vistas completas (listado, detalles, estadísticas)
- ✅ **Sistema de Vendedores**: Gestión completa con clientes y cotizaciones
- ✅ **Dashboard Supervisor**: Interface completa con métricas y navegación

### 📋 **Estructura de Rutas Actualizada**
```php
// Dashboard principal
Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index']);

// Gestión de vendedores
Route::prefix('supervisor/vendedores')->name('vendedores.')->group(function () {
    Route::get('/', [SupervisorVendedorController::class, 'index']);
    Route::get('/search', [SupervisorVendedorController::class, 'search']);
    Route::get('/{id}/clientes', [SupervisorVendedorController::class, 'clientes']);
    Route::get('/{id}/cotizaciones', [SupervisorVendedorController::class, 'cotizaciones']);
});

// Gestión de productos  
Route::prefix('supervisor/productos')->name('productos.')->group(function () {
    Route::get('/', [SupervisorProductoController::class, 'index']);
    Route::get('/search', [SupervisorProductoController::class, 'search']);
    Route::get('/{id}', [SupervisorProductoController::class, 'show']);
    Route::get('/{id}/estadisticas', [SupervisorProductoController::class, 'estadisticas']);
});
```

## �🔒 Seguridad

- Validación de datos en formularios
- Protección CSRF
- Sanitización de inputs
- Control de acceso basado en roles (futuro)

## 📄 Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## 👨‍💻 Desarrollo

Para contribuir al proyecto:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

---

**© 2025 Malkoni Hnos. - Sistema de Pedidos Online**
