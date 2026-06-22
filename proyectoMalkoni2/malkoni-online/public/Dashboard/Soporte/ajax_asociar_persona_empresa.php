<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (
  empty($_SESSION['usuario']) ||
  $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
  (int)($_SESSION['rol'] ?? 0) !== 3
) {
  echo json_encode(['success'=>false,'error'=>'No autorizado.'], JSON_UNESCAPED_UNICODE);
  exit;
}

require_once __DIR__ . '/../../../vendor/autoload.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

$em = require __DIR__ . '/../../../config/doctrine.php';

$personaId = (int)($_POST['persona_id'] ?? 0);
$empresaId = (int)($_POST['empresa_id'] ?? 0);

if (!$personaId || !$empresaId) {
  echo json_encode(['success'=>false,'error'=>'Faltan datos.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$persona = $em->getRepository(Personas::class)->find($personaId);
$empresa = $em->getRepository(Empresas::class)->find($empresaId);

if (!$persona) { echo json_encode(['success'=>false,'error'=>'Usuario no encontrado.'], JSON_UNESCAPED_UNICODE); exit; }
if (!$empresa) { echo json_encode(['success'=>false,'error'=>'Empresa no encontrada.'], JSON_UNESCAPED_UNICODE); exit; }

// Solo rol=2
if ((int)$persona->getRol() !== 2) {
  echo json_encode(['success'=>false,'error'=>'Solo se puede asociar Operarios (rol=2).'], JSON_UNESCAPED_UNICODE);
  exit;
}

// ===== Validación empresa: estado != 1 y validado = 1 =====
$estado = null;
foreach (['getEstado', 'getEstadoEmpresa', 'getEstadoEmpresas'] as $m) {
  if (method_exists($empresa, $m)) { $estado = $empresa->$m(); break; }
}
$estadoInt = ($estado === null) ? null : (int)$estado;

$validado = null;
foreach (['getValidado', 'isValidado', 'getEmpresaValidada'] as $m) {
  if (method_exists($empresa, $m)) { $validado = $empresa->$m(); break; }
}
$validadoInt = ($validado === null) ? null : (int)(is_bool($validado) ? ($validado ? 1 : 0) : $validado);

// Si no pudimos leer validado, lo consideramos NO permitido (mejor seguro)
if ($validadoInt !== 1) {
  echo json_encode(['success'=>false,'error'=>'No permitido: la empresa no está validada (validado=0).'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Si pudimos leer estado y es 1 => no permitido
if ($estadoInt === 1) {
  echo json_encode(['success'=>false,'error'=>'No permitido: la empresa está en estado=1.'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Evitar duplicado
$ex = $em->getRepository(EmpresasPersonas::class)->findOneBy([
  'empresa' => $empresa,
  'persona' => $persona,
]);

if ($ex) {
  echo json_encode(['success'=>false,'error'=>'Ese usuario ya está asociado a esa empresa.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$ep = new EmpresasPersonas();
$ep->setEmpresa($empresa);
$ep->setPersona($persona);

$em->persist($ep);
$em->flush();

echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
