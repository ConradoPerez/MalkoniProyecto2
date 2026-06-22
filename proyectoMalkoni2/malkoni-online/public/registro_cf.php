<?php
// public/registro_cf.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\Direcciones;
use Entities\Localidades;
use Entities\Provincias;
use Entities\Paises;
use Entities\EmpresasPersonas;

require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/apifact/api.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// === AJAX: buscar localidades por provincia (autocomplete) ===
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'localidades') {
    header('Content-Type: application/json; charset=utf-8');

    $provId = (int)($_GET['provincia_id'] ?? 0);
    $q      = trim((string)($_GET['q'] ?? ''));

    if ($provId <= 0 || mb_strlen($q, 'UTF-8') < 2) {
        echo json_encode(['ok' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $provincia = $entityManager->getRepository(Provincias::class)->find($provId);
    if (!$provincia) {
        echo json_encode(['ok' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $qb = $entityManager->getRepository(Localidades::class)->createQueryBuilder('l');
    $items = $qb
        ->select('l.id, l.nombre')
        ->where('l.provincia = :prov')
        ->andWhere('LOWER(l.nombre) LIKE :q')
        ->setParameter('prov', $provincia)
        ->setParameter('q', '%' . mb_strtolower($q, 'UTF-8') . '%')
        ->orderBy('l.nombre', 'ASC')
        ->setMaxResults(20)
        ->getQuery()
        ->getArrayResult();

    echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ===================== UTILIDADES ===================== */

function norm(?string $s): string { return trim((string)$s); }
function to_upper(?string $s): string {
  $s = norm($s);
  return $s === '' ? '' : mb_strtoupper($s, 'UTF-8');
}

// CUIL desde DNI + género (espera DNI como string de dígitos)
function calcularCuil(string $dni, string $genero): string {
    $dni = str_pad(preg_replace('/\D/', '', $dni), 8, '0', STR_PAD_LEFT);
    $aa = ($genero === 'M') ? '20' : '27';
    $cuilBase = $aa . $dni;
    $mult = [5,4,3,2,7,6,5,4,3,2];
    $s = 0;
    for ($i = 0; $i < 10; $i++) $s += (int)$cuilBase[$i] * $mult[$i];
    $r = $s % 11; $d = 11 - $r;
    if ($d === 11) $d = 0;
    elseif ($d === 10) { $aa = '23'; $d = ($genero === 'M') ? 9 : 4; }
    return $aa . $dni . $d;
}

function generarTokenOPT(int $length = 20): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $token = '';
    for ($i = 0; $i < $length; $i++) $token .= $chars[random_int(0, strlen($chars)-1)];
    return $token;
}

function sga_log(string $msg, array $ctx = []): void {
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if (!empty($ctx)) $line .= ' | ' . json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $line .= PHP_EOL;

    @file_put_contents($dir . '/sga_sync.log', $line, FILE_APPEND);
}

function paso1CFCompleto(array $p): bool {
    $ap = trim($p['apellido'] ?? '');
    $no = trim($p['nombre'] ?? '');
    $ge = trim($p['genero'] ?? '');
    $dn = trim($p['dni'] ?? '');
    $tl = trim($p['telefono'] ?? '');
    $em = trim($p['email'] ?? '');
    $pw = $p['password'] ?? '';
    $cp = $p['confirm_password'] ?? '';
    if ($ap==='' || $no==='' || $ge==='' || $dn==='' || $tl==='' || $em==='' || $pw==='' || $cp==='') return false;
    if (!ctype_digit($dn)) return false;
    $len = strlen($dn);
    if ($len !== 7 && $len !== 8) return false;
    if (!filter_var($em, FILTER_VALIDATE_EMAIL)) return false;
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $pw)) return false;
    if ($pw !== $cp) return false;
    return true;
}

function paso2CFCompleto(array $d): bool {
    $pais  = (int)($d['pais'] ?? 0);
    $prov  = (int)($d['provincia'] ?? 0);
    $cp    = trim($d['cp'] ?? '');

    if ($pais <= 0 || $prov <= 0 || $cp === '') return false;

    // Solo Argentina obliga Localidad
    if ($pais === 1) {
        $locId = (int)($d['localidad_id'] ?? 0);
        if ($locId <= 0) return false;
    }
    return true;
}

/* ===================== LISTAS BASE ===================== */
$listaPaises     = $entityManager->getRepository(Paises::class)->findAll();
$listaProvincias = $entityManager->getRepository(Provincias::class)->findAll();

/* ===================== PRECARGA POR GET ===================== */
$getEmpresaId = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : null;
$getClear     = isset($_GET['clear']) && $_GET['clear'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $getClear) {
    unset($_SESSION['registro_cf']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $getClear && $getEmpresaId) {
    $empresaCF = $entityManager->getRepository(Empresas::class)->find($getEmpresaId);
    if ($empresaCF) {
        // Guardamos Empresa CF (autocompletado Paso 1 y Paso 2)
        $_SESSION['registro_cf']['empresa'] = [
            'empresa_id'       => $empresaCF->getId(),
            'razon_social'     => $empresaCF->getRazonSocial(),
            'dni'              => method_exists($empresaCF,'getDni') ? $empresaCF->getDni() : null,
            'email_empresa'    => $empresaCF->getEmail(),
            'telefono_empresa' => $empresaCF->getNumTel(),
        ];

        // Precarga básica de persona desde razón social / empresa (Paso 1)
        $rs = trim((string)$empresaCF->getRazonSocial());
        $partes = preg_split('/\s+/', $rs, 2);
        $_SESSION['registro_cf']['persona'] = array_merge($_SESSION['registro_cf']['persona'] ?? [], [
            'apellido'  => $partes[0] ?? '',
            'nombre'    => $partes[1] ?? '',
            'dni'       => method_exists($empresaCF,'getDni') ? (string)$empresaCF->getDni() : '',
            'telefono'  => $empresaCF->getNumTel() ?? '',
            'email'     => $empresaCF->getEmail() ?? '',
            // genero lo completa el usuario
        ]);

        // Precarga de dirección (Paso 2) si existe en la empresa
        $d = method_exists($empresaCF,'getDirecciones') ? ($empresaCF->getDirecciones()->first() ?: null) : null;
        if ($d) {
            $dom = $d->getDomicilio() ?: '';
            if (preg_match('/^(.*)\s+(\S+)$/', $dom, $m)) { $calle = trim($m[1]); $numero = trim($m[2]); }
            else { $calle = $dom; $numero = ''; }

            $prov = $d->getProvincia();

            // Localidad segura
            $locNombre = '';
            $locId = '';
            
            $locProxy = $d->getLocalidad();
            if ($locProxy) {
                try {
                    $tmpId = method_exists($locProxy,'getId') ? (int)$locProxy->getId() : 0;
                    if ($tmpId > 0) {
                        $locReal = $entityManager->getRepository(Localidades::class)->find($tmpId);
                        if ($locReal) {
                            $locId = (string)$locReal->getId();
                            $locNombre = (string)$locReal->getNombre();
                        }
                    }
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $locNombre = '';
                    $locId = '';
                }
            }

            $_SESSION['registro_cf']['direccion'] = [
                'pais'          => ($prov && $prov->getPais()) ? $prov->getPais()->getId() : 1,
                'provincia'     => $prov ? $prov->getId() : '',
                'localidad_id'     => $locId ?? '',
                'localidad_nombre' => $locNombre,
                'barrio'        => $d->getBarrio() ?? '',
                'calle'         => $calle,
                'numero'        => $numero,
                'cp'            => $d->getCp() ?? '',
                'observaciones' => $d->getObservaciones() ?? '',
            ];
        }
    }
    $paso = 1;
}

/* ===================== DETERMINAR PASO ===================== */
if (!isset($paso)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['paso']) && !$getClear) unset($_SESSION['registro_cf']);
        $paso = (int)($_GET['paso'] ?? 1);
        if (!$paso) $paso = 1;
    } else {
        $paso = (int)($_POST['paso'] ?? 1);
    }
}

// Evitar saltar al paso 2 vía GET si Paso 1 no está completo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['paso'])) {
    $p   = (int)$_GET['paso'];
    $per = $_SESSION['registro_cf']['persona'] ?? [];
    if ($p === 2 && !paso1CFCompleto($per)) $paso = 1;
}

// Calcular máximo paso alcanzado (para bloquear en UI)
$per = $_SESSION['registro_cf']['persona'] ?? [];
$maxStep = 1;
if (paso1CFCompleto($per)) $maxStep = 2;

/* ===================== AUTOSAVE (igual a registro.php) ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_only'])) {
    $currentPaso = (int)($_POST['paso'] ?? 1);
    if ($currentPaso === 1) {
        $tmp = $_POST;
        // Normalizar DNI: 7 u 8 dígitos, y si es 7 => pad a 8 con 0 adelante
        $tmp['dni'] = preg_replace('/\D/', '', (string)($tmp['dni'] ?? ''));
        if (strlen($tmp['dni']) === 7) {
            $tmp['dni'] = str_pad($tmp['dni'], 8, '0', STR_PAD_LEFT);
        }

        // normalizamos a MAYÚSCULAS en sesión para vista coherente
        $tmp['apellido'] = to_upper($tmp['apellido'] ?? '');
        $tmp['nombre']   = to_upper($tmp['nombre'] ?? '');
        $_SESSION['registro_cf']['persona'] = array_merge($_SESSION['registro_cf']['persona'] ?? [], $tmp);
        // espejo de empresa para panel izquierdo
        $_SESSION['registro_cf']['empresa']['razon_social']     = trim(($tmp['apellido'] ?? '').' '.($tmp['nombre'] ?? ''));
        $_SESSION['registro_cf']['empresa']['email_empresa']    = $tmp['email'] ?? '';
        $_SESSION['registro_cf']['empresa']['telefono_empresa'] = $tmp['telefono'] ?? '';
    } elseif ($currentPaso === 2) {
        $tmp = $_POST;
    
        $tmp['localidad_id']   = (string)($tmp['localidad_id'] ?? '');

        // Si no es Argentina, localidad no aplica: limpiamos ID y nombre
        $paisTmp = (int)($tmp['pais'] ?? 0);
        if ($paisTmp !== 1) {
            $tmp['localidad_id'] = '';
            $tmp['localidad_nombre'] = '';
        }
        
        $tmp['barrio']         = to_upper($tmp['barrio'] ?? '');
        $tmp['calle']          = to_upper($tmp['calle'] ?? '');
        $tmp['observaciones']  = to_upper($tmp['observaciones'] ?? '');
    
        // conservar localidad_nombre desde sesión (porque el input visible no tiene name)
        $prev = $_SESSION['registro_cf']['direccion'] ?? [];
        if ($tmp['localidad_id'] === '') {
            $tmp['localidad_nombre'] = '';
        } else {
            if (!isset($tmp['localidad_nombre']) && isset($prev['localidad_nombre'])) {
                $tmp['localidad_nombre'] = $prev['localidad_nombre'];
            }
        }
        $_SESSION['registro_cf']['direccion'] = array_merge($prev, $tmp);
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true]);
    exit;
}

/* ===================== PROCESAR POST ===================== */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_only'])) {
    if ($paso === 1) {
        // Guardar persona en sesión (con MAYÚSCULAS)
        $_POST['apellido'] = to_upper($_POST['apellido'] ?? '');
        $_POST['nombre']   = to_upper($_POST['nombre'] ?? '');
        // Normalizar DNI: 7 u 8 dígitos, y si es 7 => pad a 8 con 0 adelante
        $_POST['dni'] = preg_replace('/\D/', '', (string)($_POST['dni'] ?? ''));
        if (strlen($_POST['dni']) === 7) {
            $_POST['dni'] = str_pad($_POST['dni'], 8, '0', STR_PAD_LEFT);
        }

        $_SESSION['registro_cf']['persona'] = $_POST;

        // Título panel izquierdo + espejo de empresa
        $ap = norm($_POST['apellido'] ?? '');
        $no = norm($_POST['nombre'] ?? '');
        $_SESSION['registro_cf']['empresa']['razon_social']     = trim($ap.' '.$no);
        $_SESSION['registro_cf']['empresa']['email_empresa']    = $_POST['email'] ?? '';
        $_SESSION['registro_cf']['empresa']['telefono_empresa'] = $_POST['telefono'] ?? '';

        $paso = 2;

    } elseif ($paso === 2) {
    
        // =========================
        // 1) Guardar dirección en sesión
        // =========================
        $tmp = $_POST;
    
        // Normalizaciones
        $tmp['localidad_id']   = (string)($tmp['localidad_id'] ?? '');

        // Si no es Argentina, localidad no aplica: limpiamos ID y nombre
        $paisTmp = (int)($tmp['pais'] ?? 0);
        if ($paisTmp !== 1) {
            $tmp['localidad_id'] = '';
            $tmp['localidad_nombre'] = '';
        }

        $tmp['barrio']         = to_upper($tmp['barrio'] ?? '');

        $tmp['calle']          = to_upper($tmp['calle'] ?? '');
        $tmp['observaciones']  = to_upper($tmp['observaciones'] ?? '');
    
        // IMPORTANTE: localidad_nombre NO viene por POST (el input visible no tiene name)
        // La conservamos desde sesión si ya existía
        $prev = $_SESSION['registro_cf']['direccion'] ?? [];
        if (!isset($tmp['localidad_nombre']) && isset($prev['localidad_nombre'])) {
            $tmp['localidad_nombre'] = $prev['localidad_nombre'];
        }
    
        $_SESSION['registro_cf']['direccion'] = array_merge($prev, $tmp);
    
        // =========================
        // 2) Ensamblar datos (empresa + persona + dirección)
        // =========================
        $datos = array_merge(
            $_SESSION['registro_cf']['empresa']   ?? [],
            $_SESSION['registro_cf']['persona']   ?? [],
            $_SESSION['registro_cf']['direccion'] ?? []
        );
    
        // =========================
        // 3) Validaciones base
        // =========================
        if (($datos['password'] ?? '') !== ($datos['confirm_password'] ?? '')) {
            $error = "Las contraseñas no coinciden.";
            $paso = 2;
        } else {
            $repoPers = $entityManager->getRepository(Personas::class);
    
            // Email duplicado
            if ($repoPers->findOneBy(['email' => $datos['email'] ?? ''])) {
                $error = "El email ya está registrado.";
                $paso = 2;
    
            // DNI duplicado
            } elseif ($repoPers->findOneBy(['dni' => $datos['dni'] ?? ''])) {
                $error = "El DNI ya está registrado.";
                $paso = 2;
    
            } else {
                try {
                    // =========================
                    // 4) País, provincia y localidad (con regla Argentina)
                    // =========================
                    $paisId = (int)($datos['pais'] ?? 0);
                    $provId = (int)($datos['provincia'] ?? 0);
                    
                    $pais      = $entityManager->getRepository(Paises::class)->find($paisId);
                    $provincia = $entityManager->getRepository(Provincias::class)->find($provId);
                    
                    if (!$pais || !$provincia) {
                        throw new \Exception("País o provincia inválidos.");
                    }
                    
                    $esArgentina = ($paisId === 1);
                    
                    $locEnt = null;
                    if ($esArgentina) {
                        $locId = (int)($datos['localidad_id'] ?? 0);
                        if ($locId <= 0) throw new \Exception("Debe seleccionar una localidad de la lista.");
                    
                        $locEnt = $entityManager->getRepository(Localidades::class)->find($locId);
                        if (!$locEnt) throw new \Exception("Localidad inválida.");
                    
                        if ((int)$locEnt->getProvincia()->getId() !== (int)$provincia->getId()) {
                            throw new \Exception("La localidad seleccionada no pertenece a la provincia elegida.");
                        }
                    }

                    // =========================
                    // 5) DNI + género + CUIL
                    // =========================
                    $dniLimpio = preg_replace('/\D/', '', (string)($datos['dni'] ?? ''));
                    if ($dniLimpio === '') throw new \Exception("Debe ingresar un DNI válido.");
                    
                    $lenDni = strlen($dniLimpio);
                    if ($lenDni !== 7 && $lenDni !== 8) {
                        throw new \Exception("El DNI debe tener 7 u 8 dígitos.");
                    }
                    
                    // Si es de 7, lo guardamos con 0 adelante (y se usa para todo lo demás)
                    $dniLimpio = str_pad($dniLimpio, 8, '0', STR_PAD_LEFT);

                    $genero = strtoupper(trim((string)($datos['genero'] ?? '')));
                    if ($genero !== 'M' && $genero !== 'F') throw new \Exception("Debe seleccionar el género.");
    
                    $cuil = calcularCuil($dniLimpio, $genero);
    
                    // =========================
                    // 6) Empresa (update si viene autocompletada, si no crear)
                    // =========================
                    $empresaId = $_SESSION['registro_cf']['empresa']['empresa_id'] ?? null;
    
                    if ($empresaId) {
                        /** @var Empresas|null $empresa */
                        $empresa = $entityManager->getRepository(Empresas::class)->find((int)$empresaId);
                        if (!$empresa) throw new \Exception("Empresa CF a modificar no encontrada.");
    
                        $empresa->setRazonSocial(trim(($datos['apellido'] ?? '').' '.($datos['nombre'] ?? '')))
                                ->setCuit($cuil)
                                ->setEmail($datos['email'])
                                ->setNumTel($datos['telefono'])
                                ->setCodCondIVA('CF');
    
                        if (method_exists($empresa,'setEstado'))       $empresa->setEstado(2);
                        if (method_exists($empresa,'setFechaInicial')) $empresa->setFechaInicial(new \DateTime());
                        if (method_exists($empresa,'setDni'))          $empresa->setDni($dniLimpio);
    
                        $entityManager->persist($empresa);
    
                    } else {
                        $empresa = new Empresas();
                        $empresa->setRazonSocial(trim(($datos['apellido'] ?? '').' '.($datos['nombre'] ?? '')))
                                ->setCuit($cuil)
                                ->setEmail($datos['email'])
                                ->setNumTel($datos['telefono'])
                                ->setCodCondIVA('CF');
    
                        if (method_exists($empresa,'setEstado'))       $empresa->setEstado(3);
                        if (method_exists($empresa,'setFechaInicial')) $empresa->setFechaInicial(new \DateTime());
                        if (method_exists($empresa,'setDni'))          $empresa->setDni($dniLimpio);
    
                        $entityManager->persist($empresa);
                    }
    
                    // =========================
                    // 8) Dirección (update si ya existe, si no crear)
                    // =========================
                    $dirExistente = null;
                    if (method_exists($empresa, 'getDirecciones')) {
                        $dirExistente = $empresa->getDirecciones()->first() ?: null;
                    }
    
                    $calle  = to_upper($datos['calle'] ?? '');
                    $numero = norm($datos['numero'] ?? '');
                    $domicilio = trim($calle . ($numero !== '' ? ' '.$numero : ''));
    
                    if ($dirExistente instanceof Direcciones) {
                        $dirExistente->setProvincia($provincia)
                                     ->setLocalidad($esArgentina ? $locEnt : null)
                                     ->setDomicilio($domicilio)
                                     ->setBarrio(to_upper($datos['barrio'] ?? ''))
                                     ->setCp($datos['cp'] ?? '')
                                     ->setObservaciones(to_upper($datos['observaciones'] ?? ''));
                        $entityManager->persist($dirExistente);
                    } else {
                        $dir = new Direcciones();
                        $dir->setEmpresa($empresa)
                            ->setProvincia($provincia)
                            ->setLocalidad($esArgentina ? $locEnt : null)
                            ->setDomicilio($domicilio)
                            ->setBarrio(to_upper($datos['barrio'] ?? ''))
                            ->setCp($datos['cp'] ?? '')
                            ->setObservaciones(to_upper($datos['observaciones'] ?? ''));
                        $entityManager->persist($dir);
                    }
    
                    // Flush 1 (para asegurar IDs antes de SGA)
                    $entityManager->flush();
    
                    // =========================================================
                    // 9) SGA Sync CF (no interrumpe)
                    // =========================================================
                    if (!$esArgentina) {
                        // Si no es Argentina, no sincronizamos con SGA porque requiere codloc/codpcia
                        sga_log('SGA CF sync SKIPPED (pais != AR)', [
                            'empresa_id' => (int)$empresa->getId(),
                            'pais_id'    => (int)$paisId,
                            'provincia'  => (int)$provincia->getId(),
                        ]);
                    } else {
                        try {
                            $domicilioSga = trim((string)$domicilio);
                            if ($domicilioSga === '') $domicilioSga = 'S/D';
        
                            $cpSga = trim((string)($datos['cp'] ?? ''));
                            if ($cpSga === '') $cpSga = '0';
        
                            $barrioSga = trim((string)($datos['barrio'] ?? ''));
        
                            $codpciaSga = (int)$provincia->getId();
                            $codlocSga  = (int)$locEnt->getId();
        
                            $clienteDataSga = [
                                'id'     => (int)$empresa->getId(),
                                'codcli' => "",
        
                                'pfj'      => 2,
                                'apellido' => (string)($datos['apellido'] ?? ''),
                                'nombre'   => (string)($datos['nombre'] ?? ''),
                                'genero'   => (string)$genero,
                                'dni'      => (string)$dniLimpio,
        
                                'codcondiva' => 'CF',
        
                                'domicilio' => $domicilioSga,
                                'telefono'  => (string)($datos['telefono'] ?? ''),
                                'celular'   => "",
                                'barrio'    => $barrioSga,
                                'mail'      => (string)($datos['email'] ?? ''),
                                'cp'        => (string)$cpSga,
        
                                'codloc'  => $codlocSga,
                                'codpcia' => $codpciaSga,
                            ];
        
                            sga_log('SGA CF sync REQUEST', [
                                'empresa_id' => (int)$empresa->getId(),
                                'payload'    => $clienteDataSga,
                            ]);
        
                            $debugSga = null;
                            $codcli = syncClienteFacturacion($clienteDataSga, $debugSga);
        
                            sga_log('SGA CF /clientes RAW', [
                                'empresa_id' => (int)$empresa->getId(),
                                'http_code'  => $debugSga['code'] ?? null,
                                'raw'        => $debugSga['raw'] ?? null,
                                'body'       => $debugSga['body'] ?? null,
                            ]);
        
                            sga_log('SGA CF sync RESPONSE', [
                                'empresa_id' => (int)$empresa->getId(),
                                'codcli'     => $codcli,
                            ]);
        
                            if (!empty($codcli)) {
                                $empresa->setCodCliente((string)$codcli);
                                $entityManager->persist($empresa);
                                $entityManager->flush();
        
                                sga_log('SGA CF codcli guardado OK', [
                                    'empresa_id'  => (int)$empresa->getId(),
                                    'cod_cliente' => (string)$empresa->getCodCliente(),
                                ]);
                            } else {
                                sga_log('SGA CF sync OK pero sin codcli', [
                                    'empresa_id' => (int)$empresa->getId(),
                                ]);
                            }
        
                        } catch (\Throwable $e) {
                            sga_log('SGA CF sync ERROR', [
                                'empresa_id' => isset($empresa) ? (int)$empresa->getId() : null,
                                'error'      => $e->getMessage(),
                            ]);
                        }
                    }

                    // =========================
                    // 10) Persona (usuario CF)
                    // =========================
                    $persona = new Personas();
                    $persona->setNombre(to_upper($datos['nombre']))
                            ->setApellido(to_upper($datos['apellido']))
                            ->setGenero($genero)
                            ->setDni($dniLimpio)
                            ->setEmail($datos['email'])
                            ->setNumTel($datos['telefono'])
                            ->setPass(password_hash($datos['password'], PASSWORD_DEFAULT))
                            ->setTokenOpt(generarTokenOPT())
                            ->setEmpresa($empresa)
                            ->setRol(2)
                            ->setEstadoPersona(1);
                    $entityManager->persist($persona);
    
                    // Tabla intermedia EmpresasPersonas
                    $empPer = new EmpresasPersonas();
                    $empPer->setEmpresa($empresa);
                    $empPer->setPersona($persona);
                    $empPer->setEstado(1);
                    $entityManager->persist($empPer);
    
                    // =========================
                    // 11) Token validación + flush final
                    // =========================
                    $mail  = new PHPMailer(true);
                    $token = bin2hex(random_bytes(16));
                    if (method_exists($empresa,'setValidacionToken')) {
                        $empresa->setValidacionToken($token);
                    }
    
                    $entityManager->flush();
    
                    // =========================
                    // 12) Envío mail
                    // =========================
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'mail.malkoni.com.ar';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'no-reply@online.malkoni.com.ar';
                        $mail->Password   = '#$Mcp4n3lI$#';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';
                        $mail->Encoding   = 'base64';
                        $mail->setFrom('no-reply@online.malkoni.com.ar','Malkoni Hnos');
                        $mail->addAddress($empresa->getEmail());
                        $mail->isHTML(true);
                        $mail->Subject = 'Validación de cuenta - Malkoni Hnos';
                        $link = "https://online.malkoni.com.ar/validar_usuario_mail.php?token=$token";
                        $mail->Body = "
                          <div style='background:#f4f4f4;padding:20px;font-family:sans-serif;'>
                            <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;'>
                              <div style='background:#166379;color:#fff;padding:20px;text-align:center;'>
                                <h2>Bienvenido a Malkoni Hnos</h2>
                              </div>
                              <div style='padding:30px;text-align:center;color:#333;'>
                                <p style='font-size:1rem;'>¡Gracias por registrarte! Para poder ingresar, debes validar tu cuenta:</p>
                                <a href='{$link}' style='display:inline-block;padding:12px 24px;
                                   background:#D88429;color:#fff;border-radius:30px;text-decoration:none;
                                   font-weight:bold;'>Validar cuenta</a>
                                   <p style='margin-top:20px;color:#333;'><strong>Gracias por usar los servicios online de Malkoni Hnos.</strong></p>
                              </div>
                              <div style='background:#f1f1f1;color:#888;padding:10px;text-align:center;font-size:0.8em;'>© Malkoni Hnos</div>
                            </div>
                          </div>";
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Mail error: " . $mail->ErrorInfo);
                    }
    
                    // Fin
                    session_destroy();
                    $emailParam = urlencode($datos['email']);
                    header("Location: login.php?registro=ok&email={$emailParam}");
                    exit;
    
                } catch (\Exception $e) {
                    $error = "Error inesperado: ".$e->getMessage();
                    $paso = 2;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Malkoni Hnos - Servicios Online</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/registro_cfStyles.css?v=<?=time()?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
  #localidad-input:disabled{
    background:#e9ecef !important;
    color:#6c757d !important;
    cursor:not-allowed !important;
    opacity: 1 !important;
  }
</style>

</head>
<body>

<div class="container">
  <div class="left-panel">
    <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
    <h1 style="font-family: 'Syncopate', sans-serif; font-weight: 700; color: #E1DFD9; font-size: 2.7rem; margin-bottom: 5px;">Consumidor Final</h1>
    <p style="color: #E1DFD9; font-size: 19px; margin-top: 0;">Complete sus datos para registrarse</p>

    <?php if (!empty($_SESSION['registro_cf']['empresa']['razon_social'])): ?>
      <p style="color:#E1DFD9;font-size:1.5rem;margin-top:5px;font-weight:600;">
        <?= htmlspecialchars($_SESSION['registro_cf']['empresa']['razon_social'], ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php else:
        $vn = $_SESSION['registro_cf']['persona']['apellido'] ?? '';
        $nn = $_SESSION['registro_cf']['persona']['nombre'] ?? '';
        $label = trim($vn.' '.$nn);
        if ($label !== ''): ?>
      <p style="color:#E1DFD9;font-size:1.5rem;margin-top:5px;font-weight:600;">
        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php endif; endif; ?>
  </div>

  <div class="right-panel">
    <div class="step-indicator" data-current-step="<?= (int)$paso ?>" data-max-step="<?= (int)$maxStep ?>">
      <a href="registro_cf.php?paso=1" class="step <?= $paso===1?'active':($paso>1?'completed':'') ?>" data-step="1">1</a>
      <a href="registro_cf.php?paso=2" class="step <?= $paso===2?'active':'' ?> <?= (2>$maxStep)?'locked':'' ?>" data-step="2">2</a>
    </div>

    <div class="form-header">
      <?php if ($paso===1): ?>
        <h1 class="form-title">Datos Personales</h1>
      <?php else: ?>
        <h1 class="form-title">Dirección</h1>
      <?php endif; ?>
    </div>

    <div class="form-container">
      <?php if ($paso === 1):
          $v = $_SESSION['registro_cf']['persona'] ?? [];
      ?>
      <form method="post" action="registro_cf.php" id="form-paso1" autocomplete="off">
        <input type="hidden" name="paso" value="1">
        <div class="two-columns-form">
          <div class="column">
            <div class="form-group">
              <label class="form-label">Apellido <span style="color:red">*</span></label>
              <input class="form-input" name="apellido" type="text" required placeholder="Ingrese apellido" value="<?= htmlspecialchars($v['apellido'] ?? '') ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Nombre <span style="color:red">*</span></label>
              <input class="form-input" name="nombre" type="text" required placeholder="Ingrese nombre" value="<?= htmlspecialchars($v['nombre'] ?? '') ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Género <span style="color:red">*</span></label>
              <select class="form-input" name="genero" required>
                <option value="">--Seleccione--</option>
                <option value="M" <?= ($v['genero']??'')==='M'?'selected':'' ?>>Masculino</option>
                <option value="F" <?= ($v['genero']??'')==='F'?'selected':'' ?>>Femenino</option>
              </select>
            </div>
          </div>

          <div class="column">
            <div class="form-group">
              <label class="form-label">DNI <span style="color:red">*</span></label>
              <input class="form-input" name="dni" type="text" required placeholder="Ej. 12345678" value="<?= htmlspecialchars($v['dni'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Teléfono <span style="color:red">*</span></label>
              <input class="form-input" name="telefono" type="tel" required placeholder="Ingrese teléfono" value="<?= htmlspecialchars($v['telefono'] ?? '') ?>">
            </div>
          </div>
        </div>

        <h2 class="section-title" style="font-family:'Syncopate',sans-serif;font-weight:700;color:#166379;font-size:1.7rem;margin-bottom:6px;">Datos de acceso al sistema</h2>

        <div class="form-group">
          <label class="form-label">Email Personal <span style="color:red">*</span></label>
          <input class="form-input" name="email" type="email" required placeholder="ej. usuario@dominio.com" value="<?= htmlspecialchars($v['email'] ?? '') ?>">
        </div>

        <div class="form-group password-wrapper">
          <label class="form-label">Contraseña <span style="color:red">*</span></label>
          <input id="password" class="form-input" name="password" type="password" required
                 pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}"
                 title="8+ caracteres, con mayúscula, minúscula y número"
                 placeholder="8+ caracteres">
          <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        
        <div class="password-box" id="password-box">
          <h4>Requisitos:</h4>
          <ul>
            <li id="req-length"><i class="fa-solid fa-circle"></i> Mínimo 8 caracteres</li>
            <li id="req-upper"><i class="fa-solid fa-circle"></i> 1 letra mayúscula</li>
            <li id="req-lower"><i class="fa-solid fa-circle"></i> 1 letra minúscula</li>
            <li id="req-number"><i class="fa-solid fa-circle"></i> 1 número</li>
            <li id="req-match"><i class="fa-solid fa-circle"></i> Coinciden ambas contraseñas</li>
          </ul>
        </div>


        <div class="form-group password-wrapper">
          <label class="form-label">Confirmar Contraseña <span style="color:red">*</span></label>
          <input id="confirm_password" class="form-input" name="confirm_password" type="password" required placeholder="Repetir contraseña">
          <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        
        <div class="button-group">
          <button type="button" onclick="saveAndGo(1, 'validar_usuario_cf.php')" class="submit-btn back">Volver</button>
          <button type="submit" class="submit-btn">Siguiente</button>
        </div>
      </form>

      <?php else:
        // Paso 2: usar lo que haya en sesión (por autocompletado) o defaults
        $v = array_merge([
            'pais'         => 1,
            'provincia'    => '',
            'localidad_id' => '',
            'localidad_nombre' => '',
            'barrio'       => '',
            'calle'        => '',
            'numero'       => '',
            'cp'           => '',
            'observaciones'=> ''
        ], $_SESSION['registro_cf']['direccion'] ?? []);
      ?>
      <form method="post" action="registro_cf.php" id="form-paso2" autocomplete="off">
        <input type="hidden" name="paso" value="2">
        <div class="two-columns-form">
          <div class="column">
            <!-- País -->
            <div class="form-group">
              <label class="form-label">País <span style="color:red">*</span></label>
              <select id="pais-select" class="form-input" name="pais" required>
                <option value="">--Seleccione País--</option>
                <?php foreach($listaPaises as $p): ?>
                  <option value="<?= $p->getId() ?>" <?= (string)$v['pais']===(string)$p->getId()?'selected':'' ?>>
                    <?= htmlspecialchars($p->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
        
            <!-- Provincia -->
            <div class="form-group">
              <label class="form-label">Provincia <span style="color:red">*</span></label>
              <select id="provincia-select" class="form-input" name="provincia" required>
                <option value="">--Seleccione Provincia--</option>
                <?php foreach($listaProvincias as $prov): ?>
                  <option value="<?= $prov->getId() ?>" data-pais="<?= $prov->getPais() ? $prov->getPais()->getId() : '' ?>"
                          <?= (string)$v['provincia']===(string)$prov->getId()?'selected':'' ?>>
                    <?= htmlspecialchars($prov->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
        
            <!-- Localidad (texto) -->
            <div class="form-group" style="position:relative;">
              <label class="form-label">Localidad <span style="color:red">*</span></label>
            
              <!-- ID real (OBLIGATORIO) -->
              <input type="hidden" id="localidad-id" name="localidad_id"
                     value="<?= htmlspecialchars($v['localidad_id'] ?? '') ?>">
            
              <!-- Texto visible (solo para buscar/mostrar) -->
              <?php
                $paisSel = (int)($v['pais'] ?? 0);
                $esArgentina = ($paisSel === 1);
                $disabledLoc = (!$esArgentina) || empty($v['provincia']);
              ?>
              <input
                id="localidad-input"
                class="form-input"
                type="text"
                placeholder="Escriba para buscar (mín. 2 letras)"
                value="<?= htmlspecialchars($v['localidad_nombre'] ?? '') ?>"
                autocomplete="off"
                <?= $disabledLoc ? 'disabled' : '' ?>
              >
            
              <!-- dropdown -->
              <div id="localidad-suggestions"
                   style="display:none; position:absolute; left:0; right:0; top:72px; z-index:50;
                          background:#fff; border:1px solid #e8e8ef; border-radius:10px;
                          max-height:240px; overflow:auto; box-shadow:0 10px 30px rgba(0,0,0,.08);">
              </div>
            
              <small style="display:block;margin-top:6px;color:#6c757d;">
                Escriba y seleccione una localidad.
              </small>
            </div>

            <!-- Código Postal -->
            <div class="form-group">
              <label class="form-label">Código Postal <span style="color:red">*</span></label>
              <input class="form-input" name="cp" type="text" required placeholder="Ej. 5000" value="<?= htmlspecialchars($v['cp']) ?>">
            </div>
          </div>

          <div class="column">
            <div class="form-group">
              <label class="form-label">Barrio</label>
              <input class="form-input" name="barrio" type="text" placeholder="Ingrese barrio (opcional)" value="<?= htmlspecialchars($v['barrio']) ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Calle</label>
              <input class="form-input" name="calle" type="text" placeholder="Ingrese calle (opcional)" value="<?= htmlspecialchars($v['calle']) ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Número</label>
              <input class="form-input" name="numero" type="text" placeholder="Ej. 123 (opcional)" value="<?= htmlspecialchars($v['numero']) ?>">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Observaciones</label>
          <textarea class="form-input3" name="observaciones" rows="2" placeholder="Manzana, Lote, Descripción, Referencias, etc." data-uppercase><?= htmlspecialchars($v['observaciones']) ?></textarea>
        </div>

        <div class="button-group">
          <button type="button" onclick="saveAndGo(2, 'registro_cf.php?paso=1')" class="submit-btn back">Volver</button>
          <button type="submit" class="submit-btn">Finalizar Registro</button>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Toggle password -->
<script>
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.toggle-password');
    if (!btn) return;
    e.preventDefault();
    const wrap  = btn.closest('.password-wrapper');
    if (!wrap) return;
    const input = wrap.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;
    const isPwd = input.type === 'password';
    input.type  = isPwd ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
      icon.classList.toggle('fa-eye', !isPwd);
      icon.classList.toggle('fa-eye-slash', isPwd);
    }
    btn.setAttribute('aria-label', isPwd ? 'Ocultar contraseña' : 'Mostrar contraseña');
  });
</script>

<!-- Bloqueo de navegación de pasos + AUTOSAVE -->
<script>
(function(){
  const cont = document.querySelector('.step-indicator');
  if (!cont) return;

  const currentStep = parseInt(cont.dataset.currentStep || '1', 10);
  const maxStep     = parseInt(cont.dataset.maxStep || '1', 10);

  function formForStep(step){
    if (step === 1) return document.getElementById('form-paso1');
    if (step === 2) return document.getElementById('form-paso2');
    return null;
  }

  async function autosave(step, gotoStep) {
    const form = formForStep(step);
    if (!form) return true;
    const fd = new FormData(form);
    fd.append('save_only', '1');
    if (gotoStep) fd.append('goto_step', String(gotoStep));
    try {
      const res = await fetch('registro_cf.php', { method:'POST', body: fd, credentials: 'same-origin' });
      if (!res.ok) throw new Error('HTTP '+res.status);
      return true;
    } catch (e) {
      console.error('Autosave error', e);
      return false;
    }
  }

  cont.querySelectorAll('.step').forEach(a => {
    a.addEventListener('click', async (e) => {
      const target = parseInt(a.dataset.step || '1', 10);
      if (target === currentStep) return;

      if (target < currentStep) {
        e.preventDefault();
        await autosave(currentStep, target);
        window.location.href = 'registro_cf.php?paso=' + target;
        return;
      }

      e.preventDefault();
      const form = formForStep(currentStep);
      if (!form) {
        window.location.href = 'registro_cf.php?paso=' + target;
        return;
      }
      if (!form.checkValidity()) {
        Swal.fire({
          icon: 'info',
          title: 'Completá todos los campos',
          text: 'Para continuar, primero completá los obligatorios del paso actual.',
          confirmButtonColor: '#166379'
        });
        return;
      }
      form.submit();
    });
  });

  // Inputs a MAYÚSCULAS en vivo
  function attachUppercase(selector){
    document.querySelectorAll(selector).forEach(el=>{
      el.addEventListener('input', ()=>{
        const start = el.selectionStart, end = el.selectionEnd;
        el.value = el.value.toUpperCase();
        if (start !== null && end !== null) el.setSelectionRange(start, end);
      });
    });
  }
  attachUppercase('input[data-uppercase], textarea[data-uppercase]');

    // Filtrado provincias por país + limpieza de dependencias (Provincia + Localidad)
    // Requiere en el HTML: #pais-select, #provincia-select, #localidad-input, #localidad-id, #localidad-suggestions
    const paisSelect       = document.getElementById('pais-select');
    const provinciaSelect  = document.getElementById('provincia-select');
    const localidadInput   = document.getElementById('localidad-input');
    const localidadIdInput = document.getElementById('localidad-id');
    const localidadBox     = document.getElementById('localidad-suggestions');
    
    function hideLocBox(){
      if (!localidadBox) return;
      localidadBox.style.display = 'none';
      localidadBox.innerHTML = '';
    }
    
    function limpiarLocalidad(){
      if (localidadInput)   localidadInput.value = '';
      if (localidadIdInput) localidadIdInput.value = '';
      hideLocBox();
    }
    
    function filtrarProvinciasPorPais() {
      if (!paisSelect || !provinciaSelect) return;
    
      const paisId = paisSelect.value;
    
      Array.from(provinciaSelect.options).forEach(opt => {
        const p = opt.getAttribute('data-pais');
        opt.style.display = (!p || p === paisId || opt.value === '') ? 'block' : 'none';
      });
    
      // Si la provincia seleccionada quedó oculta (no corresponde al país), la limpiamos
      if (provinciaSelect.selectedIndex > -1) {
        const selOpt = provinciaSelect.options[provinciaSelect.selectedIndex];
        if (selOpt && selOpt.style.display === 'none') {
          provinciaSelect.value = '';
        }
      }
    }
    
    if (paisSelect) {
      paisSelect.addEventListener('change', () => {
        // ⇨ Cambio de País: limpiar Provincia y Localidad (texto + id + dropdown)
        if (provinciaSelect) provinciaSelect.value = '';
        limpiarLocalidad();
        filtrarProvinciasPorPais();
    
        // Si no es Argentina, deshabilitar input de localidad
        const paisId = parseInt(paisSelect.value || '0', 10);
        const esArgentina = (paisId === 1);
        if (localidadInput) localidadInput.disabled = !esArgentina;
      });
    
      // Inicialización inmediata (sin DOMContentLoaded)
      filtrarProvinciasPorPais();
    
      // Estado inicial del input localidad (solo Argentina y con provincia)
      const paisIdInit = parseInt(paisSelect.value || '0', 10);
      const esArgentinaInit = (paisIdInit === 1);
      const hayProvInit = !!(provinciaSelect && provinciaSelect.value);
    
      if (localidadInput) localidadInput.disabled = !(esArgentinaInit && hayProvInit);
    
      // Si está deshabilitado, aseguramos limpieza completa
      if (localidadInput && localidadInput.disabled) limpiarLocalidad();
    }

    
    if (provinciaSelect) {
      provinciaSelect.addEventListener('change', () => {
        // ⇨ Cambio de Provincia: limpiar Localidad (texto + id + dropdown)
        limpiarLocalidad();
    
        // Habilitar/Deshabilitar localidad según país/provincia
        const paisId = parseInt((paisSelect?.value || '0'), 10);
        const esArgentina = (paisId === 1);
        const hayProv = !!provinciaSelect.value;
    
        if (localidadInput) {
          localidadInput.disabled = !(esArgentina && hayProv);
          if (!localidadInput.disabled) localidadInput.focus();
        }
      });
    }
})();
</script>


<!-- Validación cliente Paso 1 (password) -->
<script>
  const form1 = document.getElementById('form-paso1');
  if (form1) {
    form1.addEventListener('submit', function(e) {

      const dni = this.dni.value.trim();
      const pwd = this.password.value;
      const cpw = this.confirm_password.value;

      // ======================
      // VALIDACIÓN DNI
      // ======================
      if (!/^\d+$/.test(dni)) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'DNI inválido',
          text: 'El DNI solo debe contener números.',
          confirmButtonColor: '#d33'
        });
        return;
      }

      if (dni.length !== 7 && dni.length !== 8) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'DNI inválido',
          text: 'El DNI debe tener 7 u 8 dígitos.',
          confirmButtonColor: '#d33'
        });
        return;
      }

      // ======================
      // VALIDACIÓN PASSWORD
      // ======================
      const pwdRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

      if (!pwdRegex.test(pwd)) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Formato inválido',
          text: 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.',
          confirmButtonColor: '#d33'
        });
        return;
      }

      if (pwd !== cpw) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: '¡Atención!',
          text: 'Las contraseñas no coinciden.',
          confirmButtonColor: '#d33'
        });
        return;
      }
      if (dni.length === 7) {
          this.dni.value = dni.padStart(8, '0');
        }
      // ✔ Si pasa todo → se permite ir al Paso 2
    });
  }
</script>

<?php if ($error): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: '¡Atención!',
    text: <?= json_encode($error) ?>,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Aceptar'
  });
</script>
<?php endif; ?>


<script>
(function(){
  const provSel = document.getElementById('provincia-select');
  const locInp  = document.getElementById('localidad-input');
  const locId   = document.getElementById('localidad-id');
  const box     = document.getElementById('localidad-suggestions');
  const form2   = document.getElementById('form-paso2');

  if (!provSel || !locInp || !locId || !box || !form2) return;

  let t = null;

  function hideBox(){
    box.style.display = 'none';
    box.innerHTML = '';
  }

  function showItems(items){
    const list = items || [];
    if (!list.length) { hideBox(); return; }

    box.innerHTML = list.map(it => `
      <div class="loc-item"
           data-id="${it.id}"
           data-nombre="${String(it.nombre).replace(/"/g,'&quot;')}"
           style="padding:10px 12px; cursor:pointer;">
        ${it.nombre}
      </div>
    `).join('');

    box.style.display = 'block';
  }

  async function search(){
    // si está deshabilitado (no Argentina o sin provincia), no buscar
    if (locInp.disabled) { hideBox(); return; }

    const provId = parseInt(provSel.value || '0', 10);
    const q = (locInp.value || '').trim();

    // si cambia el texto manualmente => invalida selección
    locId.value = '';

    if (!provId || q.length < 2) { hideBox(); return; }

    const url = `registro_cf.php?action=localidades&provincia_id=${encodeURIComponent(provId)}&q=${encodeURIComponent(q)}`;
    const res = await fetch(url, { credentials:'same-origin' });
    const data = await res.json();
    showItems((data && data.items) ? data.items : []);
  }

  // debounce para buscar
  locInp.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(search, 200);
  });

  // click en sugerencia
  box.addEventListener('click', (e) => {
    const item = e.target.closest('.loc-item');
    if (!item) return;
    locId.value = item.dataset.id || '';
    locInp.value = item.dataset.nombre || '';
    hideBox();
  });

  // click afuera => cerrar
  document.addEventListener('click', (e) => {
    if (e.target === locInp || box.contains(e.target)) return;
    hideBox();
  });

  // VALIDACIÓN: no permitir submit si no eligió localidad_id (solo Argentina)
  form2.addEventListener('submit', (e) => {
    const paisId = parseInt((document.getElementById('pais-select')?.value || '0'), 10);
    const provId = parseInt(provSel.value || '0', 10);
    if (!provId) return;

    const esArgentina = (paisId === 1);
    if (esArgentina && !locId.value) {
      e.preventDefault();
      Swal.fire('Atención', 'Debés seleccionar una localidad de la lista.', 'info');
    }
  });

})();
</script>

<script>
  // Global: disponible para el onclick del botón
  async function saveAndGo(step, url) {
    try {
      const form =
        step === 1 ? document.getElementById('form-paso1') :
        step === 2 ? document.getElementById('form-paso2') : null;

      if (form) {
        const fd = new FormData(form);
        fd.append('save_only', '1');
        fd.append('paso', String(step));

        await fetch('registro_cf.php', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
        });
      }

      // Navegar igual aunque falle el autosave
      window.location.href = url;
    } catch (e) {
      console.error('saveAndGo error:', e);
      window.location.href = url;
    }
  }
</script>

<script>
(function(){
  const passwordInput = document.getElementById('password');
  const confirmInput  = document.getElementById('confirm_password');
  const box = document.getElementById('password-box');

  const reqLength = document.getElementById('req-length');
  const reqUpper  = document.getElementById('req-upper');
  const reqLower  = document.getElementById('req-lower');
  const reqNumber = document.getElementById('req-number');
  const reqMatch  = document.getElementById('req-match');

  if (!passwordInput || !confirmInput || !box || !reqLength || !reqUpper || !reqLower || !reqNumber || !reqMatch) return;

  function toggleRequirement(element, valid){
    const icon = element.querySelector('i');
    if (valid){
      element.classList.add('valid');
      if (icon) icon.className = "fa-solid fa-check";
    } else {
      element.classList.remove('valid');
      if (icon) icon.className = "fa-solid fa-circle";
    }
  }

  function refresh(){
    const value = passwordInput.value || '';
    toggleRequirement(reqLength, value.length >= 8);
    toggleRequirement(reqUpper,  /[A-Z]/.test(value));
    toggleRequirement(reqLower,  /[a-z]/.test(value));
    toggleRequirement(reqNumber, /\d/.test(value));
    toggleRequirement(reqMatch,  value !== '' && confirmInput.value === value);
  }

  passwordInput.addEventListener('focus', () => box.classList.add('active'));
  confirmInput.addEventListener('focus',  () => box.classList.add('active'));

  passwordInput.addEventListener('input', refresh);
  confirmInput.addEventListener('input', refresh);

  // por si viene precargado
  refresh();
})();
</script>


</body>
</html>
