<?php
// existecli.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// Método a invocar en la API remota
$method = 'existecli';

// 1) Leer parámetros
if (isset($_GET['mail'])) {
    $mail = trim($_GET['mail']);
    if ($mail === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro "mail" vacío']);
        exit;
    }
    $payload = ['data' => ['mail' => $mail]];
}
elseif (isset($_GET['cuit'])) {
    $cuit = trim($_GET['cuit']);
    if ($cuit === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro "cuit" vacío']);
        exit;
    }
    $payload = ['data' => ['cuit' => $cuit]];
}
else {
    http_response_code(400);
    echo json_encode(['error' => 'Debes enviar "mail" o "cuit" como parámetro']);
    exit;
}

// 2) Construir URL remota usando HTTP en el puerto 443
$json      = json_encode($payload, JSON_UNESCAPED_SLASHES);
$base64    = base64_encode($json);
$param     = rawurlencode($base64);
$remoteUrl = "http://malkonihnos.ddns.net:443/sga/rest/tep/{$method}/{$param}";

// 3) Ejecutar cURL
$ch = curl_init($remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,    15);
curl_setopt($ch, CURLOPT_TIMEOUT,           30);

// **Forzar uso de la IP 50.31.177.150 como origen de la conexión**
curl_setopt($ch, CURLOPT_INTERFACE, '50.31.177.150');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    echo json_encode([
        'error'   => 'Error en la petición a servidor remoto',
        'details' => $err
    ]);
    exit;
}

curl_close($ch);

// 4) Detectar HTML de error
if (stripos($response, '<html') !== false) {
    http_response_code(502);
    $plain = preg_replace('/<[^>]+>/', ' ', $response);
    echo json_encode([
        'error'   => 'Error interno en el servidor remoto',
        'details' => trim($plain)
    ]);
    exit;
}

// 5) Decodificar JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code($httpCode >= 400 ? $httpCode : 200);
    echo json_encode([
        'error'   => 'Respuesta no JSON desde servidor remoto',
        'details' => $response
    ]);
    exit;
}

// 6) Devolver JSON al cliente
http_response_code($httpCode >= 400 ? $httpCode : 200);
echo json_encode($data, JSON_UNESCAPED_SLASHES);
