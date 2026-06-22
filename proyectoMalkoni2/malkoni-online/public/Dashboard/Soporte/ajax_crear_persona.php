<?php
// ajax_crear_persona.php
header('Content-Type: application/json; charset=UTF-8');

// 1) Sesión y autorización
session_start();
if (
    empty($_SESSION['usuario']) ||
    $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
    $_SESSION['rol'] != 3
) {
    echo json_encode(['success' => false, 'error' => 'No autorizado.']);
    exit;
}

// 2) Autoload + uses
require_once __DIR__ . '/../../../vendor/autoload.php';
use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

// 3) EntityManager
$entityManager = require __DIR__ . '/../../../config/doctrine.php';
$repoPersona   = $entityManager->getRepository(Personas::class);

// 4) Helper para errores
function fail(string $msg) {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

// 5) Recoger + sanear
$empresaId = (int) ($_POST['empresa_id'] ?? 0);
$nombre    = trim($_POST['nombre']   ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$genero    = trim($_POST['genero']   ?? '');
$dniRaw    = trim($_POST['dni']      ?? '');
$email     = trim($_POST['email']    ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$password  = $_POST['password']      ?? '';

if (!$empresaId || $nombre === '' || $apellido === '' || $genero === '' ||
    $dniRaw === '' || $email === '' || $password === ''
) {
    fail('Faltan datos obligatorios.');
}

// 6) Casts
$dni = (int)$dniRaw;

// 7) Validaciones duplicados
if ($repoPersona->findOneBy(['email' => $email])) fail('El email ya está registrado.');
if ($repoPersona->findOneBy(['dni'   => $dni  ])) fail('El DNI ya está registrado.');
if ($telefono !== '' && $repoPersona->findOneBy(['num_tel' => $telefono])) {
    fail('El teléfono ya está registrado.');
}

// 8) Comprobar empresa
$empresa = $entityManager->getRepository(Empresas::class)->find($empresaId);
if (!$empresa) fail('Empresa no encontrada.');

// 9) Token OPT
function generarTokenOPT(int $length = 20): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $t = '';
    for ($i=0; $i<$length; $i++) {
        $t .= $chars[random_int(0, strlen($chars)-1)];
    }
    return $t;
}

// 10) Crear persona
$persona = new Personas();
$persona
    ->setNombre($nombre)
    ->setApellido($apellido)
    ->setGenero($genero)
    ->setDni($dni)
    ->setEmail($email)
    ->setNumTel($telefono ?: null)
    ->setPass(password_hash($password, PASSWORD_DEFAULT))
    ->setEstadoPersona(1)        // Activo
    ->setRol(2)                  // Operario
    ->setTokenOpt(generarTokenOPT());

// Persist persona
$entityManager->persist($persona);
$entityManager->flush();

// 11) Crear relación empresa-persona
$ep = new EmpresasPersonas();
$ep->setEmpresa($empresa);
$ep->setPersona($persona);

$entityManager->persist($ep);
$entityManager->flush();

echo json_encode(['success' => true]);
exit;
