<?php
// eliminar_cf.php

declare(strict_types=1);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// 1) Bootstrap Doctrine
require_once __DIR__ . '/../../vendor/autoload.php';
$em = require __DIR__ . '/../../config/doctrine.php';

use Entities\Empresas;

// 2) Leer y decodificar Base64
$param = trim((string)($_REQUEST['param'] ?? ''));
if ($param === '') {
    http_response_code(400);
    echo json_encode(['error'=>'Falta parámetro "param" (Base64)']);
    exit;
}
$json = base64_decode($param);
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($data['data']['cod_cli'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Payload inválido o falta cod_cli','details'=>json_last_error_msg()]);
    exit;
}
$cod_cli = (string)$data['data']['cod_cli'];

// 3) Buscar empresa CF
$repo = $em->getRepository(Empresas::class);
$empresa = $repo->findOneBy(['cod_cliente'=>$cod_cli]);
if (!$empresa) {
    http_response_code(404);
    echo json_encode(['error'=>"No existe empresa con cod_cli={$cod_cli}"]);
    exit;
}

// 4) Marcar baja lógica
try {
    $empresa->setBaja(true);
    $em->persist($empresa);
    $em->flush();

    echo json_encode([
      'status'     => 'ok',
      'empresa_id' => $empresa->getId(),
      'baja'       => true
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
      'error'=>'No se pudo marcar baja',
      'message'=>$e->getMessage()
    ]);
}
