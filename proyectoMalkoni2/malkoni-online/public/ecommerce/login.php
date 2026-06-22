<?php
// public/ecommerce/login.php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// =========================
// CORS
// =========================
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'https://deherrajes.com',
    'https://www.deherrajes.com',
    'http://localhost:5173', // opcional, solo si aún lo usan en dev
];

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Max-Age: 86400');
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
$em = require __DIR__ . '/../../config/doctrine.php';

use Entities\Personas;
use Entities\Direcciones;
use Entities\Empresas;
use Entities\EmpresasPersonas;
use JsonException;


// 1) Obtener y URL-decodificar el Base64
$param = trim((string)($_GET['param'] ?? ''));
if ($param === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Falta parámetro "param" (Base64)'], JSON_UNESCAPED_UNICODE);
    exit;
}
$param = rawurldecode($param);

// 2) Decodificar Base64 (no estricto)
$json = base64_decode($param);
if ($json === false) {
    http_response_code(400);
    echo json_encode(['error' => 'param no es un Base64 válido'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3) Parsear JSON con excepción
try {
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode([
        'error'   => 'JSON inválido o payload malformado',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4) Estructura esperada: { "data": { "email": "...", "password": "..." } }
if (!isset($data['data']) || !is_array($data['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta campo "data" en JSON'], JSON_UNESCAPED_UNICODE);
    exit;
}

$in = $data['data'];

// 5) Extraer campos
$email = strtolower(trim((string)($in['email'] ?? '')));
$pass  = (string)($in['password'] ?? '');

// 6) Validaciones
if ($email === '' || $pass === '') {
    http_response_code(400);
    echo json_encode(['error' => 'email y password son obligatorios'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'email inválido'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    /** @var Personas|null $persona */
    $persona = $em->getRepository(Personas::class)->findOneBy(['email' => $email]);

    // No revelar si existe o no
    if (!$persona) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Estado activo
    if ((int)$persona->getEstadoPersona() !== 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Usuario inactivo'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Password hash
    $hash = (string)$persona->getPass();
    if ($hash === '' || !password_verify($pass, $hash)) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Sesión
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['persona_id'] = $persona->getId();

    // Helper para obtener nombre sin asumir getters exactos
    $safeName = function ($obj): ?string {
        if (!$obj) return null;
        foreach (['getNombre', 'getName', 'getDescripcion', '__toString'] as $m) {
            if (method_exists($obj, $m)) {
                $val = $obj->$m();
                return $val !== null ? (string)$val : null;
            }
        }
        return null;
    };

    /**
     * Arma el payload de empresa (misma estructura pero con dirección aplanada)
     */
    $buildEmpresaPayload = function (Empresas $empresa) use ($em, $safeName): array {
        /** @var Direcciones|null $direccion */
        $direccion = $em->getRepository(Direcciones::class)->findOneBy(
            ['empresa' => $empresa],
            ['id' => 'DESC']
        );

        $condicionIva = $empresa->getCodCondIVA();

        $pfj = null;
        if ($condicionIva === 'CF') {
            $pfj = 2;
        } elseif (in_array($condicionIva, ['RI', 'EX', 'RN', 'MT'], true)) {
            $pfj = 1;
        }

        return [
            'id' => $empresa->getId(),
            'razon_social' => $empresa->getRazonSocial(),

            // 👇 dirección aplanada (SIN "direccion")
            'domicilio' => $direccion ? $direccion->getDomicilio() : null,
            'barrio'    => $direccion ? $direccion->getBarrio() : null,
            'cp'        => $direccion ? $direccion->getCp() : null,
            'provincia' => $direccion ? $safeName($direccion->getProvincia()) : null,
            'localidad' => $direccion ? $safeName($direccion->getLocalidad()) : null,
            'pais'      => $direccion ? $safeName($direccion->getPais()) : null,

            'cuit' => $empresa->getCuit(),
            'condicion_iva' => $condicionIva,
            'pfj' => $pfj
        ];
    };

    // =========================================================
    // Empresa principal (no duplicar dentro de asociadas)
    // =========================================================
    $empresaPrincipal = $persona->getEmpresa();
    if (!$empresaPrincipal instanceof Empresas) {
        http_response_code(403);
        echo json_encode(['error' => 'Usuario sin empresa principal'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $empresaPrincipalId = (int)$empresaPrincipal->getId();

    // =========================================================
    // Empresas asociadas secundarias (intermedia estado=1)
    // =========================================================
    $empresasAsociadasMap = [];

    $vinculos = $em->getRepository(EmpresasPersonas::class)->findBy([
        'persona' => $persona,
        'estado'  => 1
    ]);

    foreach ($vinculos as $v) {
        if (!is_object($v) || !method_exists($v, 'getEmpresa')) continue;
        $e = $v->getEmpresa();
        if ($e instanceof Empresas) {
            $eid = (int)$e->getId();
            if ($eid !== $empresaPrincipalId) { // NO repetir principal
                $empresasAsociadasMap[$eid] = $e;
            }
        }
    }

    // =========================================================
    // Determinar empresa activa (persistida si existe y es válida/asociada)
    // =========================================================
    $empresaActivaId = $empresaPrincipalId; // fallback siempre a principal

    if ((int)$persona->getRol() === 2 && method_exists($persona, 'getEmpresaActiva')) {
        $ea = $persona->getEmpresaActiva();
        if ($ea instanceof Empresas && $ea->isValidado()) {
            $eaId = (int)$ea->getId();

            // válida si es principal o está en asociadas
            if ($eaId === $empresaPrincipalId || isset($empresasAsociadasMap[$eaId])) {
                $empresaActivaId = $eaId;
            }
        }
    }

    // Mantener empresa activa en sesión
    $_SESSION['empresa_id'] = $empresaActivaId;

    // =========================================================
    // Construir payloads finales
    // =========================================================

    // Empresa principal con flag activa
    $empresaPrincipalPayload = array_merge(
        $buildEmpresaPayload($empresaPrincipal),
        [
            'activa' => ((int)$empresaActivaId === (int)$empresaPrincipalId)
        ]
    );

    // Empresas asociadas con flag activa + estado_asociacion
    $empresasAsociadas = [];
    foreach ($empresasAsociadasMap as $eid => $e) {
        $empresasAsociadas[] = array_merge(
            $buildEmpresaPayload($e),
            [
                'activa' => ((int)$eid === (int)$empresaActivaId),
                'estado_asociacion' => 1
            ]
        );
    }

    // Orden por razón social
    usort($empresasAsociadas, function (array $a, array $b) {
        return strcmp((string)($a['razon_social'] ?? ''), (string)($b['razon_social'] ?? ''));
    });

    // =========================================================
    // Respuesta final
    // =========================================================
    echo json_encode([
        'status' => 'ok',
        'token'  => session_id(),
        'user'   => [
            'apellido'          => $persona->getApellido(),
            'nombre'            => $persona->getNombre(),
            'dni'               => $persona->getDni(),
            'genero'            => $persona->getGenero(),
            'rol'               => (int)$persona->getRol(),
            'email'             => $persona->getEmail(),
            'telefono'          => $persona->getNumTel(),
            'empresa_id'        => $empresaPrincipalId,
            'empresa_activa_id' => $empresaActivaId
        ],
        'empresa_principal'  => $empresaPrincipalPayload,
        'empresas_asociadas' => $empresasAsociadas
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'Error interno',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
