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
use App\Http\Controllers\MensajeCotizacionController;
use App\Http\Controllers\Auth\LoginController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/auth/sso-bridge', [LoginController::class, 'ssoBridge'])->name('auth.sso_bridge');

Route::get('/register', function () {
    return redirect()->away('https://online.malkoni.com.ar/public/tipo_identidad.php');
})->name('register');
// Dashboard Supervisor
Route::middleware('role:supervisor')->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
});

// Dashboard y API Vendedor
Route::middleware('role:vendedor')->group(function () {
    Route::get('/vendedor/dashboard', [VendedorDashboardController::class, 'index'])->name('vendedor.dashboard');
    Route::prefix('vendedor/api')->name('vendedor.api.')->group(function () {
        Route::get('/cotizaciones-chart', [VendedorDashboardController::class, 'getCotizacionesBarChart'])->name('cotizaciones.chart');
    });
});

// vendedor (supervisor)

Route::middleware('role:supervisor')->prefix('supervisor/vendedor')->name('vendedor.')->group(function () {
    Route::get('/', [SupervisorVendedorController::class, 'index'])->name('index');
    Route::get('/search', [SupervisorVendedorController::class, 'search'])->name('search');
    Route::get('/{id}/clientes', [SupervisorVendedorController::class, 'clientes'])->name('clientes');
    Route::get('/{id}/cotizaciones', [SupervisorVendedorController::class, 'cotizaciones'])->name('cotizaciones');
});

// Productos

Route::middleware('role:supervisor')->prefix('supervisor/productos')->name('productos.')->group(function () {

    Route::get('/', [SupervisorProductoController::class, 'index'])->name('index');

    Route::get('/search', [SupervisorProductoController::class, 'search'])->name('search');

    Route::get('/{id}', [SupervisorProductoController::class, 'show'])->name('show');

    Route::get('/{id}/estadisticas', [SupervisorProductoController::class, 'estadisticas'])->name('estadisticas');

});



// Rutas adicionales para el vendedor

Route::middleware('role:vendedor')->prefix('vendedor')->name('vendedor.app.')->group(function () {

    Route::get('/clientes', [VendedorClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/{empresa}/cotizaciones', [VendedorClienteController::class, 'cotizaciones'])->name('clientes.cotizaciones');
    Route::get('/clientes/{empresa}/ficha', [VendedorClienteController::class, 'ficha'])->name('clientes.ficha');

    Route::get('/cotizaciones', [VendedorCotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/{id}', [VendedorCotizacionController::class, 'detalle'])->name('cotizaciones.detalle');
    Route::get('/cotizaciones/{id}/plano/descargar', [VendedorCotizacionController::class, 'descargarPlano'])->name('cotizaciones.plano.descargar');
    Route::put('/cotizaciones/{id}', [VendedorCotizacionController::class, 'guardar'])->name('cotizaciones.guardar');

    // Chat por cotización (vendedor)
    Route::get('/cotizaciones/{id}/mensajes', [MensajeCotizacionController::class, 'index'])->name('cotizaciones.mensajes.index');
    Route::post('/cotizaciones/{id}/mensajes', [MensajeCotizacionController::class, 'store'])->name('cotizaciones.mensajes.store');
    Route::post('/cotizaciones/{id}/mensajes/leidos', [MensajeCotizacionController::class, 'marcarLeidos'])->name('cotizaciones.mensajes.leidos');

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

Route::middleware('role:cliente')->prefix('cliente')->name('cliente.')->group(function () {
    
    // Dashboard principal del Cliente
    Route::get('/dashboard', [ClienteDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/nueva-cotizacion', [ClienteDashboardController::class, 'createQuotation'])->name('nueva_cotizacion'); 

    // --- NUEVAS RUTAS DE CREACIÓN Y FLUJO DE COTIZACIÓN ---
    // 1. Procesa la selección de vendedor y va a selección de productos (POST del formulario)
    Route::post('/cotizacion/preparar', [ClienteDashboardController::class, 'prepareQuotation'])->name('cotizacion.preparar');
    // 2. Vista para agregar productos (sin cotización creada aún)
    Route::get('/nueva-cotizacion/productos', [ClienteDashboardController::class, 'selectProducts'])->name('cotizacion.productos');
    // 3. Crea cotización y guarda productos (POST)
    Route::post('/cotizacion/crear-con-productos', [ClienteDashboardController::class, 'createQuotationWithProducts'])->name('cotizacion.crear_con_productos');
    // 4. Vista para agregar productos a cotización existente
    Route::get('/cotizacion/{id}/productos', [ClienteDashboardController::class, 'addProductsToQuotation'])->name('cotizacion.agregar_productos');
    // 5. Guarda productos adicionales a una cotización existente (POST)
    Route::post('/cotizacion/{id}/guardar-productos', [ClienteDashboardController::class, 'storeProductsToQuotation'])->name('cotizacion.guardar_productos');
    // 6. Elimina un item de la cotización
    Route::delete('/cotizacion/{cotizacionId}/item/{itemId}', [ClienteDashboardController::class, 'removeProductFromQuotation'])->name('cotizacion.eliminar_item');
    Route::get('/cotizacion/{id}/plano/descargar', [ClienteDashboardController::class, 'downloadOptPlano'])->name('cotizacion.plano.descargar');
    // ────────────────────────────────────────────────────

    Route::get('/opt', [ClienteDashboardController::class, 'goToOPT'])->name('opt'); 

    // Rutas de Notificaciones / Estados (Mensajes, Pedidos, etc.)
    Route::get('/mensajes', [ClienteDashboardController::class, 'messages'])->name('mensajes');
    Route::get('/pedidos-realizados', [ClienteDashboardController::class, 'completedOrders'])->name('pedidos_realizados');
    Route::get('/pedidos-sin-cotizar', [ClienteDashboardController::class, 'unquotedOrders'])->name('pedidos_sin_cotizar');
    Route::get('/pedidos-en-entrega', [ClienteDashboardController::class, 'deliveryOrders'])->name('pedidos_en_entrega');

    // Rutas de Acción de la Tabla (Ver Cotización)
    Route::get('/cotizacion/{id}/ver', [ClienteDashboardController::class, 'viewQuotation'])->name('cotizacion.ver');

    // Chat por cotización (cliente)
    Route::get('/cotizacion/{id}/mensajes', [MensajeCotizacionController::class, 'index'])->name('cotizacion.mensajes.index');
    Route::post('/cotizacion/{id}/mensajes', [MensajeCotizacionController::class, 'store'])->name('cotizacion.mensajes.store');
    Route::post('/cotizacion/{id}/mensajes/leidos', [MensajeCotizacionController::class, 'marcarLeidos'])->name('cotizacion.mensajes.leidos');
    
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