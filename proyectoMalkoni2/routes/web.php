<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Controllers\SupervisorVendedorController;
use App\Http\Controllers\SupervisorProductoController;
use App\Http\Controllers\VendedorDashboardController;
use App\Http\Controllers\VendedorClienteController;
use App\Http\Controllers\VendedorCotizacionController;
use App\Http\Controllers\VendedorGrupoController;
use App\Http\Controllers\ClienteDashboardController;


Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');

// Dashboard Vendedor
Route::get('/vendedor/dashboard', [VendedorDashboardController::class, 'index'])->name('vendedor.dashboard');

// AJAX Routes for Vendedor Dashboard
Route::prefix('vendedor/api')->name('vendedor.api.')->group(function () {
    Route::get('/cotizaciones-chart', [VendedorDashboardController::class, 'getCotizacionesBarChart'])->name('cotizaciones.chart');
});
 
// vendedor
Route::prefix('supervisor/vendedor')->name('vendedor.')->group(function () {
    Route::get('/', [SupervisorVendedorController::class, 'index'])->name('index');
    Route::get('/search', [SupervisorVendedorController::class, 'search'])->name('search');
    Route::get('/{id}/clientes', [SupervisorVendedorController::class, 'clientes'])->name('clientes');
    Route::get('/{id}/cotizaciones', [SupervisorVendedorController::class, 'cotizaciones'])->name('cotizaciones');
});

// Productos
Route::prefix('supervisor/productos')->name('productos.')->group(function () {
    Route::get('/', [SupervisorProductoController::class, 'index'])->name('index');
    Route::get('/search', [SupervisorProductoController::class, 'search'])->name('search');
    Route::get('/{id}', [SupervisorProductoController::class, 'show'])->name('show');
    Route::get('/{id}/estadisticas', [SupervisorProductoController::class, 'estadisticas'])->name('estadisticas');
});

// Rutas adicionales para el vendedor
Route::prefix('vendedor')->name('vendedor.app.')->group(function () {
    Route::get('/clientes', [VendedorClienteController::class, 'index'])->name('clientes.index');
    Route::get('/cotizaciones', [VendedorCotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/grupos', [VendedorGrupoController::class, 'index'])->name('grupos.index');
});

// ==========================================================
// GRUPO DE RUTAS PARA EL CLIENTE (Dashboard y Menú)
// ==========================================================
Route::prefix('cliente')->name('cliente.')->group(function () {
    
    // Dashboard principal del Cliente
    // (Llama al método 'dashboard' que configuramos en el controlador)
    Route::get('/dashboard', [ClienteDashboardController::class, 'dashboard'])->name('dashboard');

    // Rutas de Navegación del Sidebar
    // Nota: El método en el controlador debería llamarse 'index' o 'cotizaciones'
    Route::get('/cotizaciones', [ClienteDashboardController::class, 'cotizaciones'])->name('cotizaciones');
    Route::get('/nueva-cotizacion', [ClienteDashboardController::class, 'createQuotation'])->name('nueva_cotizacion'); // Si el método existe
    Route::get('/opt', [ClienteDashboardController::class, 'goToOPT'])->name('opt'); // Si el método existe

    // Rutas de Notificaciones / Estados (Mensajes, Pedidos, etc.)
    Route::get('/mensajes', [ClienteDashboardController::class, 'messages'])->name('mensajes');
    Route::get('/pedidos-realizados', [ClienteDashboardController::class, 'completedOrders'])->name('pedidos_realizados');
    Route::get('/pedidos-sin-cotizar', [ClienteDashboardController::class, 'unquotedOrders'])->name('pedidos_sin_cotizar');
    Route::get('/pedidos-en-entrega', [ClienteDashboardController::class, 'deliveryOrders'])->name('pedidos_en_entrega');

    // Rutas de Acción de la Tabla (Ver/Editar Cotización)
    Route::get('/cotizacion/{id}/ver', [ClienteDashboardController::class, 'viewQuotation'])->name('cotizacion.ver');
    Route::get('/cotizacion/{id}/editar', [ClienteDashboardController::class, 'editQuotation'])->name('cotizacion.editar');
});