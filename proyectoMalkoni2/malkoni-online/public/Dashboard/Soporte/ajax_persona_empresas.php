<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../../config/doctrine.php';

use Entities\Personas;

try {
    if (
        empty($_SESSION['usuario']) ||
        $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
        (int)($_SESSION['rol'] ?? 0) !== 3
    ) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $personaId = (int)($_GET['persona_id'] ?? 0);
    if ($personaId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'persona_id inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $persona = $entityManager->find(Personas::class, $personaId);
    if (!$persona) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Persona no encontrada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $rows = $entityManager->createQueryBuilder()
        ->select('
            e.id AS id,
            e.razon_social AS razon_social,
            e.cuit AS cuit,
            e.email AS email,
            e.num_tel AS telefono,
            e.cod_cliente AS cod_cliente,
            e.validado AS validado
        ')
        ->from('Entities\Empresas', 'e')
        ->innerJoin('Entities\EmpresasPersonas', 'ep', 'WITH', 'ep.empresa = e')
        ->where('ep.persona = :p')
        ->setParameter('p', $persona)
        ->orderBy('e.razon_social', 'ASC')
        ->getQuery()
        ->getArrayResult();

    // 🔥 Blindaje: siempre devolver 1/0 (no true/false)
    foreach ($rows as &$r) {
        $r['validado'] = (int)($r['validado'] ?? 0);
    }
    unset($r);

    echo json_encode(['success' => true, 'items' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
