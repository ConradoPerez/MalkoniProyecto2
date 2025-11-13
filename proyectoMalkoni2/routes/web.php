<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Controllers\SupervisorVendedorController;
use App\Http\Controllers\SupervisorProductoController;
use App\Http\Controllers\VendedorDashboardController;
use App\Http\Controllers\VendedorClienteController;
use App\Http\Controllers\VendedorCotizacionController;
use App\Http\Controllers\VendedorGrupoController;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');

// Dashboard Vendedor
Route::get('/vendedor/dashboard', [VendedorDashboardController::class, 'index'])->name('vendedor.dashboard');
 
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
