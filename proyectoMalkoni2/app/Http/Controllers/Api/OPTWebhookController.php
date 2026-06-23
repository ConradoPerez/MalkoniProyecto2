<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportarCotizacionRequest;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\LogIntegracion;
use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class OPTWebhookController extends Controller
{
    /**
     * Importar una cotización desde el Optimizador de Cortes (OPT).
     *
     * Este endpoint recibe datos de un pedido procesado en Lepton OPT,
     * sincroniza información de persona y empresa, y crea una nueva cotización
     * en el sistema proyectoMalkoni2.
     *
     * @param ImportarCotizacionRequest $request
     * @return JsonResponse
     */
    public function importar(ImportarCotizacionRequest $request): JsonResponse
    {
        // Generar request_id único para trazabilidad
        $requestId = 'req_' . Str::random(16);
        $log = null;

        // Extraer datos validados del request
        $payload = $request->validated();

        // Calcular clave de idempotencia
        $idempotencyKey = sprintf(
            '%s-%d-%d',
            $payload['pedido_id'] ?? 'sync-only',
            $payload['persona_external_id'],
            $payload['empresa_activa_external_id']
        );

        try {
            // ========================================
            // 1. VALIDAR TOKEN DE INTEGRACIÓN
            // ========================================
            $tokenRecibido = $request->header('X-Integration-Token');
            $tokenEsperado = config('app.integration_token');

            if (empty($tokenRecibido) || $tokenRecibido !== $tokenEsperado) {
                // Registrar intento de acceso no autorizado
                LogIntegracion::create([
                    'source_system' => 'malkoni_online',
                    'metodo' => 'POST',
                    'endpoint' => '/api/v1/cotizaciones/importar',
                    'request_id' => $requestId,
                    'idempotency_key' => $idempotencyKey,
                    'http_status' => 401,
                    'status' => 'error',
                    'request_payload' => $payload,
                    'response_payload' => [
                        'success' => false,
                        'message' => 'Token de integración inválido o ausente.',
                        'error' => 'UNAUTHORIZED',
                    ],
                    'error_message' => 'Token de integración no coincide o está ausente',
                    'processed_at' => now(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Token de integración inválido o ausente.',
                    'error' => 'UNAUTHORIZED',
                    'hint' => 'Verifique el header X-Integration-Token y asegúrese de que coincida con el token configurado en el servidor.',
                ], 401);
            }

            // ========================================
            // 2. REGISTRAR AUDITORÍA INICIAL
            // ========================================
            $log = LogIntegracion::create([
                'source_system' => 'malkoni_online',
                'metodo' => 'POST',
                'endpoint' => '/api/v1/cotizaciones/importar',
                'request_id' => $requestId,
                'idempotency_key' => $idempotencyKey,
                'http_status' => null, // Se actualizará al final
                'status' => 'processing',
                'request_payload' => $payload,
                'response_payload' => null, // Se actualizará al final
                'error_message' => null,
                'processed_at' => null, // Se actualizará al final
            ]);

            // ========================================
            // 3. VERIFICACIÓN DE IDEMPOTENCIA PREVIA
            // ========================================
            $cotizacionExistente = null;

            if (!empty($payload['pedido_id'])) {
                $cotizacionExistente = $this->findCotizacionByPedido((int) $payload['pedido_id']);

                if ($cotizacionExistente && $this->hasMatchingSnapshots($cotizacionExistente, $payload)) {
                    $responseData = $this->buildSuccessResponse(
                        cotizacion: $cotizacionExistente,
                        payload: $payload,
                        message: 'Cotización previamente importada. Redirigiendo a la cotización existente.'
                    );

                    $log->update([
                        'http_status' => 200,
                        'status' => 'success',
                        'response_payload' => $responseData,
                        'processed_at' => now(),
                    ]);

                    return response()->json($responseData, 200);
                }
            }

            // ========================================
            // 4. PERSISTENCIA REAL EN TRANSACCIÓN
            // ========================================
            $transactionResult = DB::transaction(function () use ($payload, $request, $cotizacionExistente) {
                $empresa = Empresa::updateOrCreate(
                    ['id_empresa_externo' => $payload['empresa_activa_external_id']],
                    [
                        'nombre' => $payload['empresa_razon_social'],
                        'razon_social' => $payload['empresa_razon_social'],
                        'cuit' => $payload['empresa_cuit'] ?? null,
                        'cod_cond_iva' => $payload['empresa_iva'] ?? null,
                        'last_synced_at' => now(),
                        'sync_status' => 'success',
                        'sync_error' => null,
                    ]
                );

                $persona = Persona::updateOrCreate(
                    ['id_persona_externo' => $payload['persona_external_id']],
                    [
                        'id_empresa' => $empresa->id_empresa,
                        'token_opt' => $payload['token_opt'],
                        'empresa_activa_externa_id' => $payload['empresa_activa_external_id'],
                        'nombre' => $payload['persona_nombre'],
                        'apellido' => $payload['persona_apellido'] ?? null,
                        'email' => $payload['persona_email'] ?? null,
                        'dni' => $payload['persona_dni'] ?? null,
                        'genero' => $payload['persona_genero'] ?? null,
                        'num_tel' => $payload['persona_tel'] ?? null,
                        'last_synced_at' => now(),
                        'sync_status' => 'success',
                        'sync_error' => null,
                    ]
                );

                $persona->empresas()->syncWithoutDetaching([
                    $empresa->id_empresa => [
                        'persona_external_id' => $payload['persona_external_id'],
                        'empresa_external_id' => $payload['empresa_activa_external_id'],
                        'estado' => 'activa',
                        'last_synced_at' => now(),
                    ],
                ]);

                if (empty($payload['pedido_id'])) {
                    return [
                        'cotizacion' => null,
                        'persona_id' => $persona->id_persona,
                        'empresa_id' => $empresa->id_empresa,
                        'http_status' => 200,
                        'message' => 'Identidad sincronizada exitosamente. Redirigiendo al panel de cotizaciones.',
                    ];
                }

                $cotizacionPorPedido = Cotizacion::query()
                    ->where(function ($query) use ($payload) {
                        $query->where('pedido_opt_id', $payload['pedido_id'])
                            ->orWhere('numero', $payload['pedido_id']);
                    })
                    ->lockForUpdate()
                    ->first();

                if ($cotizacionExistente && !$cotizacionPorPedido) {
                    $cotizacionPorPedido = Cotizacion::query()
                        ->whereKey($cotizacionExistente->getKey())
                        ->lockForUpdate()
                        ->first();
                }

                if ($cotizacionPorPedido) {
                    if ($this->shouldAdoptCotizacion($cotizacionPorPedido, $payload) || $this->hasMatchingSnapshots($cotizacionPorPedido, $payload)) {
                        $cotizacionPorPedido->fill([
                            'titulo' => 'Pedido OPT #' . $payload['pedido_id'],
                            'pedido_opt_id' => $payload['pedido_id'],
                            'referencia_externa' => 'OPT-' . $payload['pedido_id'],
                            'pdf_url' => $payload['pdf_url'],
                            'id_personas' => $persona->id_persona,
                            'id_empresas' => $empresa->id_empresa,
                            'id_empleados' => null,
                            'persona_external_id_snapshot' => $payload['persona_external_id'],
                            'empresa_external_id_snapshot' => $payload['empresa_activa_external_id'],
                            'origen_sistema' => 'malkoni_online',
                            'integration_status' => 'success',
                            'integration_error' => null,
                            'payload_origen' => $request->all(),
                            'imported_at' => now(),
                        ]);

                        if (!$cotizacionPorPedido->fyh) {
                            $cotizacionPorPedido->fyh = now();
                        }

                        if ($cotizacionPorPedido->precio_total === null) {
                            $cotizacionPorPedido->precio_total = 0;
                        }

                        $cotizacionPorPedido->save();

                        return [
                            'cotizacion' => $cotizacionPorPedido,
                            'http_status' => 200,
                            'message' => 'Cotización previamente importada. Redirigiendo a la cotización existente.',
                        ];
                    }

                    return [
                        'cotizacion' => $cotizacionPorPedido,
                        'http_status' => 200,
                        'message' => 'Cotización previamente importada. Redirigiendo a la cotización existente.',
                    ];
                }

                // numero permanece numérico por el esquema actual; referencia_externa guarda OPT-{pedido_id}
                $cotizacionNueva = Cotizacion::create([
                    'titulo' => 'Pedido OPT #' . $payload['pedido_id'],
                    'numero' => $payload['pedido_id'],
                    'pedido_opt_id' => $payload['pedido_id'],
                    'referencia_externa' => 'OPT-' . $payload['pedido_id'],
                    'pdf_url' => $payload['pdf_url'],
                    'id_personas' => $persona->id_persona,
                    'id_empresas' => $empresa->id_empresa,
                    'id_empleados' => null,
                    'fyh' => now(),
                    'precio_total' => 0,
                    'persona_external_id_snapshot' => $payload['persona_external_id'],
                    'empresa_external_id_snapshot' => $payload['empresa_activa_external_id'],
                    'origen_sistema' => 'malkoni_online',
                    'integration_status' => 'success',
                    'payload_origen' => $request->all(),
                    'imported_at' => now(),
                ]);

                return [
                    'cotizacion' => $cotizacionNueva,
                    'http_status' => 201,
                    'message' => 'Cotización importada exitosamente desde Optimizador de Cortes.',
                ];
            });

            $cotizacion = $transactionResult['cotizacion'];
            $httpStatus = $transactionResult['http_status'];
            $responseData = $cotizacion
                ? $this->buildSuccessResponse(
                    cotizacion: $cotizacion,
                    payload: $payload,
                    message: $transactionResult['message']
                )
                : $this->buildIdentitySyncResponse(
                    personaId: $transactionResult['persona_id'],
                    empresaId: $transactionResult['empresa_id'],
                    message: $transactionResult['message']
                );

            // ========================================
            // 5. ACTUALIZAR LOG DE AUDITORÍA (ÉXITO)
            // ========================================
            $log->update([
                'http_status' => $httpStatus,
                'status' => 'success',
                'response_payload' => $responseData,
                'processed_at' => now(),
            ]);

            // ========================================
            // 6. RETORNAR RESPUESTA
            // ========================================
            return response()->json($responseData, $httpStatus);

        } catch (Exception $e) {
            Log::error('Error al importar cotización OPT', [
                'request_id' => $requestId,
                'idempotency_key' => $idempotencyKey,
                'error' => $e->getMessage(),
            ]);

            // ========================================
            // MANEJO DE ERRORES NO CONTROLADOS
            // ========================================
            $errorResponse = [
                'success' => false,
                'message' => 'Ocurrió un error interno al procesar la solicitud. Por favor, contacte al equipo de soporte.',
                'error' => 'INTERNAL_SERVER_ERROR',
                'request_id' => $requestId,
            ];

            // Actualizar log con el error (si fue creado)
            if ($log) {
                $log->update([
                    'http_status' => 500,
                    'status' => 'error',
                    'response_payload' => $errorResponse,
                    'error_message' => $e->getMessage(),
                    'processed_at' => now(),
                ]);
            } else {
                // Si el log no se creó, registrarlo ahora
                LogIntegracion::create([
                    'source_system' => 'malkoni_online',
                    'metodo' => 'POST',
                    'endpoint' => '/api/v1/cotizaciones/importar',
                    'request_id' => $requestId,
                    'idempotency_key' => $idempotencyKey,
                    'http_status' => 500,
                    'status' => 'error',
                    'request_payload' => $payload ?? [],
                    'response_payload' => $errorResponse,
                    'error_message' => $e->getMessage(),
                    'processed_at' => now(),
                ]);
            }

            return response()->json($errorResponse, 500);
        }
    }

    private function buildSuccessResponse(Cotizacion $cotizacion, array $payload, string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'cotizacion_id' => $cotizacion->id,
                'numero' => 'OPT-' . $payload['pedido_id'],
                'estado' => $cotizacion->estado ?? 'Nuevo',
                'persona_id' => $cotizacion->id_personas,
                'empresa_id' => $cotizacion->id_empresas,
                'pedido_opt_id' => $cotizacion->pedido_opt_id,
                'pdf_url' => $cotizacion->pdf_url,
                'created_at' => optional($cotizacion->created_at)?->toIso8601String(),
                'updated_at' => optional($cotizacion->updated_at)?->toIso8601String(),
            ],
            'redirect_url' => route('auth.sso_bridge', [
                'persona_id' => $cotizacion->id_personas,
                'cotizacion_id' => $cotizacion->id,
            ]),
        ];
    }

    private function buildIdentitySyncResponse(int $personaId, int $empresaId, string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'persona_id' => $personaId,
                'empresa_id' => $empresaId,
                'synced_only' => true,
            ],
            'redirect_url' => route('auth.sso_bridge', [
                'persona_id' => $personaId,
            ]),
        ];
    }

    private function findCotizacionByPedido(int $pedidoId): ?Cotizacion
    {
        return Cotizacion::query()
            ->where('pedido_opt_id', $pedidoId)
            ->orWhere('numero', $pedidoId)
            ->first();
    }

    private function hasMatchingSnapshots(Cotizacion $cotizacion, array $payload): bool
    {
        return (int) $cotizacion->persona_external_id_snapshot === (int) $payload['persona_external_id']
            && (int) $cotizacion->empresa_external_id_snapshot === (int) $payload['empresa_activa_external_id'];
    }

    private function shouldAdoptCotizacion(Cotizacion $cotizacion, array $payload): bool
    {
        $personaSnapshot = $cotizacion->persona_external_id_snapshot;
        $empresaSnapshot = $cotizacion->empresa_external_id_snapshot;

        if ($personaSnapshot === null || $empresaSnapshot === null) {
            return true;
        }

        return (int) $personaSnapshot === (int) $payload['persona_external_id']
            && (int) $empresaSnapshot === (int) $payload['empresa_activa_external_id'];
    }
}
