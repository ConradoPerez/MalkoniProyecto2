<?php 
// usuario_token.php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

use Entities\Personas;

$token = $_GET['access_token'] ?? $_POST['access_token'] ?? null;
if (!$token || !is_string($token)) {
    echo json_encode(['error' => 'Token inválido'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $persona = $entityManager
        ->getRepository(Personas::class)
        ->findOneBy(['tokenOpt' => trim($token)]);

    if (!$persona) {
        echo json_encode(['error' => 'Persona no encontrada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Valores fijos
    $idCountry = 1;
    $idRegion  = 16;

    // Datos del usuario
    $email     = $persona->getEmail()    ?? '';
    $name      = $persona->getNombre()   ?? '';
    $lastname  = $persona->getApellido() ?? '';
    $telephone = $persona->getNumTel()   ?? '';

    // Construimos la dirección (domicilio) de la empresa asociada
    $address = '';
    if ($empresa = $persona->getEmpresa()) {
        // toArray() para convertir Collection en array y tomar la primera
        $dirs = $empresa->getDirecciones()->toArray();
        if (!empty($dirs)) {
            $address = $dirs[0]->getDomicilio() ?? '';
        }
    }

    // Respuesta final
    $response = [
        'email'     => $email,
        'name'      => $name,
        'lastname'  => $lastname,
        'idCountry' => $idCountry,
        'idRegion'  => $idRegion,
        'address'   => $address,
        'telephone' => $telephone,
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (\Throwable $e) {
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
