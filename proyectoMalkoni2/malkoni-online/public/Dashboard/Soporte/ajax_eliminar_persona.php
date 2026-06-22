<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../../config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

try {
    // S¨®lo soporte
    if (
        empty($_SESSION['usuario']) ||
        $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
        (int)($_SESSION['rol'] ?? 0) !== 3
    ) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $personaId = (int)($_POST['id'] ?? 0);
    $empresaId = (int)($_POST['empresa_id'] ?? 0);

    if ($personaId <= 0 || $empresaId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Par¨¢metros inv¨¢lidos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $persona = $entityManager->find(Personas::class, $personaId);
    $empresa = $entityManager->find(Empresas::class, $empresaId);

    if (!$persona || !$empresa) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Persona o empresa no encontrada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 1) eliminar v¨ªnculo en tabla intermedia
    $repoEP = $entityManager->getRepository(EmpresasPersonas::class);
    $ep = $repoEP->findOneBy(['persona' => $persona, 'empresa' => $empresa]);

    if ($ep) {
        $entityManager->remove($ep);
    }

    // 2) compatibilidad con modelo viejo Personas->empresa:
    // si el usuario "apunta" a esta empresa, lo cortamos, pero NO borramos Personas
    if (method_exists($persona, 'getEmpresa') && method_exists($persona, 'setEmpresa')) {
        $empOld = $persona->getEmpresa();
        if ($empOld && (int)$empOld->getId() === $empresaId) {
            $persona->setEmpresa(null);
        }
    }

    $entityManager->flush();

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
