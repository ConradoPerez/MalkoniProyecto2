<?php
// gestion_cf.php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';
$em = require __DIR__ . '/../../config/doctrine.php';

use Entities\Empresas;
use Entities\Direcciones;
use Entities\Provincias;
use Entities\Localidades;
use Entities\Paises;
use JsonException;

/**
 * Calcula el CUIL a partir de DNI (hasta 8 dígitos) y género ('M' o 'F').
 */
function calcularCuil(string $dni, string $genero): string {
    $dni    = str_pad(preg_replace('/\D/', '', $dni), 8, '0', STR_PAD_LEFT);
    $prefix = strtoupper($genero) === 'M' ? '20' : '27';
    $base   = $prefix . $dni;
    $mult   = [5,4,3,2,7,6,5,4,3,2];
    $sum    = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += (int)$base[$i] * $mult[$i];
    }
    $r = $sum % 11;
    $d = 11 - $r;
    if ($d === 11) {
        $d = 0;
    } elseif ($d === 10) {
        // Ajuste especial
        $prefix = ($genero === 'M') ? '23' : '24';
        $d      = 9;
    }
    return $prefix . $dni . $d;
}

// 1) Obtener y URL-decodificar el Base64
$param = trim((string)($_GET['param'] ?? ''));
if ($param === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Falta parámetro "param" (Base64)']);
    exit;
}
$param = rawurldecode($param);

// 2) Decodificar Base64 (no estricto)
$json = base64_decode($param);
if ($json === false) {
    http_response_code(400);
    echo json_encode(['error' => 'param no es un Base64 válido']);
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
    ]);
    exit;
}
if (!isset($data['data']) || !is_array($data['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta campo "data" en JSON']);
    exit;
}
$in = $data['data'];

// 4) Extraer campos
$cod_cli       = $in['cod_cli']           ?? '';
$nombre        = $in['nombre']            ?? '';
$apellido      = $in['apellido']          ?? '';
$dni_raw       = $in['dni']               ?? '';
$genero_raw    = $in['genero']            ?? '';
$telefono      = $in['telefono']          ?? '';
$mail          = $in['mail']              ?? '';
$fecha_alta    = $in['fecha_alta']        ?? '';
$fecha_ultcont = $in['fecha_ultcontacto'] ?? '';
$domicilio     = $in['domicilio']         ?? '';
$id_provincia  = $in['id_provincia']      ?? '';
$id_localidad  = $in['id_localidad']      ?? '';
$cp            = $in['codigo_postal']     ?? '';
$barrio        = $in['barrio']            ?? '';

// 5) Validaciones
if ($cod_cli === '') {
    http_response_code(400);
    echo json_encode(['error' => 'cod_cli es obligatorio']);
    exit;
}
$g = strtoupper($genero_raw);
if (!preg_match('/^\d{1,8}$/', $dni_raw) || !in_array($g, ['M','F'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'dni (1–8 dígitos) y genero (M o F) son obligatorios']);
    exit;
}

// 6) Calcular CUIL
$cuit = calcularCuil($dni_raw, $g);

// Normalizo DNI para guardar (int)
$dni_int = (int)preg_replace('/\D/', '', (string)$dni_raw);

try {
    $em->getConnection()->beginTransaction();

    // 7) Upsert Empresas
    $repoE   = $em->getRepository(Empresas::class);
    $empresa = $repoE->findOneBy(['cod_cliente' => $cod_cli]) ?? new Empresas();

    $empresa->setCodCliente($cod_cli)
            ->setCuit($cuit)
            ->setDni($dni_int)        // guardamos DNI
            ->setEstado(4)            // estado siempre = 1
            ->setRazonSocial(trim("$nombre $apellido"))
            ->setObservacion('')
            ->setCodCondIVA('CF')
            ->setNumTel($telefono)
            ->setEmail($mail);

    if ($fecha_alta)    { $empresa->setFechaAlta(new \DateTime($fecha_alta)); }
    if ($fecha_ultcont) { $empresa->setFechaUltContacto(new \DateTime($fecha_ultcont)); }

    $em->persist($empresa);
    $em->flush();

    // 8) FK Provincia/Localidad
    $prov = $id_provincia !== ''
        ? $em->getRepository(Provincias::class)->find((int)$id_provincia)
        : null;
    $loc  = $id_localidad !== ''
        ? $em->getRepository(Localidades::class)->find((int)$id_localidad)
        : null;

    // 9) Upsert Direcciones
    $repoD     = $em->getRepository(Direcciones::class);
    $direccion = $repoD->findOneBy(['empresa' => $empresa])
               ?: (new Direcciones())->setEmpresa($empresa);

    // País fijo = 1 -> pasar ENTIDAD, no entero
    $paisRef = $em->getReference(Paises::class, 1);

    $direccion->setDomicilio($domicilio)
              ->setBarrio($barrio)
              ->setCp($cp)
              ->setPais($paisRef)         // <<< aquí el cambio correcto
              ->setObservaciones('');

    if ($prov instanceof Provincias)  { $direccion->setProvincia($prov); }
    if ($loc instanceof Localidades)  { $direccion->setLocalidad($loc); }

    $em->persist($direccion);
    $em->flush();

    $em->getConnection()->commit();

    echo json_encode([
        'status'       => 'ok',
        'empresa_id'   => $empresa->getId(),
        'direccion_id' => $direccion->getId(),
        'cuit'         => $cuit
    ]);
} catch (\Throwable $e) {
    $em->getConnection()->rollBack();
    http_response_code(500);
    echo json_encode([
        'error'   => 'No se pudo guardar',
        'message' => $e->getMessage(),
        'trace'   => $e->getTraceAsString()
    ]);
}
