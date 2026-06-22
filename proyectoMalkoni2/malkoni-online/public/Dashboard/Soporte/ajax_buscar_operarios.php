<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Entities\Personas;

require_once __DIR__ . '/../../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../../config/doctrine.php';

// Solo soporte
if (
    empty($_SESSION['usuario']) ||
    $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
    (int)($_SESSION['rol'] ?? 0) !== 3
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) {
    echo json_encode(['success' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$t = '%' . mb_strtolower($q) . '%';
$isNumeric = ctype_digit($q);

$qb = $entityManager->createQueryBuilder();
$qb->select('p')
   ->from(Personas::class, 'p')
   ->where('p.rol = :rol')
   ->setParameter('rol', 2);

// búsqueda “amigable”
// - email (case-insensitive)
// - nombre+apellido (case-insensitive)
// - teléfono
// - dni como string usando CONCAT('', p.dni) (sin CAST)
$orX = $qb->expr()->orX(
    $qb->expr()->like('LOWER(p.email)', ':t'),
    $qb->expr()->like('LOWER(CONCAT(COALESCE(p.nombre, \'\'), \' \', COALESCE(p.apellido, \'\')))', ':t'),
    $qb->expr()->like('COALESCE(p.num_tel, \'\')', ':t'),
    $qb->expr()->like('CONCAT(\'\', COALESCE(p.dni, \'\'))', ':t')
);

$qb->andWhere($orX)
   ->setParameter('t', $t);

// si el término es numérico: matcheo exacto por DNI (mejor performance)
if ($isNumeric) {
    $qb->orWhere('p.dni = :dniExact')
       ->setParameter('dniExact', (int)$q);
}

$qb->setMaxResults(25);

$items = [];
foreach ($qb->getQuery()->getResult() as $p) {
    /** @var Personas $p */
    $nombre = trim((string)$p->getNombre() . ' ' . (string)$p->getApellido());
    $email  = (string)$p->getEmail();
    $dni    = (string)$p->getDni();
    $tel    = (string)$p->getNumTel();

    $labelParts = [];
    if ($nombre !== '') $labelParts[] = $nombre;
    if ($email !== '')  $labelParts[] = $email;
    if ($dni !== '')    $labelParts[] = 'DNI ' . $dni;
    if ($tel !== '')    $labelParts[] = 'Tel ' . $tel;

    $items[] = [
        'id'      => (int)$p->getId(),
        'label'   => implode(' · ', $labelParts),
        'nombre'  => $nombre,
        'email'   => $email,
        'dni'     => $dni,
        'telefono'=> $tel,
    ];
}

echo json_encode(['success' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
