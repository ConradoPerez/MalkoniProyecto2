<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

// Si no hay sesión válida → login real (sube un nivel)
if (empty($_SESSION['usuario']) || empty($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

$empresaId = (int)($_GET['empresa_id'] ?? 0);
if ($empresaId <= 0) {
    header('Location: empresas_asociadas.php');
    exit;
}

/** @var Personas|null $persona */
$persona = $entityManager->find(Personas::class, (int)$_SESSION['id']);
if (!$persona) {
    header('Location: ../login.php');
    exit;
}

// Solo usuarios comunes
if ((int)$persona->getRol() !== 2) {
    header('Location: dashboard.php');
    exit;
}

// Si ya está activa, no hacemos nada (mejor UX)
if ((int)($_SESSION['empresa_id'] ?? 0) === $empresaId) {
    $_SESSION['successMensaje'] = 'Ya estás usando esa empresa.';
    header('Location: empresas_asociadas.php');
    exit;
}

// Empresa existe + validada
/** @var Empresas|null $empresa */
$empresa = $entityManager->getRepository(Empresas::class)->find($empresaId);
if (!$empresa || !$empresa->isValidado()) {
    $_SESSION['errorMensaje'] = 'La empresa seleccionada no es válida.';
    header('Location: empresas_asociadas.php');
    exit;
}

// Chequear que el usuario esté asociado (principal o intermedia activa)
$principal = $persona->getEmpresa();
$principalId = $principal ? (int)$principal->getId() : 0;

$asociado = ($principalId === $empresaId);

if (!$asociado) {
    $v = $entityManager->getRepository(EmpresasPersonas::class)->findOneBy([
        'persona' => $persona,
        'empresa' => $empresa,
        'estado'  => 1
    ]);
    $asociado = (bool)$v;
}

if (!$asociado) {
    $_SESSION['errorMensaje'] = 'No estás asociado a esa empresa.';
    header('Location: empresas_asociadas.php');
    exit;
}

// Cambiar empresa activa en sesión
$_SESSION['empresa_id'] = $empresaId;

// Persistir última empresa activa (si existe el campo en Persona)
if (method_exists($persona, 'setEmpresaActiva')) {
    $persona->setEmpresaActiva($empresa);
    $entityManager->flush();
}

$_SESSION['successMensaje'] = 'Ahora estás usando: ' . ($empresa->getRazonSocial() ?? 'Empresa');
header('Location: empresas_asociadas.php');
exit;
