<?php
// act_fecha_ult.php — Actualiza fecha_ult_contacto en Empresas por cod_cli
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';
$em = require __DIR__ . '/../../config/doctrine.php';

use Entities\Empresas;

/**
 * Envía respuesta JSON y finaliza.
 */
function json_response(array $payload, int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Normaliza base64 URL-safe (por si usan - _ en lugar de + /).
 */
function b64url_normalize(string $b64): string {
    $b64 = strtr($b64, '-_', '+/');
    $rem = strlen($b64) % 4;
    if ($rem > 0) {
        $b64 .= str_repeat('=', 4 - $rem);
    }
    return $b64;
}

/**
 * Valida fecha exacta en formato YYYY-MM-DD y retorna DateTime si es válida.
 */
function parse_date_ymd(string $ymd): ?\DateTime {
    // Debe ser exactamente YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
        return null;
    }
    $dt = \DateTime::createFromFormat('Y-m-d', $ymd);
    $errors = \DateTime::getLastErrors();
    if ($dt === false || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
        return null;
    }
    // Normaliza a medianoche sin zona (tipo DATE de Doctrine)
    $dt->setTime(0, 0, 0);
    return $dt;
}

try {
    // 1) Obtener el parámetro ?param= (en GET preferentemente; fallback a POST).
    $paramRaw = $_GET['param'] ?? $_POST['param'] ?? null;
    if ($paramRaw === null || $paramRaw === '') {
        json_response([
            'status'  => 'error',
            'message' => 'Parámetro "param" no recibido. Debe ser un JSON en base64 con {"data":{"cod_cli":"...","fecha_ult_contacto":"YYYY-MM-DD"}}'
        ]);
    }

    // 2) Decodificar base64 (soporta URL-safe).
    $paramDecoded = base64_decode(b64url_normalize($paramRaw), true);
    if ($paramDecoded === false) {
        json_response([
            'status'  => 'error',
            'message' => 'El parámetro "param" no es un base64 válido.'
        ]);
    }

    // 3) Decodificar JSON.
    $payload = json_decode($paramDecoded, true, 512, 0);
    if (!is_array($payload)) {
        json_response([
            'status'  => 'error',
            'message' => 'El contenido decodificado no es un JSON válido.'
        ]);
    }

    // 4) Extraer y validar campos requeridos.
    $data = $payload['data'] ?? null;
    if (!is_array($data)) {
        json_response([
            'status'  => 'error',
            'message' => 'Estructura inválida. Se esperaba un objeto "data" con "cod_cli" y "fecha_ult_contacto".'
        ]);
    }

    $codCli  = isset($data['cod_cli']) ? trim((string)$data['cod_cli']) : '';
    $fechaIn = isset($data['fecha_ult_contacto']) ? trim((string)$data['fecha_ult_contacto']) : '';

    if ($codCli === '' || $fechaIn === '') {
        json_response([
            'status'  => 'error',
            'message' => 'Faltan campos obligatorios en "data": cod_cli y/o fecha_ult_contacto.'
        ]);
    }

    $dt = parse_date_ymd($fechaIn);
    if ($dt === null) {
        json_response([
            'status'  => 'error',
            'message' => 'Formato de fecha inválido. Use "YYYY-MM-DD".'
        ]);
    }

    // 5) Buscar empresa por cod_cliente.
    /** @var Empresas|null $empresa */
    $empresa = $em->getRepository(Empresas::class)->findOneBy(['cod_cliente' => $codCli]);

    if (!$empresa) {
        // Cliente inexistente: responder según tu doc.
        json_response([
            'status'  => 'error',
            'message' => 'No se pudo actualizar la fecha: no existe el cliente con cod_cli "' . $codCli . '" en la base de datos.',
            'cod_cli' => $codCli
        ]);
    }

    // 6) Actualizar fecha_ult_contacto y persistir.
    $empresa->setFechaUltContacto($dt);
    $em->flush();

    // 7) Respuesta OK.
    json_response([
        'status'              => 'ok',
        'empresa_id'          => $empresa->getId(),
        'fecha_ult_contacto'  => $dt->format('Y-m-d')
    ]);
}
catch (\Throwable $e) {
    // Errores inesperados.
    json_response([
        'status'  => 'error',
        'message' => 'Error interno al procesar la solicitud.',
        'detail'  => $e->getMessage() // Si prefieres, omite "detail" en producción.
    ], 500);
}
