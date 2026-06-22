<?php
// public/apifact/api.php
// Helper para comunicarse con la API del sistema de facturación (SGA)

declare(strict_types=1);

if (!defined('SGA_BASE_URL')) {
    define('SGA_BASE_URL', 'http://malkonihnos.ddns.net:9000/sga/rest/tep');
    define('SGA_TERMINAL', '100'); // como string
    define('SGA_CODE',     '063f638f975b0731523f0f5dafbe7517'); // solo se usa en /connect
}

/**
 * POST contra la API SGA.
 *
 * $path: '/connect', '/clientes', '/errores', etc.
 * $payload: array PHP -> se encodea a JSON
 *
 * Devuelve ['code' => HTTP_CODE, 'body' => array JSON, 'raw' => string]
 */
function sga_http_post(string $path, array $payload): array
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new \RuntimeException('No se pudo codificar JSON para SGA');
    }

    $url = rtrim(SGA_BASE_URL, '/') . $path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS     => $json,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT        => 30,
    ]);

    // Forzar IP de origen de la terminal 100
    curl_setopt($ch, CURLOPT_INTERFACE, '50.31.177.150');

    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        throw new \RuntimeException('Error cURL SGA: ' . $err);
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException(
            'Respuesta no JSON desde SGA (HTTP ' . $code . '): ' . substr($raw, 0, 500)
        );
    }

    return [
        'code' => $code,
        'body' => $data,
        'raw'  => $raw,
    ];
}

/**
 * Obtiene el TOKEN desde /connect
 *
 * BODY requerido:
 * {
 *   "cred": {
 *     "terminal": "100",
 *     "code": "063f..."
 *   }
 * }
 */
function sga_obtener_token(): string
{
    $payload = [
        'cred' => [
            'terminal' => SGA_TERMINAL,
            'code'     => SGA_CODE,
        ],
    ];

    $resp = sga_http_post('/connect', $payload);

    if (($resp['code'] ?? 0) >= 400) {
        throw new \RuntimeException('Error HTTP al obtener token SGA: ' . $resp['code'] . ' - ' . ($resp['raw'] ?? ''));
    }

    if (empty($resp['body']['data']['token'])) {
        throw new \RuntimeException('Token SGA no presente: ' . json_encode($resp['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    return (string)$resp['body']['data']['token'];
}

/**
 * Inserta/actualiza un cliente en SGA.
 *
 * BODY:
 * {
 *   "cred": {
 *     "terminal": "100",
 *     "token": "XXXX"
 *   },
 *   "data": {
 *     ... campos del cliente ...
 *   }
 * }
 *
 * Devuelve codcli si viene en la respuesta, o null.
 *
 * $debug (opcional, por referencia): te devuelve el resp completo ['code','body','raw']
 * para loggear exactamente lo que respondió SGA.
 */
function syncClienteFacturacion(array $clienteData, ?array &$debug = null): ?string
{
    // 1) Obtener token
    $token = sga_obtener_token();

    // 2) Normalizar codcli (si no viene, lo mandamos como string vacío para alta)
    if (!array_key_exists('codcli', $clienteData) || $clienteData['codcli'] === null) {
        $clienteData['codcli'] = "";
    }

    // 3) Normalizar por pfj (tipo)
    if (isset($clienteData['pfj']) && (int)$clienteData['pfj'] === 1) {
        // Empresa: solo rsocial (no hace falta dni)
        unset($clienteData['apellido'], $clienteData['nombre'], $clienteData['genero']);

        // Si llega dni en null/vacío, lo sacamos (ya no hace falta enviarlo)
        if (!isset($clienteData['dni']) || $clienteData['dni'] === null || $clienteData['dni'] === "") {
            unset($clienteData['dni']);
        }

        if (!isset($clienteData['rsocial'])) {
            $clienteData['rsocial'] = "";
        }

        // Si domicilio viene null y SGA lo exige, podés forzarlo acá (opcional):
        // if (!isset($clienteData['domicilio']) || $clienteData['domicilio'] === null || trim((string)$clienteData['domicilio']) === '') {
        //     $clienteData['domicilio'] = 'S/D';
        // }

    } elseif (isset($clienteData['pfj']) && (int)$clienteData['pfj'] === 2) {
        // Persona física: no hace falta rsocial
        unset($clienteData['rsocial']);

        foreach (['apellido', 'nombre', 'genero'] as $campo) {
            if (!array_key_exists($campo, $clienteData)) {
                $clienteData[$campo] = null;
            }
        }

        if (!array_key_exists('dni', $clienteData)) {
            $clienteData['dni'] = null;
        }

        // cuit no hace falta; si está vacío/null, no mandarlo
        if (isset($clienteData['cuit']) && ($clienteData['cuit'] === "" || $clienteData['cuit'] === null)) {
            unset($clienteData['cuit']);
        }
    }

    // 4) Armar payload final para /clientes (cred = terminal + token)
    $payload = [
        'cred' => [
            'terminal' => SGA_TERMINAL,
            'token'    => $token,
        ],
        'data' => $clienteData,
    ];

    // 5) POST a /clientes
    $resp = sga_http_post('/clientes', $payload);

    // Guardar debug completo para logs si lo piden
    $debug = $resp;

    $body = $resp['body'];
    
    // SGA a veces devuelve HTTP 200 con error adentro
    if (!empty($body['data']['error'])) {
        $det = $body['data']['detalles'] ?? [];
        $msg = $body['data']['error'] . ' | detalles: ' . json_encode($det, JSON_UNESCAPED_UNICODE);
        throw new \RuntimeException('Error en SGA /clientes: ' . $msg);
    }

    $body = $resp['body'] ?? [];

    // 6) Devolver codcli (más tolerante con variantes)
    $candidates = [
        $body['data']['detalles'][0]['codcli'] ?? null,   // formato visto: detalles array
        $body['data']['detalles']['codcli'] ?? null,      // por si detalles no es array
        $body['data']['codcli'] ?? null,
        $body['codcli'] ?? null,
    ];

    foreach ($candidates as $c) {
        if ($c !== null && $c !== '') {
            return (string)$c;
        }
    }

    return null;
}
