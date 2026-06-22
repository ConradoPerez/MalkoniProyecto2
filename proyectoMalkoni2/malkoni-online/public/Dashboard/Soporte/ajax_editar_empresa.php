<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificación de sesión y rol
if (
    empty($_SESSION['usuario']) ||
    $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
    $_SESSION['rol'] != 3
) {
    echo json_encode(['success' => false, 'error' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../apifact/api.php'; // <-- helper SGA

use Entities\Empresas;
use Entities\Direcciones;
use Entities\Paises;
use Entities\Provincias;
use Entities\Localidades;
use Entities\Personas;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
$entityManager = require __DIR__ . '/../../../config/doctrine.php';

$repoEmp  = $entityManager->getRepository(Empresas::class);
$repoPais = $entityManager->getRepository(Paises::class);
$repoProv = $entityManager->getRepository(Provincias::class);
$repoLoc  = $entityManager->getRepository(Localidades::class);
$repoPer  = $entityManager->getRepository(Personas::class);

// Campos enviados por POST
$id            = (int)   ($_POST['id']               ?? 0);
$rs            = trim((string)($_POST['razon_social']      ?? ''));
$cuit          = trim((string)($_POST['cuit']              ?? ''));
$email         = trim((string)($_POST['email']             ?? ''));
$tel           = trim((string)($_POST['telefono']          ?? ''));
$iva           = trim((string)($_POST['codcondiva']        ?? ''));
$domicilio     = trim((string)($_POST['domicilio']         ?? ''));
$barrio        = trim((string)($_POST['barrio']            ?? ''));
$cp            = trim((string)($_POST['cp']                ?? ''));
$observaciones = trim((string)($_POST['observaciones']     ?? ''));

$paisId        = (int)  ($_POST['pais']             ?? 0);
$provId        = (int)  ($_POST['provincia']        ?? 0);
$locId         = (int)  ($_POST['localidad_id']     ?? 0);
$locNombre     = trim((string)($_POST['localidad_nombre']  ?? ''));

// Validación básica
if (!$id || $rs === '' || $cuit === '' || $email === '') {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios.']);
    exit;
}

/**
 * Log simple (opcional) para debug
 */
function sga_log(string $msg, array $ctx = []): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . ' | ' . json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    @file_put_contents(__DIR__ . '/../../../logs/sga_sync.log', $line, FILE_APPEND);
}

/**
 * Para CF (pfj=2): traer persona asociada a la empresa.
 * Prioriza rol=1 (Admin). Si no hay, trae cualquiera.
 */
function getPersonaDeEmpresaParaCF(\Doctrine\ORM\EntityManagerInterface $em, Empresas $emp): ?Personas
{
    // Admin primero
    $p = $em->createQueryBuilder()
        ->select('p')
        ->from(Personas::class, 'p')
        ->where('p.empresa = :emp')
        ->andWhere('p.rol = 1')
        ->setParameter('emp', $emp)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    if ($p) return $p;

    // Cualquiera
    return $em->createQueryBuilder()
        ->select('p')
        ->from(Personas::class, 'p')
        ->where('p.empresa = :emp')
        ->setParameter('emp', $emp)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

try {
    $conn = $entityManager->getConnection();
    $conn->beginTransaction();

    // 1) Evitar duplicados de CUIT y Email
    if ($ex = $repoEmp->findOneBy(['cuit' => $cuit])) {
        if ((int)$ex->getId() !== $id) {
            throw new \Exception('CUIT ya registrado.');
        }
    }
    if ($ex = $repoEmp->findOneBy(['email' => $email])) {
        if ((int)$ex->getId() !== $id) {
            throw new \Exception('Email ya registrado.');
        }
    }

    /** @var Empresas|null $emp */
    $emp = $repoEmp->find($id);
    if (!$emp) {
        throw new \Exception('Empresa no encontrada.');
    }

    // 2) Actualizar datos de Empresa
    $emp->setRazonSocial($rs)
        ->setCuit($cuit)
        ->setEmail($email)
        ->setNumTel($tel !== '' ? $tel : null)
        ->setCodCondIVA($iva !== '' ? $iva : null);

    // 3) Dirección asociada (primera o crear)
    $dirs = $emp->getDirecciones();
    if ($dirs->isEmpty()) {
        $dir = new Direcciones();
        $emp->addDireccion($dir);
        $entityManager->persist($dir);
    } else {
        $dir = $dirs->first();
    }

    // 4) Provincia válida
    /** @var Provincias|null $prov */
    $prov = $repoProv->find($provId);
    if (!$prov) {
        throw new \Exception('Provincia inválida.');
    }

    // 5) País: validar/inferir
    /** @var Paises|null $pais */
    $pais = null;
    if ($paisId > 0) {
        $pais = $repoPais->find($paisId);
        if (!$pais) {
            throw new \Exception('País inválido.');
        }

        if (method_exists($prov, 'getPais')) {
            $provPais = $prov->getPais();
            if ($provPais && $provPais->getId() !== $pais->getId()) {
                throw new \Exception('La provincia no pertenece al país seleccionado.');
            }
        }
    } else {
        if (method_exists($prov, 'getPais') && $prov->getPais()) {
            $pais = $prov->getPais();
        }
    }

    // 6) Localidad: por ID o por nombre+provincia
    $loc = null;
    if ($locId > 0) {
        $loc = $repoLoc->find($locId);
        if (!$loc) {
            throw new \Exception('Localidad inválida.');
        }
        if (method_exists($loc, 'getProvincia') && $loc->getProvincia() && $loc->getProvincia()->getId() !== $prov->getId()) {
            throw new \Exception('La localidad no pertenece a la provincia seleccionada.');
        }
    } elseif ($locNombre !== '') {
        $loc = $repoLoc->findOneBy([
            'nombre'    => $locNombre,
            'provincia' => $prov
        ]);
        if (!$loc) {
            $loc = new Localidades();
            $loc->setNombre($locNombre)
                ->setProvincia($prov);
            $entityManager->persist($loc);
        }
    }

    // 7) Guardar dirección
    $dir->setDomicilio($domicilio !== '' ? $domicilio : null)
        ->setBarrio($barrio !== '' ? $barrio : null)
        ->setCp($cp !== '' ? $cp : null)
        ->setObservaciones($observaciones !== '' ? $observaciones : null)
        ->setProvincia($prov)
        ->setLocalidad($loc);

    if (method_exists($dir, 'setPais')) {
        $dir->setPais($pais);
    }

    // 8) Flush local
    $entityManager->flush();

    // ==================================
    // 9) Sync SGA (facturación)
    // ==================================
    $codcli = $emp->getCodCliente();
    $codcli = ($codcli === null) ? "" : (string)$codcli;

    // Regla pfj según IVA:
    $pfj = ((string)$iva === 'CF') ? 2 : 1;

    // codloc / codpcia sincronizados con SGA (mismos ids)
    $codpcia = $prov ? (int)$prov->getId() : null;
    $codloc  = ($loc && method_exists($loc, 'getId')) ? (int)$loc->getId() : null;

    // Base payload (como test_cliente.php)
    $clienteData = [
        'id'         => (int)$emp->getId(),                 // <-- importante (doc)
        'codcli'     => $codcli,                            // en editar debería venir siempre
        'pfj'        => $pfj,
        'codcondiva' => (string)($emp->getCodCondIVA() ?? ''),
        'cuit'       => (string)($emp->getCuit() ?? ''),
        'domicilio'  => (string)($dir->getDomicilio() ?? ''),
        'telefono'   => (string)($emp->getNumTel() ?? ''),
        'celular'    => "",                                 // no lo editás, pero el test lo manda
        'barrio'     => (string)($dir->getBarrio() ?? ''),
        'mail'       => (string)($emp->getEmail() ?? ''),
        'cp'         => (string)($dir->getCp() ?? ''),
    ];

    if (trim($clienteData['domicilio']) === '') {
        $clienteData['domicilio'] = 'S/D';
    }

    if ($codloc !== null)  $clienteData['codloc']  = $codloc;
    if ($codpcia !== null) $clienteData['codpcia'] = $codpcia;

    if ($pfj === 1) {
        // Empresa: rsocial obligatorio
        $clienteData['rsocial'] = (string)($emp->getRazonSocial() ?? '');
        if (trim($clienteData['rsocial']) === '') {
            throw new \Exception('PFJ=1 requiere Razón Social para SGA.');
        }
    } else {
        // Persona física: nombre/apellido/genero/dni desde Personas
        $p = getPersonaDeEmpresaParaCF($entityManager, $emp);
        if (!$p) {
            throw new \Exception('PFJ=2 (CF) requiere una Persona asociada (tabla Personas) para enviar datos a SGA.');
        }

        $apellido = trim((string)($p->getApellido() ?? ''));
        $nombre   = trim((string)($p->getNombre() ?? ''));
        $genero   = strtoupper(trim((string)($p->getGenero() ?? '')));
        $dniRaw   = (string)($p->getDni() ?? '');
        $dni      = preg_replace('/\D+/', '', $dniRaw);

        if ($apellido === '' || $nombre === '') {
            throw new \Exception('PFJ=2 requiere nombre y apellido en Personas para SGA.');
        }
        if ($genero !== 'F' && $genero !== 'M') {
            throw new \Exception('PFJ=2 requiere género "F" o "M" en Personas para SGA.');
        }
        if ($dni === '' || strlen($dni) !== 8) {
            throw new \Exception('PFJ=2 requiere DNI de 8 dígitos en Personas para SGA.');
        }

        $clienteData['apellido'] = $apellido;
        $clienteData['nombre']   = $nombre;
        $clienteData['genero']   = $genero;
        $clienteData['dni']      = $dni;
        // Nota: NO mandamos rsocial en pfj=2 (tu helper además lo elimina).
    }

    // Ejecutar sync SGA
    $debug = null;
    sga_log('SGA sync REQUEST', ['empresa_id' => $emp->getId(), 'payload' => $clienteData]);

    $newCodcli = syncClienteFacturacion($clienteData, $debug);

    sga_log('SGA sync RESPONSE', [
        'empresa_id' => $emp->getId(),
        'http_code'  => $debug['code'] ?? null,
        'body'       => $debug['body'] ?? null,
        'new_codcli' => $newCodcli,
    ]);

    // Si SGA devolvió codcli y acá no estaba, guardarlo
    if ($newCodcli !== null && $newCodcli !== '' && ($emp->getCodCliente() === null || $emp->getCodCliente() === '')) {
        $emp->setCodCliente($newCodcli);
        $entityManager->flush();
    }

    $conn->commit();

    echo json_encode(['success' => true]);
    exit;

} catch (\Throwable $e) {
    try {
        $conn = $entityManager->getConnection();
        if ($conn->isTransactionActive()) {
            $conn->rollBack();
        }
    } catch (\Throwable $ignored) {}

    sga_log('SGA/LOCAL sync ERROR', ['error' => $e->getMessage()]);

    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
