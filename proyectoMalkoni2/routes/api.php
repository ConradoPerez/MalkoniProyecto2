<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OPTWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Grupo de rutas para la versión 1 de la API de integración
Route::prefix('v1')->group(function () {
    
    // Endpoint de importación de cotizaciones desde Optimizador de Cortes (OPT)
    Route::post('/cotizaciones/importar', [OPTWebhookController::class, 'importar'])
        ->name('api.v1.cotizaciones.importar');

});
