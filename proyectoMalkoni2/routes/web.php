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
use App\Http\Controllers\ProductoClienteController;

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
    Route::get('/clientes/{empresa}/cotizaciones', [VendedorClienteController::class, 'cotizaciones'])->name('clientes.cotizaciones');
    Route::get('/clientes/{empresa}/ficha', [VendedorClienteController::class, 'ficha'])->name('clientes.ficha');

    Route::get('/cotizaciones', [VendedorCotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/{id}', [VendedorCotizacionController::class, 'detalle'])->name('cotizaciones.detalle');
    Route::put('/cotizaciones/{id}', [VendedorCotizacionController::class, 'guardar'])->name('cotizaciones.guardar');

    // Rutas para grupos
    Route::get('/grupos', [VendedorGrupoController::class, 'index'])->name('grupos.index');
    Route::post('/grupos', [VendedorGrupoController::class, 'store'])->name('grupos.store');
    Route::post('/grupos/{grupo}/empresas', [VendedorGrupoController::class, 'addEmpresa'])->name('grupos.add_empresa');
    Route::delete('/grupos/{grupo}/empresas/{empresa}', [VendedorGrupoController::class, 'removeEmpresa'])->name('grupos.remove_empresa');
    Route::delete('/grupos/{grupo}', [VendedorGrupoController::class, 'destroy'])->name('grupos.destroy');

});



// ==========================================================

// GRUPO DE RUTAS PARA EL CLIENTE (Dashboard y Menú)

// ==========================================================

Route::prefix('cliente')->name('cliente.')->group(function () {
    
    // Dashboard principal del Cliente
    Route::get('/dashboard', [ClienteDashboardController::class, 'dashboard'])->name('dashboard');

    // Rutas de Navegación del Sidebar
    Route::get('/cotizaciones', [ClienteDashboardController::class, 'cotizaciones'])->name('cotizaciones');
    Route::get('/nueva-cotizacion', [ClienteDashboardController::class, 'createQuotation'])->name('nueva_cotizacion'); 

    // --- NUEVAS RUTAS DE CREACIÓN Y FLUJO DE COTIZACIÓN ---
    // 1. Guarda la cotización inicial (POST del formulario)
    Route::post('/cotizacion/store', [ClienteDashboardController::class, 'storeQuotation'])->name('cotizacion.store');
    // 2. Vista para agregar productos a la cotización recién creada
    Route::get('/cotizacion/{id}/productos', [ClienteDashboardController::class, 'addProductsToQuotation'])->name('cotizacion.productos');
    // 3. Guarda productos a una cotización (POST)
    Route::post('/cotizacion/{id}/guardar-productos', [ClienteDashboardController::class, 'storeProductsToQuotation'])->name('cotizacion.guardar_productos');
    // 4. Elimina un item de la cotización
    Route::delete('/cotizacion/{cotizacionId}/item/{itemId}', [ClienteDashboardController::class, 'removeProductFromQuotation'])->name('cotizacion.eliminar_item');
    // ────────────────────────────────────────────────────

    Route::get('/opt', [ClienteDashboardController::class, 'goToOPT'])->name('opt'); 

    // Rutas de Notificaciones / Estados (Mensajes, Pedidos, etc.)
    Route::get('/mensajes', [ClienteDashboardController::class, 'messages'])->name('mensajes');
    Route::get('/pedidos-realizados', [ClienteDashboardController::class, 'completedOrders'])->name('pedidos_realizados');
    Route::get('/pedidos-sin-cotizar', [ClienteDashboardController::class, 'unquotedOrders'])->name('pedidos_sin_cotizar');
    Route::get('/pedidos-en-entrega', [ClienteDashboardController::class, 'deliveryOrders'])->name('pedidos_en_entrega');

    // Rutas de Acción de la Tabla (Ver/Editar Cotización)
    Route::get('/cotizacion/{id}/ver', [ClienteDashboardController::class, 'viewQuotation'])->name('cotizacion.ver');
    Route::get('/cotizacion/{id}/editar', [ClienteDashboardController::class, 'editQuotation'])->name('cotizacion.editar');
    
    // Rutas de Productos
    Route::get('/cotizacion/{cotizacionId}/agregar-productos-catalogo', [ProductoClienteController::class, 'agregarProducto'])->name('agregar_productos_catalogo');
    
});

// ==========================================================
// APIS PARA PRODUCTOS (Cliente)
// ==========================================================

Route::prefix('api/productos')->name('api.productos.')->group(function () {
    
    // APIs para búsqueda y filtros
    Route::get('/por-categoria/{categoriaId}', [ProductoClienteController::class, 'obtenerPorCategoria'])->name('por_categoria');
    Route::get('/buscar', [ProductoClienteController::class, 'buscar'])->name('buscar');
    
});