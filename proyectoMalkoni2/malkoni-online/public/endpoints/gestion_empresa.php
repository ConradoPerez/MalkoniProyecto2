<?php
// gestion_empresa.php

declare(strict_types=1);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// 1) Bootstrap Doctrine
require_once __DIR__ . '/../../vendor/autoload.php';
$em = require __DIR__ . '/../../config/doctrine.php';

use Entities\Empresas;
use Entities\Direcciones;
use Entities\Provincias;
use Entities\Localidades;
use Entities\Paises; // <<< Importante
use JsonException;

// 2) Leer y decodificar Base64 (admite URL-encoded)
$param = trim((string)($_GET['param'] ?? ''));
if ($param === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Falta parámetro "param" (Base64)']);
    exit;
}
$param = rawurldecode($param);

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
        'details' => $e->getMessage(),
    ]);
    exit;
}
if (!isset($data['data']) || !is_array($data['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido: falta "data"']);
    exit;
}
$in = $data['data'];

// 4) Extraer campos
$cod_cli        = $in['cod_cli']            ?? '';
$razon_social   = $in['razon_social']       ?? '';
$cuit_raw       = $in['cuit']               ?? '';
$codCondIVA     = $in['CodCondIVA']         ?? '';
$telefono       = $in['telefono']           ?? '';
$mail           = $in['mail']               ?? '';
$fecha_alta     = $in['fecha_alta']         ?? '';
$fecha_ultcont  = $in['fecha_ultcontacto']  ?? '';
$domicilio      = $in['domicilio']          ?? '';
$id_provincia   = $in['id_provincia']       ?? '';
$id_localidad   = $in['id_localidad']       ?? '';
$cp             = $in['codigo_postal']      ?? '';
$barrio         = $in['barrio']             ?? '';

// 5) Validar requeridos y normalizar CUIT
if ($cod_cli === '') {
    http_response_code(400);
    echo json_encode(['error' => 'cod_cli es obligatorio']);
    exit;
}
if ($razon_social === '') {
    http_response_code(400);
    echo json_encode(['error' => 'razon_social es obligatorio']);
    exit;
}
if ($codCondIVA === '') {
    http_response_code(400);
    echo json_encode(['error' => 'CodCondIVA es obligatorio']);
    exit;
}
$cuit = preg_replace('/\D/', '', (string)$cuit_raw);
if ($cuit === '' || strlen($cuit) !== 11) {
    http_response_code(400);
    echo json_encode(['error' => 'cuit inválido: debe tener 11 dígitos']);
    exit;
}

try {
    $em->getConnection()->beginTransaction();

    // 6) Upsert en Empresas (por cod_cliente)
    $repoE   = $em->getRepository(Empresas::class);
    $empresa = $repoE->findOneBy(['cod_cliente' => $cod_cli]) ?? new Empresas();

    $empresa->setCodCliente($cod_cli)
            ->setRazonSocial($razon_social)
            ->setCuit($cuit)
            ->setObservacion('')
            ->setCodCondIVA($codCondIVA)
            ->setNumTel($telefono)
            ->setEmail($mail)
            ->setEstado(4); // estado siempre = 1

    if ($fecha_alta)    { $empresa->setFechaAlta(new \DateTime($fecha_alta)); }
    if ($fecha_ultcont) { $empresa->setFechaUltContacto(new \DateTime($fecha_ultcont)); }

    $em->persist($empresa);
    $em->flush();

    // 7) Resolver FK Provincia/Localidad (opcionales)
    $prov = $id_provincia !== ''
        ? $em->getRepository(Provincias::class)->find((int)$id_provincia)
        : null;
    $loc  = $id_localidad !== ''
        ? $em->getRepository(Localidades::class)->find((int)$id_localidad)
        : null;

    // 8) Upsert en Direcciones
    $repoD     = $em->getRepository(Direcciones::class);
    $direccion = $repoD->findOneBy(['empresa' => $empresa])
               ?: (new Direcciones())->setEmpresa($empresa);

    // País fijo = 1 (pasar ENTIDAD, no entero)
    $paisRef = $em->getReference(Paises::class, 1);

    $direccion->setDomicilio($domicilio)
              ->setBarrio($barrio)
              ->setCp($cp)
              ->setPais($paisRef)          // <<< aquí el cambio correcto
              ->setObservaciones('');

    if ($prov instanceof Provincias)  { $direccion->setProvincia($prov); }
    if ($loc  instanceof Localidades) { $direccion->setLocalidad($loc); }

    $em->persist($direccion);
    $em->flush();

    $em->getConnection()->commit();

    echo json_encode([
        'status'       => 'ok',
        'empresa_id'   => $empresa->getId(),
        'direccion_id' => $direccion->getId(),
    ]);

} catch (\Throwable $e) {
    $em->getConnection()->rollBack();
    http_response_code(500);
    echo json_encode([
        'error'   => 'No se pudo guardar',
        'message' => $e->getMessage(),
        'trace'   => $e->getTraceAsString(),
    ]);
}
