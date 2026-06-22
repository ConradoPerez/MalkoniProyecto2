<?php
session_start();
header('Content-Type: application/json');

if (
    empty($_SESSION['usuario']) ||
    $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
    $_SESSION['rol'] != 3
) {
    echo json_encode(['success' => false, 'error' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/../../../vendor/autoload.php';
use Entities\Personas;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
$entityManager = require __DIR__ . '/../../../config/doctrine.php';
$repo = $entityManager->getRepository(Personas::class);

try {
    $id        = (int) ($_POST['id'] ?? 0);
    $nuevoMail = trim($_POST['email'] ?? '');
    $nuevoDniS = trim($_POST['dni'] ?? '');
    $nuevoTel  = trim($_POST['telefono'] ?? '');

    if ($id <= 0) {
        throw new Exception('ID inválido.');
    }

    // 1) Verificar email duplicado
    if ($nuevoMail !== '') {
        $exEmail = $repo->findOneBy(['email' => $nuevoMail]);
        if ($exEmail && $exEmail->getId() !== $id) {
            echo json_encode(['success' => false, 'error' => 'El email ya está registrado en otro usuario.']);
            exit;
        }
    }

    // 2) Verificar DNI duplicado (con casteo a int)
    $nuevoDni = ($nuevoDniS !== '') ? (int)$nuevoDniS : null;
    if ($nuevoDni !== null) {
        $exDni = $repo->findOneBy(['dni' => $nuevoDni]);
        if ($exDni && $exDni->getId() !== $id) {
            echo json_encode(['success' => false, 'error' => 'El DNI ya está registrado en otro usuario.']);
            exit;
        }
    }

    // 3) Verificar Teléfono duplicado
    if ($nuevoTel !== '') {
        $exTel = $repo->findOneBy(['num_tel' => $nuevoTel]);
        if ($exTel && $exTel->getId() !== $id) {
            echo json_encode(['success' => false, 'error' => 'El teléfono ya está registrado en otro usuario.']);
            exit;
        }
    }

    // 4) Actualizar datos
    /** @var Personas|null $persona */
    $persona = $repo->find($id);
    if (!$persona) {
        throw new Exception('Usuario no encontrado.');
    }

    $persona
        ->setNombre(trim($_POST['nombre'] ?? ''))
        ->setApellido(trim($_POST['apellido'] ?? ''))
        ->setDni($nuevoDni)
        ->setEmail($nuevoMail !== '' ? $nuevoMail : null)
        ->setNumTel($nuevoTel !== '' ? $nuevoTel : null);

    if (!empty($_POST['password'])) {
        $persona->setPass(password_hash($_POST['password'], PASSWORD_DEFAULT));
    }

    if (isset($_POST['rol']) && $_POST['rol'] !== '') {
        $persona->setRol((int)$_POST['rol']);
    }
    if (isset($_POST['estado']) && $_POST['estado'] !== '') {
        $persona->setEstadoPersona((int)$_POST['estado']);
    }

    $entityManager->flush();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
