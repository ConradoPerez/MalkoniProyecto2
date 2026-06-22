<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

if (
  empty($_SESSION['usuario']) ||
  $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
  (int)($_SESSION['rol'] ?? 0) !== 3
) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
  exit;
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../../config/doctrine.php';

use Entities\Empresas;
use Entities\Personas;
use Entities\EmpresasPersonas;

function jerr(string $msg, int $code = 400): void {
  http_response_code($code);
  echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
  $empresaId = (int)($_GET['empresa_id'] ?? 0);
  if ($empresaId <= 0) jerr('Falta empresa_id');

  $empresa = $entityManager->getRepository(Empresas::class)->find($empresaId);
  if (!$empresa) jerr('Empresa no encontrada', 404);

  // Personas asociadas a esta empresa
  $personas = $entityManager->createQueryBuilder()
      ->select('DISTINCT p')
      ->from(Personas::class, 'p')
      ->leftJoin(EmpresasPersonas::class, 'ep', 'WITH', 'ep.persona = p AND ep.empresa = :emp')
      ->where('ep.id IS NOT NULL OR p.empresa = :emp')
      ->setParameter('emp', $empresa)
      ->getQuery()
      ->getResult();

  $users = [];
  foreach ($personas as $p) {
    $personaId = (int)$p->getId();

    // cuántas empresas tiene esa persona
    $count = (int)$entityManager->createQueryBuilder()
      ->select('COUNT(ep2.id)')
      ->from(EmpresasPersonas::class, 'ep2')
      ->where('ep2.persona = :per')
      ->setParameter('per', $p)
      ->getQuery()
      ->getSingleScalarResult();

    $accion = ($count <= 1) ? 'eliminar' : 'desasociar';

    $users[] = [
      'id' => $personaId,
      'nombre' => (string)($p->getNombre() ?? ''),
      'apellido' => (string)($p->getApellido() ?? ''),
      'email' => (string)($p->getEmail() ?? ''),
      'telefono' => (string)($p->getNumTel() ?? ''),
      'empresas_count' => $count,
      'accion' => $accion,
    ];
  }

  echo json_encode([
    'success' => true,
    'empresa' => [
      'id' => (int)$empresa->getId(),
      'razon_social' => (string)($empresa->getRazonSocial() ?? ''),
      'cuit' => (string)($empresa->getCuit() ?? ''),
      'email' => (string)($empresa->getEmail() ?? ''),
      'telefono' => (string)($empresa->getNumTel() ?? ''),
    ],
    'users' => $users
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($method === 'POST') {
  $empresaId = (int)($_POST['empresa_id'] ?? 0);
  if ($empresaId <= 0) jerr('Falta empresa_id');

  $empresa = $entityManager->getRepository(Empresas::class)->find($empresaId);
  if (!$empresa) jerr('Empresa no encontrada', 404);

  $entityManager->beginTransaction();
  try {
    // Traigo relaciones empresa-persona
    $rels = $entityManager->getRepository(EmpresasPersonas::class)->findBy(['empresa' => $empresa]);

    // Primero: procesar personas (desasociar o eliminar)
    foreach ($rels as $ep) {
      $persona = $ep->getPersona();
      if (!$persona) {
        $entityManager->remove($ep);
        continue;
      }

      // cuántas empresas tiene esa persona
      $count = (int)$entityManager->createQueryBuilder()
        ->select('COUNT(ep2.id)')
        ->from(EmpresasPersonas::class, 'ep2')
        ->where('ep2.persona = :per')
        ->setParameter('per', $persona)
        ->getQuery()
        ->getSingleScalarResult();

      // siempre removemos la relación con esta empresa
      $entityManager->remove($ep);
      $entityManager->flush(); // para que al contar/eliminar quede consistente si hay constraints

      // si sólo estaba en esta empresa -> eliminar persona
      if ($count <= 1) {
        // por si hay otras relaciones, podés necesitar limpiar antes (según tu modelo)
        $entityManager->remove($persona);
        $entityManager->flush();
      }
    }

    // Eliminar direcciones asociadas (evita FK)
    if (method_exists($empresa, 'getDirecciones')) {
      foreach ($empresa->getDirecciones() as $dir) {
        $entityManager->remove($dir);
      }
    }

    // Finalmente eliminar empresa
    $entityManager->remove($empresa);

    $entityManager->flush();
    $entityManager->commit();

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;

  } catch (\Throwable $e) {
    $entityManager->rollback();
    jerr('Error al eliminar: ' . $e->getMessage(), 500);
  }
}

jerr('Método no permitido', 405);
