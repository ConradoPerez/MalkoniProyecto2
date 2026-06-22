<?php
// Activar display de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Entities\Empresas;
use Entities\Personas;
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

session_start();
mb_internal_encoding('UTF-8');

// Autoload de Composer y Doctrine
require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

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

    // LIKE con límite (rápido y suficiente para autocomplete)
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

// Capturamos GET para tomar decisiones
$getPaso      = isset($_GET['paso'])       ? (int) $_GET['paso']       : null;
$getEmpresaId = isset($_GET['empresa_id']) ? (int) $_GET['empresa_id'] : null;
$getCuitRaw   = isset($_GET['cuit'])       ? trim($_GET['cuit'])       : null;
$getClear     = isset($_GET['clear'])      ? $_GET['clear'] === '1'    : false;

// === Helpers ===
function validarCuit(string $cuit): bool {
    $cuit = preg_replace('/\D/', '', $cuit);
    if (strlen($cuit) !== 11) return false;
    $digits      = str_split($cuit);
    $multipliers = [5,4,3,2,7,6,5,4,3,2];
    $sum = 0;
    for ($i = 0; $i < 10; $i++) $sum += intval($digits[$i]) * $multipliers[$i];
    $mod = $sum % 11;
    $expected = 11 - $mod;
    if ($expected === 11) $expected = 0;
    elseif ($expected === 10) $expected = 9;
    return intval($digits[10]) === $expected;
}

function isValidEmail(?string $email): bool {
    if ($email === null) return false;
    $email = trim($email);
    return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function norm(?string $s): string {
    return trim((string)$s);
}

function isStrongPassword(?string $pwd): bool {
    if ($pwd === null) return false;
    return (bool)preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $pwd);
}

function generarTokenOPT(int $length = 20): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $out = '';
    for ($i = 0; $i < $length; $i++) $out .= $chars[random_int(0, strlen($chars) - 1)];
    return $out;
}

function to_upper(?string $s): string {
    $s = norm($s);
    return $s === '' ? '' : mb_strtoupper($s, 'UTF-8');
}

function sga_log(string $msg, array $ctx = []): void {
    $dir = __DIR__ . '/../logs'; // crea /logs al lado de /public
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if (!empty($ctx)) $line .= ' | ' . json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $line .= PHP_EOL;

    @file_put_contents($dir . '/sga_sync.log', $line, FILE_APPEND);
}

// 1) Limpiamos sesión sólo si venimos con clear=1
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $getClear) {
    unset($_SESSION['registro']);
}

// 2) Precarga desde validar_usuario.php con empresa existente
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $getClear && $getEmpresaId) {
    $empresa = $entityManager->getRepository(Empresas::class)->find($getEmpresaId);
    if ($empresa) {
        $_SESSION['registro']['empresa'] = [
            'empresa_id'       => $getEmpresaId,
            'razon_social'     => $empresa->getRazonSocial(),
            'cuit'             => $empresa->getCuit(),
            'cod_cond_iva'     => $empresa->getCodCondIVA(),
            'email_empresa'    => $empresa->getEmail(),
            'telefono_empresa' => $empresa->getNumTel(),
        ];
    }
    $paso = 1;
}

// 3) Precarga CUIT cuando venimos sin empresa_id pero con ?clear=1&cuit=...&paso=1
if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    $getClear && $getPaso === 1 && !$getEmpresaId && $getCuitRaw !== null
) {
    $clean = preg_replace('/\D/', '', $getCuitRaw);
    $_SESSION['registro']['empresa']['cuit'] = $clean;
    $paso = 1;
}

// === Autosave AJAX (sin validación dura) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_only'])) {
    $currentPaso = (int)($_POST['paso'] ?? 1);
    if ($currentPaso === 1) {
        $tmp = $_POST;
        $tmp['razon_social'] = to_upper($tmp['razon_social'] ?? '');
        $_SESSION['registro']['empresa'] = $tmp;
    } elseif ($currentPaso === 2) {
        $_SESSION['registro']['direccion'] = $_POST;
    } elseif ($currentPaso === 3) {
        $tmp = $_POST;
        $tmp['apellido'] = to_upper($tmp['apellido'] ?? '');
        $tmp['nombre']   = to_upper($tmp['nombre'] ?? '');
        $_SESSION['registro']['persona'] = $tmp;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true]);
    exit;
}

$error            = '';
$alertType        = ''; // 'error' | 'info' para SweetAlert
$errorCuit        = '';
$listaPaises      = $entityManager->getRepository(Paises::class)->findAll();
$listaProvincias  = $entityManager->getRepository(Provincias::class)->findAll();

// Determinar paso actual
if (!isset($paso)) {
    if ($getPaso !== null) {
        $p = $getPaso;
        if ($p === 1 || ($p === 2 && isset($_SESSION['registro']['empresa'])) ||
            ($p === 3 && isset($_SESSION['registro']['empresa'], $_SESSION['registro']['direccion']))) {
            $paso = $p;
        } else {
            $paso = 1;
        }
    } else {
        $paso = (int)($_POST['paso'] ?? 1);
    }
}

// === Procesar POST de los pasos ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_only'])) {

    if ($paso === 1) {
        // Paso 1: Validar CUIT y datos de empresa
        $rawCuit       = $_POST['cuit'] ?? '';
        $cleanCuit     = preg_replace('/\D/', '', $rawCuit);
        $empresaIdPost = $_POST['empresa_id'] ?? null;

        // Normalizar a MAYÚSCULAS la razón social
        $_POST['razon_social'] = to_upper($_POST['razon_social'] ?? '');

        // Empresa previa en sesión (para detectar cambios)
        $prevEmpresa   = $_SESSION['registro']['empresa'] ?? [];
        $prevEmpresaId = $prevEmpresa['empresa_id'] ?? null;
        $prevCuit      = preg_replace('/\D/','', $prevEmpresa['cuit'] ?? '');

        if (!validarCuit($cleanCuit)) {
            $errorCuit = 'Por favor ingrese un CUIT válido.';
            $_SESSION['registro']['empresa'] = $_POST;
            $paso = 1;

        } else {
            // Reglas de duplicados y obligatorios
            $rs   = norm($_POST['razon_social'] ?? '');
            $iva  = norm($_POST['cod_cond_iva'] ?? '');
            $mail = norm($_POST['email_empresa'] ?? '');

            if ($rs === '' || $iva === '' || $mail === '' || !isValidEmail($mail)) {
                $error = $mail && !isValidEmail($mail)
                    ? 'El Email Empresa no tiene un formato válido.'
                    : 'Completá Razón Social, Condición de IVA y Email Empresa.';
                $alertType = 'info';
                $_SESSION['registro']['empresa'] = $_POST;
                $paso = 1;

            } else {
                // Si no hay empresa_id, controlar duplicados por CUIT/Email
                if (empty($empresaIdPost)) {
                    $repoEmp  = $entityManager->getRepository(Empresas::class);
                    $dupCuit  = $repoEmp->findOneBy(['cuit'  => $cleanCuit]);
                    $dupEmail = $repoEmp->findOneBy(['email' => $mail]);
                    if ($dupCuit) {
                        $error = 'Ya existe una empresa registrada con ese CUIT.';
                        $alertType = 'error';
                        $_SESSION['registro']['empresa'] = $_POST;
                        $paso = 1;
                    } elseif ($dupEmail) {
                        $error = 'Ya existe una empresa registrada con ese email.';
                        $alertType = 'error';
                        $_SESSION['registro']['empresa'] = $_POST;
                        $paso = 1;
                    } else {
                        // Guardar empresa en sesión con CUIT limpio
                        $_SESSION['registro']['empresa'] = array_merge($_POST, ['cuit' => $cleanCuit]);

                        // Detectar cambio de empresa (respecto a lo previo en sesión)
                        $empresaCambio = false;
                        if (!empty($prevEmpresa)) {
                            $empresaCambio = (
                                (string)$prevEmpresaId !== (string)$empresaIdPost ||
                                (string)$prevCuit      !== (string)$cleanCuit
                            );
                        }
                        if ($empresaCambio) {
                            unset($_SESSION['registro']['direccion'], $_SESSION['registro']['persona']);
                        }
                        $paso = 2;
                    }
                } else {
                    // Con empresa existente (empresa_id dado) → guardar y limpiar pasos siguientes SOLO si cambió empresa
                    $_SESSION['registro']['empresa'] = array_merge($_POST, ['cuit' => $cleanCuit]);

                    $empresaCambio = false;
                    if (!empty($prevEmpresa)) {
                        $empresaCambio = (
                            (string)$prevEmpresaId !== (string)$empresaIdPost ||
                            (string)$prevCuit      !== (string)$cleanCuit
                        );
                    }
                    if ($empresaCambio) {
                        unset($_SESSION['registro']['direccion'], $_SESSION['registro']['persona']);
                    }
                    $paso = 2;
                }
            }
        }

    } elseif ($paso === 2) {
    $pais   = (int)($_POST['pais'] ?? 0);
    $prov   = (int)($_POST['provincia'] ?? 0);
    $locId  = (int)($_POST['localidad_id'] ?? 0);
    $cp     = norm($_POST['cp'] ?? '');

    // Guardar en sesión
    $_SESSION['registro']['direccion'] = $_POST;

    $esArgentina = ($pais === 1);

    // Validación base
    if ($pais <= 0 || $prov <= 0 || $cp === '') {
        $error = 'Completá País, Provincia y Código Postal.';
        $alertType = 'info';
        $paso = 2;
    } else {
        // Validar provincia existe y pertenece al país seleccionado
        $provincia = $entityManager->getRepository(Provincias::class)->find($prov);
        if (!$provincia || (int)$provincia->getPais()->getId() !== $pais) {
            $error = 'Provincia inválida para el país seleccionado.';
            $alertType = 'error';
            $paso = 2;
        } else {
            if ($esArgentina) {
                // Argentina: localidad obligatoria y debe pertenecer a provincia
                if ($locId <= 0) {
                    $error = 'Completá Localidad (seleccionándola de la lista).';
                    $alertType = 'info';
                    $paso = 2;
                } else {
                    $locEnt = $entityManager->getRepository(Localidades::class)->find($locId);
                    if (!$locEnt || (int)$locEnt->getProvincia()->getId() !== (int)$provincia->getId()) {
                        $error = 'Localidad inválida para la provincia seleccionada.';
                        $alertType = 'error';
                        $paso = 2;
                    } else {
                        $_SESSION['registro']['direccion']['localidad_nombre'] = $locEnt->getNombre();
                        $paso = 3;
                    }
                }
            } else {
                // Otros países: localidad debe ser NULL
                $_SESSION['registro']['direccion']['localidad_id'] = '';
                $_SESSION['registro']['direccion']['localidad_nombre'] = '';
                $paso = 3;
            }
        }
    }


    } elseif ($paso === 3) {
        // Paso 3: Crear usuarios y completar empresa/dirección

        // Normalizar NOMBRE/APELLIDO a MAYÚSCULAS en servidor
        $_POST['apellido'] = to_upper($_POST['apellido'] ?? '');
        $_POST['nombre']   = to_upper($_POST['nombre'] ?? '');

        $pwd = $_POST['password'] ?? '';
        $cpw = $_POST['confirm_password'] ?? '';

        $apellido = norm($_POST['apellido'] ?? '');
        $nombre   = norm($_POST['nombre'] ?? '');
        $genero   = norm($_POST['genero'] ?? '');
        $dni      = norm($_POST['dni'] ?? '');
        $tel      = norm($_POST['telefono'] ?? '');
        $emailUsr = norm($_POST['email'] ?? '');

        $faltantes = [];
        if ($apellido === '') $faltantes[] = 'Apellido';
        if ($nombre === '')   $faltantes[] = 'Nombre';
        if ($genero === '')   $faltantes[] = 'Género';
        if ($dni === '')      $faltantes[] = 'DNI';
        if ($tel === '')      $faltantes[] = 'Teléfono';
        if ($emailUsr === '') $faltantes[] = 'Email Personal';

        if (!empty($faltantes)) {
            $error = 'Completá: ' . implode(', ', $faltantes) . '.';
            $alertType = 'info';
            $paso  = 3;
        } elseif (!ctype_digit($dni)) {
            $error = 'El DNI debe ser numérico.';
            $alertType = 'info';
            $paso  = 3;
        } elseif (!isValidEmail($emailUsr)) {
            $error = 'El Email Personal no tiene un formato válido.';
            $alertType = 'info';
            $paso  = 3;
        } elseif (!isStrongPassword($pwd)) {
            $error = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
            $alertType = 'error';
            $paso  = 3;
        } elseif ($pwd !== $cpw) {
            $error = "Las contraseñas no coinciden.";
            $alertType = 'error';
            $paso  = 3;
        } else {
            $_SESSION['registro']['persona'] = $_POST;
            $datos = array_merge(
                $_SESSION['registro']['empresa'] ?? [],
                $_SESSION['registro']['direccion'] ?? [],
                $_SESSION['registro']['persona'] ?? []
            );

            try {
                // Verificar email empresa ≠ email usuario
                $emailEmpresa = norm($datos['email_empresa'] ?? '');
                $emailUsuario = norm($datos['email'] ?? '');
                if ($emailEmpresa !== '' && $emailUsuario !== '' && strcasecmp($emailEmpresa, $emailUsuario) === 0) {
                    throw new \Exception("El email de la empresa no puede ser el mismo que el email del usuario.");
                }

                // Verificar duplicados de email y DNI
                $repoPers = $entityManager->getRepository(Personas::class);
                if ($repoPers->findOneBy(['email' => $datos['email'] ?? ''])) {
                    throw new \Exception("El email personal ya está registrado.");
                }
                if ($repoPers->findOneBy(['dni' => $datos['dni'] ?? ''])) {
                    throw new \Exception("El DNI ya está registrado en el sistema.");
                }

                // Carga País/Provincia
                $paisId    = (int)($datos['pais'] ?? 0);
                $provId    = (int)($datos['provincia'] ?? 0);
                $locId = (int)($datos['localidad_id'] ?? 0);

                $esArgentina = ($paisId === 1);

                if ($paisId <= 0 || $provId <= 0 || ($esArgentina && $locId <= 0)) {
                    $error = "Faltan País/Provincia" . ($esArgentina ? "/Localidad" : "") . ". Por favor completá estos datos.";
                    $alertType = 'info';
                    $paso  = 2;
                    throw new \Exception("__VOLVER_PASO_2__");
                }

                $pais      = $entityManager->getRepository(Paises::class)->find($paisId);
                $provincia = $entityManager->getRepository(Provincias::class)->find($provId);
                
                if (!$pais || !$provincia || (int)$provincia->getPais()->getId() !== $paisId) {
                    $error = "País/Provincia inválidos. Revisá los datos.";
                    $alertType = 'info';
                    $paso  = 2;
                    throw new \Exception("__VOLVER_PASO_2__");
                }
                
                $locEnt = null;
                if ($esArgentina) {
                    $locEnt = $entityManager->getRepository(Localidades::class)->find($locId);
                    if (!$locEnt || (int)$locEnt->getProvincia()->getId() !== (int)$provincia->getId()) {
                        $error = "Provincia/Localidad inválidas. Revisá los datos.";
                        $alertType = 'info';
                        $paso  = 2;
                        throw new \Exception("__VOLVER_PASO_2__");
                    }
                }

                
                // =======================
                // 1) Cargar o crear EMPRESA
                // =======================
                
                $empresaId   = (int)($datos['empresa_id'] ?? 0);
                $esExistente = ($empresaId > 0);
                
                if ($esExistente) {
                    $empresa = $entityManager->getRepository(Empresas::class)->find($empresaId);
                    if (!$empresa) {
                        throw new \Exception("Empresa no encontrada.");
                    }
                
                    // ====== REGLA: si es empresa vieja que se modifica => estado = 2 ======
                    if (method_exists($empresa, 'setEstado')) {
                        $empresa->setEstado(2);
                    }
                
                    // IMPORTANTE: NO tocar fecha_inicial / fecha_alta en ediciones
                } else {
                    $empresa = new Empresas();
                
                    // ====== REGLA: empresa nueva online => estado = 3 ======
                    if (method_exists($empresa, 'setEstado')) {
                        $empresa->setEstado(3);
                    }
                
                    // Fechas SOLO en alta
                    $hoy = new \DateTimeImmutable('today');
                    if (method_exists($empresa, 'setFechaInicial')) $empresa->setFechaInicial($hoy);
                    if (method_exists($empresa, 'setFechaAlta'))    $empresa->setFechaAlta($hoy);
                }
                
                // Setear datos básicos empresa (ajustá setters según tu Entity)
                $empresa->setRazonSocial(to_upper($datos['razon_social'] ?? ''));
                $empresa->setCuit($datos['cuit'] ?? null);
                $empresa->setCodCondIVA($datos['cod_cond_iva'] ?? null);
                $empresa->setEmail($datos['email_empresa'] ?? null);
                $empresa->setNumTel($datos['telefono_empresa'] ?? null);
                
                $entityManager->persist($empresa);
                $entityManager->flush(); // para tener $empresa->getId() sí o sí
                
                // =======================
                // 2) Cargar o crear DIRECCIÓN (SIN romper entidades)
                // =======================
                $dirExistente = null;
                
                if (method_exists($empresa, 'getDirecciones')) {
                    $col = $empresa->getDirecciones();
                    if ($col && $col->count() > 0) {
                        $dirExistente = $col->first();
                        if ($dirExistente === false) $dirExistente = null;
                    }
                }
                
                // Si no hay dirección, creamos una nueva y la vinculamos desde el lado Empresa
                if ($dirExistente) {
                    $dir = $dirExistente;
                } else {
                    $dir = new Direcciones();
                    // IMPORTANTE: esto setea empresa en Dirección y mantiene la colección sincronizada
                    $empresa->addDireccion($dir);
                }
                
                // Domicilio: calle + número
                $domicilio = trim((string)($datos['calle'] ?? '') . ' ' . (string)($datos['numero'] ?? ''));
                
                // Setters de dirección (Direcciones es el owning side, pero addDireccion ya setea empresa)
                $dir->setPais($pais);
                $dir->setProvincia($provincia);
                $dir->setLocalidad($locEnt ?: null);
                $dir->setDomicilio($domicilio !== '' ? $domicilio : null);
                $dir->setBarrio(($datos['barrio'] ?? '') !== '' ? $datos['barrio'] : null);
                $dir->setCp(($datos['cp'] ?? '') !== '' ? $datos['cp'] : null);
                $dir->setObservaciones(($datos['observaciones'] ?? '') !== '' ? $datos['observaciones'] : null);
                
                // Persist (si ya está managed, no pasa nada; si es nueva, queda persistida)
                $entityManager->persist($empresa);
                $entityManager->persist($dir);
                $entityManager->flush();

                
                // =========================================================
                // SGA: Sync empresa (pfj=1) - NO interrumpe el registro si falla
                // =========================================================
                try {
                    // Domicilio y CP: en tu sistema pueden venir vacíos,
                    // pero SGA los valida -> mandamos placeholder
                    $domicilioSga = trim((string)$domicilio);
                    if ($domicilioSga === '') {
                        $domicilioSga = 'S/D'; // sin domicilio
                    }
                
                    $cpSga = trim((string)($datos['cp'] ?? ''));
                    if ($cpSga === '') {
                        $cpSga = '0'; // o 'S/CP' si SGA acepta string; si no, dejar '0'
                    }
                
                    // Barrio (si viene vacío, lo dejamos vacío)
                    $barrioSga = trim((string)($datos['barrio'] ?? ''));
                    
                    // Usar Provincia/Localidad elegidas en el registro (IDs de tu DB)
                    $codpciaSga = (int)$provincia->getId();  // Provincia seleccionada
                    $codlocSga = $locEnt ? (int)$locEnt->getId() : 0;     // Localidad seleccionada
                
                    $clienteDataSga = [
                        // Referencia local (para que SGA lo “encuentre” por id en futuras sync)
                        'id'         => (int)$empresa->getId(),
                        'codcli'     => "",
                
                        'pfj'        => 1,  // Empresa (Responsable Inscripto)
                
                        'rsocial'    => (string)$empresa->getRazonSocial(),
                        'cuit'       => (string)$empresa->getCuit(),
                        'codcondiva' => (string)$empresa->getCodCondIVA(),
                
                        'domicilio'  => $domicilioSga,
                        'telefono'   => (string)($empresa->getNumTel() ?? ''),
                        'celular'    => "",
                        'barrio'     => (string)($barrioSga ?? ''),
                        'mail'       => (string)$empresa->getEmail(),
                        'cp'         => (string)($cpSga ?? ''),
                
                        'codloc'     => (int)$codlocSga,  // Localidad
                        'codpcia'    => (int)$codpciaSga, // Provincia
                    ];
                    
                    sga_log('SGA empresa sync REQUEST', [
                        'empresa_id' => (int)$empresa->getId(),
                        'payload'    => $clienteDataSga,
                    ]);

                    $debugSga = null;
                    $codcli = syncClienteFacturacion($clienteDataSga, $debugSga);
                    
                    sga_log('SGA empresa /clientes RAW', [
                        'empresa_id' => (int)$empresa->getId(),
                        'http_code'  => $debugSga['code'] ?? null,
                        'raw'        => $debugSga['raw'] ?? null,
                        'body'       => $debugSga['body'] ?? null,
                    ]);

                    
                    sga_log('SGA empresa sync RESPONSE', [
                        'empresa_id' => (int)$empresa->getId(),
                        'codcli'     => $codcli,
                    ]);

                
                    // Log interno para verificar que sincronizó
                    sga_log('SGA empresa sync OK', [
                        'empresa_id' => (int)$empresa->getId(),
                        'codcli'     => $codcli,
                        'mail'       => (string)$empresa->getEmail(),
                        'cuit'       => (string)$empresa->getCuit(),
                    ]);
                
                    if (!empty($codcli)) {
                        $empresa->setCodCliente((string)$codcli);  // <-- ESTE ES EL CAMBIO CLAVE
                        $entityManager->persist($empresa);
                        $entityManager->flush();
                    
                        sga_log('SGA empresa codcli guardado OK', [
                            'empresa_id'  => (int)$empresa->getId(),
                            'cod_cliente' => (string)$empresa->getCodCliente(),
                        ]);
                    } else {
                        sga_log('SGA empresa sync OK pero sin codcli', [
                            'empresa_id' => (int)$empresa->getId(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    sga_log('SGA empresa sync ERROR', [
                        'empresa_id' => isset($empresa) ? (int)$empresa->getId() : null,
                        'error'      => $e->getMessage(),
                    ]);
                }

                // ¿Es el primer usuario de la empresa?
                $existeAlguno = $repoPers->findOneBy(['empresa' => $empresa]);
                if (!$existeAlguno) {
                    // Crear Usuario Administrador (rol=1) con credenciales aleatorias
                    $razon = (string)$empresa->getRazonSocial();
                    $parts = explode(' ', trim($razon), 2);
                    $nombreEmp   = $parts[0] ?? '';
                    $apellidoEmp = $parts[1] ?? '';

                    $chars     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    $adminPass = '';
                    for ($i = 0; $i < 8; $i++) $adminPass .= $chars[random_int(0, strlen($chars) - 1)];

                    $admin = new Personas();
                    $admin->setNombre($nombreEmp);
                    $admin->setApellido($apellidoEmp);
                    $admin->setGenero(null);
                    $admin->setDni(null);
                    $admin->setEmail($empresa->getEmail());
                    $admin->setNumTel($empresa->getNumTel());
                    $admin->setPass(password_hash($adminPass, PASSWORD_DEFAULT));
                    $admin->setEmpresa($empresa);
                    $admin->setTokenOpt(generarTokenOPT());
                    $admin->setRol(1);            // administrador
                    $admin->setEstadoPersona(1);  // activo
                    $entityManager->persist($admin);

                    // Enviar mail con credenciales Admin
                    $mailAdmin = new PHPMailer(true);
                    try {
                        $mailAdmin->isSMTP();
                        $mailAdmin->Host       = 'mail.malkoni.com.ar';
                        $mailAdmin->SMTPAuth   = true;
                        $mailAdmin->Username   = 'no-reply@online.malkoni.com.ar';
                        $mailAdmin->Password   = '#$Mcp4n3lI$#';
                        $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mailAdmin->Port       = 587;
                        $mailAdmin->CharSet    = 'UTF-8';
                        $mailAdmin->Encoding   = 'base64';
                        $mailAdmin->setFrom('no-reply@online.malkoni.com.ar','Malkoni Hnos');
                        $mailAdmin->addAddress($empresa->getEmail());
                        $mailAdmin->isHTML(true);
                        $mailAdmin->Subject = 'Credenciales de Administrador – Malkoni Hnos';
                        $mailAdmin->Body = "
                        <div style='background:#f4f4f4;padding:20px;font-family:sans-serif;'>
                          <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;'>
                            <div style='background:#166379;color:#fff;padding:20px;text-align:center;'>
                              <h2>Usuario Administrador Creado</h2>
                              <p style='margin:0;font-size:1rem;'>Empresa: <strong>{$empresa->getRazonSocial()}</strong></p>
                            </div>
                            <div style='padding:30px;color:#333; text-align:left;'>
                              <p>Se ha generado un usuario administrador para su empresa con los siguientes datos:</p>
                              <ul>
                                <li><strong>Email:</strong> {$empresa->getEmail()}</li>
                                <li><strong>Contraseña:</strong> {$adminPass}</li>
                              </ul>
                              <p>Con este usuario podrá:</p>
                              <ul>
                                <li>Crear, eliminar y modificar el estado de los usuarios de la empresa.</li>
                                <li>Ingresar al sistema de optimización de cortes.</li>
                                <li>Ingresar al sistema de pedidos online.</li>
                                <li>Ingresar al sistema de ecommerce.</li>
                              </ul>
                              <p style='text-align:center;margin-top:30px;'>
                                <a href='https://online.malkoni.com.ar/public/login.php'
                                   style='display:inline-block;padding:12px 24px;
                                          background:#D88429;color:#fff;border-radius:30px;
                                          text-decoration:none;font-weight:bold;'>
                                  Ir al Login
                                </a>
                              </p>
                            </div>
                            <div style='background:#f1f1f1;color:#888;padding:10px;text-align:center;font-size:0.8em;'>
                              © Malkoni Hnos
                            </div>
                          </div>
                        </div>";
                        $mailAdmin->send();
                    } catch (Exception $e) {
                        error_log("MailAdmin error: " . $mailAdmin->ErrorInfo);
                    }
                }

                // Crear Operario (rol=2) con datos del formulario — Nombre/Apellido en MAYÚSCULAS
                $operario = new Personas();
                $operario->setNombre(to_upper($datos['nombre'] ?? null));
                $operario->setApellido(to_upper($datos['apellido'] ?? null));
                $operario->setGenero($datos['genero'] ?? null);
                $operario->setDni((int)($datos['dni'] ?? 0));
                $operario->setEmail($datos['email'] ?? null);
                $operario->setNumTel($datos['telefono'] ?? null);
                $operario->setPass(password_hash($datos['password'], PASSWORD_DEFAULT));
                $operario->setEmpresa($empresa);
                $operario->setTokenOpt(generarTokenOPT());
                $operario->setRol(2);             // operario
                $operario->setEstadoPersona(1);   // activo
                $entityManager->persist($operario);
                
                // vincular empresa-persona SOLO para rol=2
                $empPer = new EmpresasPersonas();
                $empPer->setEmpresa($empresa);
                $empPer->setPersona($operario);
                $empPer->setEstado(1); // opcional (ya default=1)
                $entityManager->persist($empPer);

                // Token de validación para la empresa + envío mail de validación al operario
                $token = bin2hex(random_bytes(16));
                if (method_exists($empresa, 'setValidacionToken')) {
                    $empresa->setValidacionToken($token);
                }
                $entityManager->flush();

                $mail = new PHPMailer(true);
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
                    $mail->addAddress($operario->getEmail());
                    $mail->isHTML(true);
                    $mail->Subject = 'Validación de cuenta - Malkoni Hnos';
                    $link = "https://online.malkoni.com.ar/validar_usuario_mail.php?token={$token}";
                    $mail->Body = "
                      <div style='background:#f4f4f4;padding:20px;font-family:sans-serif;'>
                        <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;'>
                          <div style='background:#166379;color:#fff;padding:20px;text-align:center;'>
                            <h2>Bienvenido a Malkoni Hnos</h2>
                          </div>
                          <div style='padding:30px;text-align:center;color:#333;'>
                            <p style='font-size:1rem;'>¡Gracias por registrarte! Para poder ingresar, debes validar tu cuenta:</p>
                            <a href='{$link}' style='display:inline-block;padding:12px 24px;
                               background:#D88429;color:#fff;border-radius:30px;text-decoration:none;font-weight:bold;'>
                              Validar cuenta
                            </a>
                            <p style='margin-top:20px;color:#333;'><strong>Gracias por usar los servicios online de Malkoni Hnos.</strong></p>
                          </div>
                          <div style='background:#f1f1f1;color:#888;padding:10px;text-align:center;font-size:0.8em;'>
                            © Malkoni Hnos
                          </div>
                        </div>
                      </div>";
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mail error: " . $mail->ErrorInfo);
                }

                // Fin y redirección
                session_destroy();
                $emailPersonal = urlencode($datos['email'] ?? '');
                header("Location: login.php?registro=ok&email={$emailPersonal}");
                exit;

            } catch (\Exception $e) {
                if ($e->getMessage() !== "__VOLVER_PASO_2__") {
                    $error = "¡Error inesperado! " . $e->getMessage();
                    $alertType = 'error';
                }
                if ($e->getMessage() !== "__VOLVER_PASO_2__" && $paso !== 2) {
                    $paso = 3;
                }
            }
        }
    }
}

// Precarga paso 2 si falta en sesión
if ($paso === 2 && !isset($_SESSION['registro']['direccion'])) {
    $eid = $_SESSION['registro']['empresa']['empresa_id'] ?? null;
    if ($eid) {
        $empresa = $entityManager->getRepository(Empresas::class)->find($eid);
        if ($empresa) {
            $d   = method_exists($empresa, 'getDirecciones') ? ($empresa->getDirecciones()->first() ?: null) : null;
            $loc = $d ? $d->getLocalidad() : null;
            if ($d) {
                // Extraer calle y número del domicilio
                $dom = $d->getDomicilio() ?: '';
                if (preg_match('/^(.*)\s+(\S+)$/', $dom, $m)) {
                    $calle  = $m[1];
                    $numero = $m[2];
                } else {
                    $calle  = $dom;
                    $numero = '';
                }
                $prov = $d->getProvincia();
                $_SESSION['registro']['direccion'] = [
                    'pais'          => ($d->getPais()) ? $d->getPais()->getId() : (($prov && method_exists($prov, 'getPais') && $prov->getPais()) ? $prov->getPais()->getId() : ''),
                    'provincia'     => $prov ? $prov->getId() : '',
                    'localidad_id'     => $loc ? $loc->getId() : '',
                    'localidad_nombre' => $loc ? $loc->getNombre() : '',
                    'barrio'        => $d->getBarrio(),
                    'calle'         => $calle,
                    'numero'        => $numero,
                    'cp'            => $d->getCp(),
                    'observaciones' => $d->getObservaciones() ?? '',
                ];
            }
        }
    }
}

// === Calcular $maxStep visible para el front ===
$maxStep = 1;
if (!empty($_SESSION['registro']['empresa'])) {
    $e = $_SESSION['registro']['empresa'];
    $hasEmpresa = norm($e['razon_social'] ?? '') !== '' &&
                  norm($e['cod_cond_iva'] ?? '') !== '' &&
                  isValidEmail($e['email_empresa'] ?? '') &&
                  validarCuit(preg_replace('/\D/','', $e['cuit'] ?? ''));
    if ($hasEmpresa) $maxStep = 2;
}
if (!empty($_SESSION['registro']['direccion'])) {
    $d = $_SESSION['registro']['direccion'];
    $paisIdDir = (int)($d['pais'] ?? 0);
    $needsLoc  = ($paisIdDir === 1);
    
    $hasDir = ($paisIdDir > 0) &&
              ((int)($d['provincia'] ?? 0) > 0) &&
              (!$needsLoc || ((int)($d['localidad_id'] ?? 0) > 0)) &&
              norm($d['cp'] ?? '') !== '';
    if ($hasDir) $maxStep = 3;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Malkoni Hnos - Servicios Online</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="styles/registroStyles.css?v=<?= time() ?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container">
 <div class="left-panel">
  <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
  <h1 style="font-family:'Syncopate',sans-serif;font-weight:700;color:#E1DFD9;font-size:2.7rem;">Empresa</h1>
  <p style="font-family:'Syncopate',sans-serif;font-weight:300;color:#E1DFD9;font-size:1rem;">Completa los datos de tu empresa</p>
  <?php if (!empty($_SESSION['registro']['empresa']['razon_social'])): ?>
    <p style="color:#E1DFD9;font-size:1.5rem;margin-top:5px;font-weight:600;">
      <?= htmlspecialchars($_SESSION['registro']['empresa']['razon_social'], ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>
</div>

  
    <div class="right-panel">
    <div class="step-indicator" data-current-step="<?= (int)$paso ?>" data-max-step="<?= (int)$maxStep ?>">
      <a href="registro.php?paso=1" class="step <?= $paso===1?'active':($paso>1?'completed':'') ?>" data-step="1">1</a>
      <a href="registro.php?paso=2" class="step <?= $paso===2?'active':($paso>2?'completed':'') ?>" data-step="2">2</a>
      <a href="registro.php?paso=3" class="step <?= $paso===3?'active':'' ?> <?= (3>$maxStep)?'locked':'' ?>" data-step="3">3</a>
    </div>

    <div class="form-header">
      <?php if ($paso===1): ?>
        <h1 class="form-title">Datos de la Empresa</h1>
        <p class="form-subtitle">Completa la información básica de tu empresa.</p>
      <?php elseif ($paso===2): ?>
        <h1 class="form-title">Dirección</h1>
        <p class="form-subtitle">Ingresa la dirección de la empresa.</p>
      <?php else: ?>
        <h1 class="form-title">Datos Personales</h1>
        <p class="form-subtitle">Completa los datos del representante.</p>
      <?php endif; ?>
    </div>

    <div class="form-container">
      <?php if ($paso == 1):
          $v = array_merge($_GET, $_SESSION['registro']['empresa'] ?? []);
          $readonlyCuit = !empty($v['empresa_id'])
                          ? 'readonly style="background:#e9ecef; cursor:not-allowed;"'
                          : '';
      ?>
      <form method="post" action="registro.php" autocomplete="off" id="form-paso1">
        <input type="hidden" name="paso" value="1">
        <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($v['empresa_id'] ?? '') ?>">

        <div class="form-group">
          <label class="form-label">Razón Social <span style="color:red">*</span></label>
          <input class="form-input1" name="razon_social" type="text" required data-uppercase
                 value="<?= htmlspecialchars($v['razon_social'] ?? '') ?>">
        </div>

        <div class="form-group" style="position: relative;">
          <label class="form-label">CUIT <span style="color:red">*</span></label>
          <input id="cuit-input" class="form-input1" name="cuit" type="text" required
                 placeholder="ej. 20-12345678-6"
                 value="<?= htmlspecialchars($v['cuit'] ?? '') ?>"
                 <?= $readonlyCuit ?>>
          <span id="cuit-feedback"
                style="display:none; position:absolute; right:10px; top:36px; color:green; font-weight:bold;">
            ✓ CUIT válido
          </span>
          <?php if (!empty($errorCuit)): ?>
            <div class="error-message" style="color:red; margin-top:4px;">
              <?= htmlspecialchars($errorCuit) ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Condición de IVA <span style="color:red">*</span></label>
          <select class="form-input1" name="cod_cond_iva" required>
            <option value="">--Seleccione--</option>
            <option value="MT" <?= ($v['cod_cond_iva'] ?? '')==='MT'?'selected':'' ?>>Responsable Monotributista</option>
            <option value="RI" <?= ($v['cod_cond_iva'] ?? '')==='RI'?'selected':'' ?>>Responsable Inscripto</option>
            <option value="EX" <?= ($v['cod_cond_iva'] ?? '')==='EX'?'selected':'' ?>>Exento</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Email Empresa <span style="color:red">*</span></label>
          <input class="form-input1" name="email_empresa" type="email" required
                 value="<?= htmlspecialchars($v['email_empresa'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Teléfono Empresa</label>
          <input class="form-input1" name="telefono_empresa" type="tel"
                 value="<?= htmlspecialchars($v['telefono_empresa'] ?? '') ?>">
        </div>
        
        <div class="btn-group">
          <button type="button" onclick="saveAndGo(1, 'tipo_identidad.php')" class="submit-btn back">Volver</button>
          <button type="submit" class="submit-btn">Siguiente</button>
        </div>
      </form>

      <?php elseif ($paso == 2):
          $hasDireccion = isset($_SESSION['registro']['direccion']);
          $v = $hasDireccion
             ? $_SESSION['registro']['direccion']
             : ['pais'=>'','provincia'=>'','localidad'=>'','barrio'=>'','calle'=>'','numero'=>'','cp'=>'','observaciones'=>''];
      ?>
      <form method="post" action="registro.php" autocomplete="off" id="form-paso2">
        <input type="hidden" name="paso" value="2">
        <div class="two-columns-form">
          <div class="column">
            <div class="form-group">
              <label class="form-label">País <span style="color:red">*</span></label>
              <select id="pais-select" class="form-input" name="pais" required>
                <option value="">--Seleccione País--</option>
                <?php foreach($listaPaises as $p): ?>
                  <option value="<?= $p->getId() ?>" <?= (string)($v['pais'] ?? '')===(string)$p->getId()?'selected':'' ?>>
                    <?= htmlspecialchars($p->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Provincia <span style="color:red">*</span></label>
              <select id="provincia-select" class="form-input" name="provincia" required>
                <option value="">--Seleccione Provincia--</option>
                <?php foreach($listaProvincias as $prov): ?>
                  <option value="<?= $prov->getId() ?>" data-pais="<?= $prov->getPais()->getId() ?>" <?= (string)($v['provincia'] ?? '')===(string)$prov->getId()?'selected':'' ?>>
                    <?= htmlspecialchars($prov->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

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
              value="<?= htmlspecialchars($v['localidad_nombre'] ?? ($v['localidad'] ?? '')) ?>"
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

            <div class="form-group">
              <label class="form-label">Código Postal <span style="color:red">*</span></label>
              <input class="form-input" name="cp" type="text" placeholder="Ej. 5000" value="<?= htmlspecialchars($v['cp'] ?? '') ?>" required>
            </div>
          </div>

          <div class="column">
            <div class="form-group">
              <label class="form-label">Barrio</label>
              <input class="form-input" name="barrio" type="text" placeholder="Ingrese barrio (opcional)" value="<?= htmlspecialchars($v['barrio'] ?? '') ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Calle</label>
              <input class="form-input" name="calle" type="text" placeholder="Ingrese calle (opcional)" value="<?= htmlspecialchars($v['calle'] ?? '') ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Número</label>
              <input class="form-input" name="numero" type="text" placeholder="Ej. 123 (opcional)" value="<?= htmlspecialchars($v['numero'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="form-group full-width">
          <label class="form-label">Observaciones</label>
          <textarea name="observaciones" rows="2" class="form-input3" placeholder="Manzana, Lote, Descripción, Referencias, etc." data-uppercase><?= htmlspecialchars($v['observaciones'] ?? '') ?></textarea>
        </div>

        <div class="btn-group">
          <button type="button" onclick="saveAndGo(2, 'registro.php?paso=1')" class="submit-btn back">Volver</button>
          <button type="submit" class="submit-btn">Siguiente</button>
        </div>
      </form>

      <?php else:
          $v = $_SESSION['registro']['persona'] ?? [];
      ?>
      <form method="post" action="registro.php" id="form-paso3" autocomplete="off">
        <input type="hidden" name="paso" value="3">
        <div class="two-columns-form">
          <div class="column">
            <div class="form-group">
              <label class="form-label">Apellido <span style="color:red">*</span></label>
              <input class="form-input" name="apellido" type="text" required value="<?= htmlspecialchars($v['apellido'] ?? '') ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Nombre <span style="color:red">*</span></label>
              <input class="form-input" name="nombre" type="text" required value="<?= htmlspecialchars($v['nombre'] ?? '') ?>" data-uppercase>
            </div>
            <div class="form-group">
              <label class="form-label">Género de nacimiento <span style="color:red">*</span></label>
              <select class="form-input" name="genero" required>
                <option value="">--Seleccione--</option>
                <option value="M" <?= ($v['genero'] ?? '')==='M'?'selected':'' ?>>Masculino</option>
                <option value="F" <?= ($v['genero'] ?? '')==='F'?'selected':'' ?>>Femenino</option>
              </select>
            </div>
          </div>
          <div class="column">
            <div class="form-group">
              <label class="form-label">DNI <span style="color:red">*</span></label>
              <input class="form-input" name="dni" type="text" required value="<?= htmlspecialchars($v['dni'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Teléfono <span style="color:red">*</span></label>
              <input class="form-input" name="telefono" type="tel" required value="<?= htmlspecialchars($v['telefono'] ?? '') ?>">
            </div>
          </div>
        </div>

        <h2 class="section-title" style="font-family:'Syncopate',sans-serif;font-weight:700;color:#166379;font-size:1.7rem;margin-bottom:6px;">
          Datos de acceso al sistema
        </h2>

        <div class="form-container">
          <div class="form-group">
            <label class="form-label">Email Personal <span style="color:red">*</span></label>
            <input class="form-input" name="email" type="email" required value="<?= htmlspecialchars($v['email'] ?? '') ?>">
            <small style="display:block;margin-top:4px;color:#6c757d;">
              Debe ser distinto del Email Empresa.
            </small>
          </div>

          <div class="password-group">

              <div style="flex:1;">
                <div class="form-group password-wrapper">
                  <label class="form-label">Contraseña <span style="color:red">*</span></label>
                  <input id="password" class="form-input" name="password" type="password" required
                         pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}"
                         title="8+ caracteres, con mayúscula, minúscula y número">
                  <button type="button" class="toggle-password" tabindex="-1">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
            
                <div class="form-group password-wrapper">
                  <label class="form-label">Confirmar Contraseña <span style="color:red">*</span></label>
                  <input id="confirm_password" class="form-input" name="confirm_password" type="password" required>
                  <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>
            
              <div class="password-box" id="password-box">
                <h4>Requisitos:</h4>
                <ul>
                  <li id="req-length"><i class="fa-solid fa-circle"></i> Mínimo 8 caracteres</li>
                  <li id="req-upper"><i class="fa-solid fa-circle"></i> 1 letra mayúscula</li>
                  <li id="req-lower"><i class="fa-solid fa-circle"></i> 1 letra minúscula</li>
                  <li id="req-number"><i class="fa-solid fa-circle"></i> 1 número</li>
                </ul>
                <div id="req-match" class="req-match" style="margin-top:10px; font-size:.9rem;">
                  <i class="fa-solid fa-circle"></i> Las contraseñas coinciden
                </div>
              </div>
            
            </div>

          <div class="btn-group">
            <button type="button" onclick="saveAndGo(3, 'registro.php?paso=2')" class="submit-btn back">Volver</button>
            <button type="submit" class="submit-btn">Finalizar Registro</button>
          </div>
        </div>
      </form>

      <script>
        // Validación de contraseña en el cliente + email empresa ≠ usuario
        document.getElementById('form-paso3').addEventListener('submit', function(e) {
          const pwd = this.password.value;
          const cpw = this.confirm_password.value;
          const re  = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
          if (!re.test(pwd)) {
            e.preventDefault();
            this.password.value = '';
            this.confirm_password.value = '';
            Swal.fire('Error', 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número', 'error');
            return;
          } else if (pwd !== cpw) {
            e.preventDefault();
            this.password.value = '';
            this.confirm_password.value = '';
            Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
            return;
          }
          const emailEmpresa = <?= json_encode($_SESSION['registro']['empresa']['email_empresa'] ?? '') ?>.toLowerCase();
          const emailUsuario = (this.email.value || '').toLowerCase();
          if (emailEmpresa && emailUsuario && emailEmpresa === emailUsuario) {
            e.preventDefault();
            Swal.fire('Error','El email del usuario no puede ser el mismo que el email de la empresa','error');
          }
        });
      </script>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Precarga de Provincia segun Pais -->
<script>
(function(){
  const paisSel = document.getElementById('pais-select');
  const provSel = document.getElementById('provincia-select');
  const locInp  = document.getElementById('localidad-input');
  const locId   = document.getElementById('localidad-id');
  const box     = document.getElementById('localidad-suggestions');

  if (!paisSel || !provSel) return;

  function hideBox(){
    if (!box) return;
    box.style.display = 'none';
    box.innerHTML = '';
  }

  function clearLoc(){
    if (locInp) locInp.value = '';
    if (locId)  locId.value  = '';
    hideBox();
  }

  function applyCountryFilter(){
    const paisId = parseInt(paisSel.value || '0', 10);

    // 1) Filtrar provincias por data-pais
    [...provSel.options].forEach(opt => {
      if (!opt.value) return; // placeholder
      const optPais = parseInt(opt.dataset.pais || '0', 10);
      opt.hidden = (paisId > 0 && optPais !== paisId);
    });

    // Si la provincia seleccionada no pertenece al país, resetear
    const selected = provSel.selectedOptions[0];
    if (selected && selected.value) {
      const selPais = parseInt(selected.dataset.pais || '0', 10);
      if (paisId > 0 && selPais !== paisId) {
        provSel.value = '';
      }
    }

    // 2) Localidad: sólo para Argentina (id=1)
    const esArgentina = (paisId === 1);

    if (!esArgentina) {
      // deshabilitar localidad y dejarla en NULL (vacío en form)
      if (locInp) {
        locInp.disabled = true;
        locInp.value = '';
      }
      if (locId) locId.value = '';
      clearLoc();
    } else {
      // Argentina: habilitar si hay provincia
      if (locInp) {
        locInp.disabled = !provSel.value;
      }
      clearLoc();
    }
    
    if (!esArgentina) {
      if (locId) locId.value = '';
      if (locId) locId.disabled = true;
    } else {
      if (locId) locId.disabled = false;
    }
  }

  paisSel.addEventListener('change', () => {
    applyCountryFilter();
    // al cambiar país, siempre limpiamos provincia y localidad
    provSel.value = '';
    clearLoc();
  });

  // al cargar: aplicar filtro inicial (por precarga)
  applyCountryFilter();
})();
</script>

<!-- Validación instantánea de CUIT -->
<script>
(function(){
  const input = document.getElementById('cuit-input');
  if (!input) return;
  const feedback = document.getElementById('cuit-feedback');
  function validarCuitJS(cuit) {
    if (cuit.length !== 11) return false;
    const digits = cuit.split('').map(d => parseInt(d,10));
    const mult = [5,4,3,2,7,6,5,4,3,2];
    let sum = 0;
    for (let i=0; i<10; i++) sum += digits[i] * mult[i];
    let mod = sum % 11;
    let expected = 11 - mod;
    if (expected === 11) expected = 0;
    else if (expected === 10) expected = 9;
    return digits[10] === expected;
  }
  input.addEventListener('input', function(){
    const clean = this.value.replace(/\D/g,'');
    feedback.style.display = validarCuitJS(clean) ? 'block' : 'none';
  });
})();
</script>

<!-- Navegación por pasos con verificación y AUTOSAVE a sesión -->
<script>
(function(){
  const cont = document.querySelector('.step-indicator');
  if (!cont) return;

  const currentStep = parseInt(cont.dataset.currentStep || '1', 10);
  const maxStep     = parseInt(cont.dataset.maxStep || '1', 10);

  function formForStep(step){
    if (step === 1) return document.getElementById('form-paso1');
    if (step === 2) return document.getElementById('form-paso2');
    if (step === 3) return document.getElementById('form-paso3');
    return null;
  }

  async function autosave(step, gotoStep) {
    const form = formForStep(step);
    if (!form) return true;
    const fd = new FormData(form);
    fd.append('save_only', '1');
    if (gotoStep) fd.append('goto_step', String(gotoStep));
    try {
      const res = await fetch('registro.php', { method:'POST', body: fd, credentials: 'same-origin' });
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
        const ok = await autosave(currentStep, target);
        window.location.href = 'registro.php?paso=' + target;
        return;
      }

      e.preventDefault();
      const form = formForStep(currentStep);
      if (!form) {
        window.location.href = 'registro.php?paso=' + target;
        return;
      }
      if (!form.checkValidity()) {
        Swal.fire({
          icon: 'info',
          title: 'Completá todos los campos',
          text: 'Para continuar, primero completá los campos obligatorios del paso actual.',
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
})();
</script>

<?php if ($error): ?>
<script>
  Swal.fire({
    icon: <?= json_encode($alertType === 'info' ? 'info' : 'error') ?>,
    title: '¡Atención!',
    text: <?= json_encode($error) ?>,
    confirmButtonColor: <?= json_encode($alertType === 'info' ? '#166379' : '#d33') ?>,
    confirmButtonText: 'Aceptar'
  });
</script>
<?php endif; ?>

<!-- SIEMPRE CARGADO: Toggle password + saveAndGo -->
<script>
// Toggle password con delegación global
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

// Guarda en sesión el formulario del paso actual y navega
window.saveAndGo = async function(currentStep, url){
  const form = document.getElementById('form-paso' + currentStep);
  if (form) {
    const fd = new FormData(form);
    fd.append('save_only', '1'); // canal de autosave del servidor
    try {
      await fetch('registro.php', { method:'POST', body: fd, credentials:'same-origin' });
    } catch(e) {
      console.error('Autosave error', e);
    }
  }
  window.location.href = url;
};
</script>

<script>
(function(){
  const provSel = document.getElementById('provincia-select');
  const locInp  = document.getElementById('localidad-input');
  const locId   = document.getElementById('localidad-id');
  const box     = document.getElementById('localidad-suggestions');
  const form2   = document.getElementById('form-paso2');

  if (!provSel || !locInp || !locId || !box || !form2) return;

  let lastItems = [];
  let t = null;

  function clearLoc(){
    locInp.value = '';
    locId.value  = '';
    hideBox();
  }

  function hideBox(){
    box.style.display = 'none';
    box.innerHTML = '';
  }

  function showItems(items){
    lastItems = items || [];
    if (!lastItems.length) { hideBox(); return; }

    box.innerHTML = lastItems.map(it => `
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
    const provId = parseInt(provSel.value || '0', 10);
    const q = (locInp.value || '').trim();

    // si cambia el texto manualmente => invalida selección
    locId.value = '';

    if (!provId || q.length < 2) { hideBox(); return; }

    const url = `registro.php?action=localidades&provincia_id=${encodeURIComponent(provId)}&q=${encodeURIComponent(q)}`;
    const res = await fetch(url, { credentials:'same-origin' });
    const data = await res.json();
    showItems((data && data.items) ? data.items : []);
  }

  // al cambiar provincia: habilitar input y limpiar localidad
    provSel.addEventListener('change', () => {
      const paisId = parseInt((document.getElementById('pais-select')?.value || '0'), 10);
      const esArgentina = (paisId === 1);
    
      if (provSel.value && esArgentina) {
        locInp.disabled = false;
        clearLoc();
        locInp.focus();
      } else {
        locInp.disabled = true;
        clearLoc();
      }
    });

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

  // VALIDACIÓN: no permitir submit si no eligió localidad_id
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
(function(){
  const passwordInput = document.getElementById('password');
  const box = document.getElementById('password-box');
  const reqLength = document.getElementById('req-length');
  const reqUpper  = document.getElementById('req-upper');
  const reqLower  = document.getElementById('req-lower');
  const reqNumber = document.getElementById('req-number');
  const reqMatch  = document.getElementById('req-match');
  const confirmInput = document.getElementById('confirm_password');


  if (!passwordInput || !confirmInput || !box || !reqLength || !reqUpper || !reqLower || !reqNumber || !reqMatch) return;

  function toggleRequirement(element, valid){
    const icon = element.querySelector('i');
    if(valid){
      element.classList.add('valid');
      if (icon) icon.className = "fa-solid fa-check";
    } else {
      element.classList.remove('valid');
      if (icon) icon.className = "fa-solid fa-circle";
    }
  }

    passwordInput.addEventListener('focus', () => box.classList.add('active'));
    confirmInput.addEventListener('focus', () => box.classList.add('active'));
    
    passwordInput.addEventListener('input', function () {
      const value = this.value || '';
    
      toggleRequirement(reqLength, value.length >= 8);
      toggleRequirement(reqUpper,  /[A-Z]/.test(value));
      toggleRequirement(reqLower,  /[a-z]/.test(value));
      toggleRequirement(reqNumber, /\d/.test(value));
    
      toggleRequirement(reqMatch, value !== '' && confirmInput.value === value);
    });
    
    confirmInput.addEventListener('input', function () {
      toggleRequirement(reqMatch, passwordInput.value !== '' && this.value === passwordInput.value);
    });


})();
</script>


</body>
</html>
