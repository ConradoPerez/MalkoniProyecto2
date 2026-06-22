<?php
// crear_localidad.php
require_once __DIR__ . '/../vendor/autoload.php';
$em = require __DIR__ . '/../config/doctrine.php';
use Entities\Localidades, Entities\Provincias;

$data = json_decode(file_get_contents('php://input'), true);
$nombre    = trim($data['nombre'] ?? '');
$provincia = (int) ($data['provincia'] ?? 0);

header('Content-Type: application/json');
if ($nombre === '' || $provincia<=0) {
    http_response_code(400);
    echo json_encode(['error'=>'Faltan datos']);
    exit;
}

$repoProv = $em->getRepository(Provincias::class);
$prov     = $repoProv->find($provincia);
if (!$prov) {
    http_response_code(404);
    echo json_encode(['error'=>'Provincia no encontrada']);
    exit;
}

// chequeo duplicado
$repoLoc = $em->getRepository(Localidades::class);
$exist   = $repoLoc->findOneBy(['nombre'=>$nombre,'provincia'=>$prov]);
if ($exist) {
    echo json_encode(['id'=>$exist->getId()]);
    exit;
}

// creo
$loc = new Localidades();
$loc->setNombre($nombre)->setProvincia($prov);
$em->persist($loc);
$em->flush();

echo json_encode(['id'=>$loc->getId()]);
