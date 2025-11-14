# 🏢 Sistema de Gestión Empresarial - Malkoni Hnos.

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.1.17-blue.svg" alt="Tailwind Version">
  <img src="https://img.shields.io/badge/Chart.js-4.x-yellow.svg" alt="Chart.js Version">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
  <img src="https://img.shields.io/badge/Status-Active_Development-brightgreen.svg" alt="Status">
</p>

## 📋 Descripción del Proyecto

Sistema web integral de gestión empresarial para **Malkoni Hnos.**, desarrollado con Laravel 12 y diseño completamente responsive. La plataforma ofrece múltiples interfaces especializadas para supervisores, vendedores y clientes, permitiendo la gestión completa de cotizaciones, productos, empleados y relaciones comerciales.

### ✨ Características Principales

- 🎛️ **Dashboard Supervisor**: Panel de control centralizado con métricas, gráficos interactivos y gestión completa
- 📱 **Diseño Responsive**: Interfaz completamente adaptable para móvil, tablet y desktop
- 👥 **Multi-Usuario**: Interfaces especializadas para supervisores, vendedores y clientes
- 📊 **Analytics Avanzados**: Visualización de datos con Chart.js y métricas en tiempo real
- 🛍️ **Gestión de Productos**: Catálogo completo con búsqueda, filtros y estadísticas de ventas
- 👨‍💼 **Gestión de Vendedores**: Control de equipos de ventas, clientes asignados y performance
- 📈 **Sistema de Cotizaciones**: Creación, seguimiento y gestión del flujo completo de ventas
- 🏢 **CRM Empresarial**: Gestión de empresas clientes con CUIT, contactos y historial
- 🎨 **Branding Malkoni**: Sistema de diseño coherente con colores y tipografías corporativas
- 🔄 **Estados Dinámicos**: Seguimiento detallado de cambios y evolución de cotizaciones

## 🗄️ Arquitectura de Base de Datos

### 📊 Estructura Completa (18 Migraciones)

| Tabla | Descripción | Relaciones Clave |
|-------|-------------|------------------|
| `users` | Sistema de autenticación Laravel | - |
| `roles` | Gestión de roles (Supervisor, Vendedor, Cliente) | `empleados` |
| `estados` | Estados de cotizaciones (Nuevo, Abierto, Cotizado, En entrega) | `cambios` |
| `personas` | Datos personales y contacto | `empleados`, `cotizaciones` |
| `empresas` | Empresas clientes con CUIT y datos comerciales | `cotizaciones`, `grupos` |
| `empleados` | Empleados del sistema con roles específicos | `personas`, `roles`, `cotizaciones` |
| `grupos` | Agrupación de clientes por vendedor | `empleados`, `empresas` |
| **Clasificación de Productos** | | |
| `tipos` | Clasificación principal | `subtipos` |
| `subtipos` | Subclasificación por tipo | `productos` |
| `categorias` | Categorización de productos | `subcategorias` |
| `subcategorias` | Subcategorización específica | `productos` |
| `productos` | Catálogo completo con precios y stock | `subtipos`, `subcategorias`, `items` |
| **Sistema de Cotizaciones** | | |
| `cotizaciones` | Cotizaciones generadas | `empresas`, `personas`, `empleados` |
| `items` | Items específicos de cada cotización | `cotizaciones`, `productos` |
| `cambios` | Historial completo de cambios de estado | `cotizaciones`, `estados` |

### 🔗 Relaciones Críticas del Sistema

- **🏢 Gestión de Clientes**: `empresas` ↔ `personas` ↔ `cotizaciones`
- **👥 Equipo de Ventas**: `empleados` ↔ `roles` ↔ `grupos` ↔ `empresas`
- **📦 Catálogo Jerárquico**: `tipos` → `subtipos` → `productos` ← `categorias` → `subcategorias`
- **📋 Flujo de Cotizaciones**: `cotizaciones` → `items` → `productos` + `cambios` → `estados`
- **📈 Trazabilidad**: `cambios` mantiene historial completo de cada cotización

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

## 📁 Arquitectura del Proyecto

```
proyectoMalkoni2/
├── app/
│   ├── Http/Controllers/              # 🎛️ Controladores por Rol
│   │   ├── SupervisorDashboardController.php    # Dashboard principal
│   │   ├── SupervisorVendedorController.php     # Gestión vendedores
│   │   ├── SupervisorProductoController.php     # Gestión productos
│   │   ├── VendedorDashboardController.php      # Panel vendedores
│   │   ├── VendedorClienteController.php        # Clientes asignados
│   │   ├── VendedorCotizacionController.php     # Cotizaciones vendedor
│   │   ├── VendedorGrupoController.php          # Grupos de clientes
│   │   ├── ClienteDashboardController.php       # Panel clientes
│   │   └── ProductoClienteController.php        # Productos para clientes
│   ├── Models/                        # 🗄️ Modelos Eloquent (15 modelos)
│   │   ├── User.php, Rol.php, Estado.php
│   │   ├── Persona.php, Empresa.php, Empleado.php, Grupo.php
│   │   ├── Tipo.php, Subtipo.php, Categoria.php, Subcategoria.php
│   │   ├── Producto.php, Cotizacion.php, Item.php, Cambio.php
│   └── Providers/                     # Service Providers Laravel
├── database/
│   ├── migrations/                    # 📊 18 Migraciones Estructuradas
│   │   ├── 0001_create_users_table.php
│   │   ├── 2025_10_22_205754_create_roles_table.php
│   │   ├── 2025_10_22_205811_create_estados_table.php
│   │   ├── ... (clasificación y productos)
│   │   └── 2025_10_22_210530_create_cambios_table.php
│   ├── seeders/                       # 🌱 Datos de Prueba Completos
│   │   ├── EstadoSeeder, RolSeeder, TipoSeeder
│   │   ├── PersonaSeeder, EmpresaSeeder, EmpleadoSeeder
│   │   ├── ProductoSeeder, CotizacionSeeder, CambioSeeder
│   └── factories/                     # Model Factories
├── resources/
│   ├── views/                         # 🎨 Vistas Organizadas por Rol
│   │   ├── layouts/app.blade.php      # Layout base responsive
│   │   ├── supervisor/                # Interface Supervisor
│   │   │   ├── dashboard.blade.php    # Dashboard principal
│   │   │   ├── components/            # Componentes reutilizables
│   │   │   │   ├── sidebar.blade.php, header.blade.php
│   │   │   │   ├── metrics.blade.php, charts.blade.php
│   │   │   │   └── tables.blade.php
│   │   │   ├── vendedores/            # Gestión vendedores
│   │   │   │   ├── index.blade.php, clientes.blade.php
│   │   │   │   └── cotizaciones.blade.php
│   │   │   └── productos/             # Gestión productos
│   │   │       ├── index.blade.php, show.blade.php
│   │   │       └── estadisticas.blade.php
│   │   ├── vendedor/                  # Interface Vendedores
│   │   ├── cliente/                   # Interface Clientes
│   │   └── components/                # Componentes globales
│   │       └── custom-pagination.blade.php
│   ├── css/app.css                    # 🎨 Tailwind CSS v4.1.17
│   └── js/app.js                      # ⚡ JavaScript + Chart.js
├── routes/
│   ├── web.php                        # 🛣️ Rutas por Prefijo Organizadas
│   └── console.php                    # Comandos Artisan
├── config/                            # ⚙️ Configuración Laravel
├── public/                            # 📦 Assets Compilados (Vite)
├── composer.json                      # 📦 Dependencias PHP
└── package.json                       # 📦 Dependencias Node.js
```

## 🛠️ Stack Tecnológico

### 🏗️ **Backend**
- **Laravel 12.x** - Framework PHP moderno con Eloquent ORM
- **PHP 8.2+** - Lenguaje base con tipado estricto
- **SQLite** (desarrollo) / **MySQL/PostgreSQL** (producción)
- **Artisan** - CLI para comandos personalizados

### 🎨 **Frontend**
- **Blade Templates** - Motor de plantillas Laravel
- **Tailwind CSS v4.1.17** - Framework CSS utility-first con configuración personalizada
- **Chart.js 4.x** - Gráficos interactivos para dashboards
- **Vite 7.x** - Bundler moderno para assets
- **JavaScript Vanilla** - Sin frameworks adicionales

### 📱 **Diseño & UX**
- **Responsive Design** - Mobile-first approach
- **Tipografías**: Syncopate (títulos) + Satoshi (textos)
- **Paleta Malkoni**: #E1DFD9, #D88429, #166379, #B1B7BB
- **Heroicons** - Iconografía consistente SVG
- **Sistema de Componentes** - Reutilizables y modulares

### 🔧 **Herramientas de Desarrollo**
- **Composer** - Gestión de dependencias PHP
- **NPM** - Gestión de dependencias Node.js
- **Laravel Pail** - Logs en tiempo real
- **Laravel Sail** - Docker environment
- **Concurrently** - Múltiples procesos simultáneos
- **Laravel Pint** - Code style fixer

### 📊 **DevOps & Deployment**
- **Git** - Control de versiones
- **Vite Build** - Optimización de assets para producción
- **Laravel Mix** - Alternativa de build process
- **Environment Variables** - Configuración flexible

## 📝 Funcionalidades Implementadas

### 🎛️ **Dashboard Supervisor** ✅
- **Panel Central**: Métricas en tiempo real (clientes online, cotizaciones en proceso, ingresos mensuales)
- **Gráficos Interactivos**: Chart.js con visualización de cotizaciones por vendedor
- **Navegación Intuitiva**: Sidebar responsive con acceso rápido a todas las secciones
- **Tabla de Últimas Cotizaciones**: Estados con colores, clientes y vendedores asignados
- **Ranking de Productos**: Top productos más cotizados con sistema de medallas
- **Responsive Design**: Funciona perfecto en móvil, tablet y desktop

### 👥 **Gestión de Vendedores** ✅
- **Lista Completa**: Todos los vendedores con email y DNI
- **Búsqueda Avanzada**: Por nombre o DNI/CUIT con resultados en tiempo real
- **Vista de Clientes**: Clientes asignados a cada vendedor
- **Seguimiento de Cotizaciones**: Historial completo por vendedor
- **Responsive Tables**: Columnas adaptativas según tamaño de pantalla
- **Paginación Inteligente**: Diferente para móvil y desktop

### 🛍️ **Gestión de Productos** ✅
- **Catálogo Completo**: 100 productos con clasificación jerárquica
- **Búsqueda Dual**: Por código (exacto) y nombre (similitud)
- **Métricas de Productos**: Total productos, cotizaciones e ingresos
- **Vista Detallada**: Información completa de cada producto
- **Estadísticas Avanzadas**: Panel dedicado para análisis de ventas
- **Ranking Visual**: Sistema de colores para identificar top productos

### 🎨 **Sistema de Diseño Malkoni** ✅
- **Paleta Corporativa**: 4 colores principales consistentes en toda la app
- **Tipografías Personalizadas**: Syncopate (headlines) + Satoshi (body text)
- **Componentes Modulares**: Header, sidebar, métricas, charts, tables reutilizables
- **Responsive Components**: Cada componente adaptado para todos los dispositivos
- **CSS Custom Properties**: Variables CSS para fácil mantenimiento

### 📱 **Diseño Responsive** ✅
- **Mobile-First Approach**: Diseño optimizado desde móvil hacia desktop
- **Breakpoints Inteligentes**: sm: 640px, md: 768px, lg: 1024px, xl: 1280px
- **Sidebar Adaptativo**: Oculto en móvil con menú hamburguesa, fijo en desktop
- **Tablas Responsivas**: Columnas se ocultan/muestran según dispositivo
- **Formularios Centrados**: Búsquedas optimizadas para móvil
- **Paginación Dual**: Versión móvil compacta y desktop completa

### 🗄️ **Arquitectura de Datos** ✅
- **18 Migraciones**: Estructura completa de base de datos
- **15 Modelos Eloquent**: Relaciones definidas y optimizadas
- **Seeders Completos**: Datos de prueba realistas para desarrollo
- **Relaciones Complejas**: Manejo de FK personalizadas y múltiples relaciones

### 🔄 **Funcionalidades en Desarrollo**
- **Sistema de Autenticación**: Login/registro con roles diferenciados
- **Panel de Vendedores**: Dashboard específico para el equipo de ventas
- **Panel de Clientes**: Interface para empresas clientes
- **CRUD Completo**: Crear, editar y eliminar productos/cotizaciones
- **Reportes Avanzados**: Exports PDF/Excel y analytics detallados
- **Sistema de Notificaciones**: Alertas en tiempo real
- **API REST**: Endpoints para integración con otras aplicaciones

## 📅 Historial de Desarrollo

### 🚀 **Noviembre 2025 - Release v2.0**

#### **13 Noviembre 2025** - Responsive Design Complete
- ✅ **Diseño Responsive Completo**: Todas las vistas adaptadas para móvil, tablet y desktop
- ✅ **Paginación Inteligente**: Sistema dual móvil/desktop con navegación optimizada
- ✅ **Formularios Centrados**: Búsquedas optimizadas para experiencia móvil
- ✅ **Tablas Adaptativas**: Columnas se ocultan/muestran según resolución
- ✅ **Testing Completo**: Verificación en múltiples dispositivos y breakpoints

#### **12 Noviembre 2025** - Database Integration
- ✅ **Migración Base de Datos**: Adaptación a nueva estructura con 18 tablas
- ✅ **Modelos Actualizados**: Relaciones corregidas para nuevas FK
- ✅ **Controladores Sincronizados**: Queries adaptadas a estructura actual
- ✅ **Seeders Operacionales**: Datos de prueba realistas y coherentes
- ✅ **Estados y Colores**: Sistema de estados con colores corporativos

#### **10 Noviembre 2025** - Architecture Refactor
- ✅ **Reorganización de Controladores**: Prefijo "Supervisor" para organización
  - `DashboardController` → `SupervisorDashboardController`
  - `VendedorController` → `SupervisorVendedorController`  
  - `ProductoController` → `SupervisorProductoController`
- ✅ **Implementación MVC Completa**: Eliminación de closures, arquitectura limpia
- ✅ **Sistema de Componentes**: Componentes modulares y reutilizables
- ✅ **Dashboard Supervisor**: Interface completa con métricas y navegación

### 🗂️ **Estructura de Rutas Actual**
```php
// 🏠 Dashboard Principal
Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index']);

// 👥 Gestión de Vendedores
Route::prefix('supervisor/vendedor')->name('vendedor.')->group(function () {
    Route::get('/', [SupervisorVendedorController::class, 'index']);
    Route::get('/search', [SupervisorVendedorController::class, 'search']);
    Route::get('/{id}/clientes', [SupervisorVendedorController::class, 'clientes']);
    Route::get('/{id}/cotizaciones', [SupervisorVendedorController::class, 'cotizaciones']);
});

// 📦 Gestión de Productos
Route::prefix('supervisor/productos')->name('productos.')->group(function () {
    Route::get('/', [SupervisorProductoController::class, 'index']);
    Route::get('/search', [SupervisorProductoController::class, 'search']);
    Route::get('/{id}', [SupervisorProductoController::class, 'show']);
    Route::get('/{id}/estadisticas', [SupervisorProductoController::class, 'estadisticas']);
});

// 👨‍💼 Panel de Vendedores
Route::prefix('vendedor')->name('vendedor.app.')->group(function () {
    Route::get('/dashboard', [VendedorDashboardController::class, 'index']);
    Route::get('/clientes', [VendedorClienteController::class, 'index']);
    Route::get('/cotizaciones', [VendedorCotizacionController::class, 'index']);
    Route::get('/grupos', [VendedorGrupoController::class, 'index']);
});

// 🏢 Panel de Clientes
Route::prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [ClienteDashboardController::class, 'index']);
    Route::get('/cotizaciones', [ClienteDashboardController::class, 'cotizaciones']);
    Route::post('/cotizacion/store', [ClienteDashboardController::class, 'storeQuotation']);
    Route::get('/cotizacion/{id}/productos', [ClienteDashboardController::class, 'addProductsToQuotation']);
});
```

### 📊 **Métricas del Proyecto**
- **Controladores**: 10 controladores especializados
- **Modelos**: 15 modelos Eloquent con relaciones
- **Migraciones**: 18 migraciones estructuradas
- **Vistas**: 25+ vistas Blade organizadas
- **Componentes**: 8 componentes reutilizables
- **Rutas**: 30+ rutas organizadas por prefijo
- **Responsive**: 100% compatible móvil/desktop

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

## 🎯 Roadmap Futuro

### 🚀 **Próximas Características (Q1 2026)**
- **Autenticación Completa**: Login/logout con roles diferenciados
- **CRUD Avanzado**: Crear, editar, eliminar productos y cotizaciones
- **Sistema de Archivos**: Upload de imágenes y documentos
- **Notificaciones**: Sistema en tiempo real con WebSockets
- **API REST**: Endpoints completos para integración
- **Exports**: PDF/Excel para reportes y cotizaciones

### 📈 **Optimizaciones Técnicas**
- **Caching**: Redis para mejor performance
- **Queue Jobs**: Procesamiento en background
- **Testing**: PHPUnit test suite completa
- **CI/CD**: Pipeline automatizado con GitHub Actions
- **Docker**: Containerización completa
- **PWA**: Progressive Web App capabilities

---

<div align="center">

**🏢 © 2025 Malkoni Hnos. - Sistema de Gestión Empresarial**

*Desarrollado con ❤️ usando Laravel 12 + Tailwind CSS v4*

[![Laravel](https://img.shields.io/badge/Built_with-Laravel_12-red.svg)](https://laravel.com)
[![Tailwind](https://img.shields.io/badge/Styled_with-Tailwind_v4-blue.svg)](https://tailwindcss.com)
[![Responsive](https://img.shields.io/badge/Design-Responsive-green.svg)](https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design)

</div>
