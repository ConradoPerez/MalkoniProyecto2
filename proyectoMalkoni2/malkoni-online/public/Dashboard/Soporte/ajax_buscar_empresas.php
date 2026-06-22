<?php
declare(strict_types=1);

session_start();

header('X-Debug-File: ajax_buscar_empresas.php@v5');
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Entities\Empresas;

require_once __DIR__ . '/../../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../../config/doctrine.php';

function toUtf8($v): string {
    if ($v === null) return '';
    $s = (string)$v;

    // si ya es UTF-8 válido
    if ($s !== '' && mb_check_encoding($s, 'UTF-8')) {
        return $s;
    }

    // intento convertir desde encodings típicos
    $converted = @mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
    if (is_string($converted) && $converted !== '') {
        $s = $converted;
    } else {
        // último recurso (iconv si está disponible)
        if (function_exists('iconv')) {
            $tmp = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s);
            if (is_string($tmp)) $s = $tmp;
        } else {
            $s = utf8_encode($s);
        }
    }

    // limpiar carácter de reemplazo (�) y controles
    $s = str_replace("\xEF\xBF\xBD", '', $s);
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s) ?? $s;

    return $s;
}

function jsonOut(array $payload, int $code = 200): void {
    http_response_code($code);
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($json === false) {
        http_response_code(500);
        echo '{"success":false,"error":"json_encode fallo","json_error":"' . toUtf8(json_last_error_msg()) . '"}';
        exit;
    }

    echo $json;
    exit;
}

// Solo soporte
if (
    empty($_SESSION['usuario']) ||
    $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
    (int)($_SESSION['rol'] ?? 0) !== 3
) {
    jsonOut(['success' => false, 'error' => 'No autorizado'], 403);
}

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '__ping__') {
    jsonOut(['success' => true, 'ping' => true, 'dbg' => 'v5']);
}

if (mb_strlen($q) < 2) {
    jsonOut(['success' => true, 'items' => []]);
}

$t = '%' . mb_strtolower($q) . '%';

$qb = $entityManager->createQueryBuilder();
$qb->select('e')
   ->from(Empresas::class, 'e');

$orX = $qb->expr()->orX(
    $qb->expr()->like('LOWER(COALESCE(e.razon_social, \'\'))', ':t'),
    $qb->expr()->like('LOWER(COALESCE(e.email, \'\'))', ':t'),
    $qb->expr()->like('LOWER(COALESCE(e.num_tel, \'\'))', ':t'),
    $qb->expr()->like('LOWER(COALESCE(e.cuit, \'\'))', ':t'),
    $qb->expr()->like('LOWER(COALESCE(e.cod_cliente, \'\'))', ':t')
);

$qb->where($orX)
   ->setParameter('t', $t)
   ->setMaxResults(25)
   ->orderBy('e.razon_social', 'ASC');

$items = [];

foreach ($qb->getQuery()->getResult() as $e) {
    /** @var Empresas $e */
    $razon = toUtf8($e->getRazonSocial() ?? '');
    $cuit  = toUtf8($e->getCuit() ?? '');
    $email = toUtf8($e->getEmail() ?? '');
    $tel   = toUtf8($e->getNumTel() ?? '');
    $cod   = toUtf8($e->getCodCliente() ?? '');

    // ===== estado (blindado) =====
    $estadoInt = null;
    $estadoRaw = null;
    foreach (['getEstado', 'getEstadoEmpresa', 'getEstadoEmpresas'] as $m) {
        if (method_exists($e, $m)) { $estadoRaw = $e->$m(); break; }
    }
    if ($estadoRaw !== null && (is_numeric($estadoRaw) || (is_string($estadoRaw) && ctype_digit($estadoRaw)))) {
        $estadoInt = (int)$estadoRaw;
    }

    // ===== validado (blindado) =====
    $validadoInt = null;
    $valRaw = null;
    foreach (['getValidado', 'isValidado', 'getEmpresaValidada'] as $m) {
        if (method_exists($e, $m)) { $valRaw = $e->$m(); break; }
    }
    if ($valRaw !== null) {
        // puede venir bool, int, string "0"/"1"
        $validadoInt = (int)(is_bool($valRaw) ? ($valRaw ? 1 : 0) : $valRaw);
    }

    // ===== regla final =====
    // NO seleccionable si: estado=1 OR validado=0
    $isEstadoOk   = ($estadoInt === null) ? true : ($estadoInt !== 1);
    $isValidadoOk = ($validadoInt === null) ? true : ($validadoInt === 1);

    $selectable = $isEstadoOk && $isValidadoOk;

    // Label 100% ASCII para que nunca aparezcan “��”
    $labelParts = [];
    if ($razon !== '') $labelParts[] = $razon;
    if ($cuit !== '')  $labelParts[] = 'CUIT ' . $cuit;
    if ($cod !== '')   $labelParts[] = 'CodCli ' . $cod;

    $items[] = [
        'id'           => (int)$e->getId(),
        'label'        => implode(' | ', $labelParts), // <- ASCII
        'razon_social' => $razon,
        'cuit'         => $cuit,
        'email'        => $email,
        'telefono'     => $tel,
        'cod_cliente'  => $cod,
        'estado'       => $estadoInt,     // puede ser null
        'validado'     => $validadoInt,   // puede ser null
        'selectable'   => $selectable,
    ];
}

jsonOut(['success' => true, 'items' => $items]);
