<?php
session_start();
// Sólo soporte puede entrar
if (
    empty($_SESSION['usuario']) ||
    $_SESSION['usuario'] !== 'soporte@online.malkoni.com.ar' ||
    $_SESSION['rol'] != 3
) {
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Entities\Empresas;
use Entities\Personas;
use Entities\Paises;
use Entities\Provincias;
use Entities\Localidades;
use Doctrine\ORM\EntityNotFoundException;

require_once __DIR__ . '/../../../vendor/autoload.php';
$entityManager     = require __DIR__ . '/../../../config/doctrine.php';

$listaPaises       = $entityManager->getRepository(Paises::class)->findAll();
$listaProvincias   = $entityManager->getRepository(Provincias::class)->findAll();
$listaLocalidades  = $entityManager->getRepository(Localidades::class)->findAll();

// Agrupar localidades por provincia (blindado)
$localidadesPorProvincia = [];
foreach ($listaLocalidades as $loc) {
    $prov = $loc->getProvincia();
    if (!$prov) { continue; }
    $pid = $prov->getId();
    $localidadesPorProvincia[$pid][] = [
        'id'     => $loc->getId(),
        'nombre' => $loc->getNombre()
    ];
}

// Parámetros de búsqueda, orden y paginación
$by        = $_GET['by'] ?? 'razon_social'; // NUEVO
$busqueda  = trim($_GET['q'] ?? '');
$order = $_GET['order'] ?? 'web_fecha_desc';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 20;

// Construyo QueryBuilder base
$qb = $entityManager
    ->getRepository(Empresas::class)
    ->createQueryBuilder('e');

// (Opcional) si querés que también encuentre la empresa por email/teléfono de sus usuarios asociados:
$qb->leftJoin('Entities\EmpresasPersonas', 'ep', 'WITH', 'ep.empresa = e')
   ->leftJoin('ep.persona', 'p');

// Filtro por búsqueda
if ($busqueda !== '') {
    switch ($by) {
        case 'cuit':
            $qb->andWhere('e.cuit LIKE :term');
            break;

        case 'email':
            $qb->andWhere('e.email LIKE :term');
            break;

        case 'telefono':
            $qb->andWhere('e.num_tel LIKE :term');
            break;

        case 'razon_social':
        default:
            $qb->andWhere('e.razon_social LIKE :term');
            break;
    }
    $qb->setParameter('term', "%{$busqueda}%");
}

// Aplicar orden
switch ($order) {
    case 'razon_asc':
        $qb->orderBy('e.razon_social', 'ASC');
        break;

    case 'razon_desc':
        $qb->orderBy('e.razon_social', 'DESC');
        break;

    // Alta en sistema WEB (fecha_inicial)
    case 'web_fecha_asc':
        // primero: los que tienen fecha (0) / luego NULL (1)
        $qb->addOrderBy('CASE WHEN e.fecha_inicial IS NULL THEN 1 ELSE 0 END', 'ASC')
           ->addOrderBy('e.fecha_inicial', 'ASC');
        break;

    case 'web_fecha_desc':
        $qb->addOrderBy('CASE WHEN e.fecha_inicial IS NULL THEN 1 ELSE 0 END', 'ASC')
           ->addOrderBy('e.fecha_inicial', 'DESC');
        break;


    // Alta en sistema FACTURACIÓN (fecha_alta)
    case 'fac_fecha_asc':
        $qb->addOrderBy('CASE WHEN e.fecha_alta IS NULL THEN 1 ELSE 0 END', 'ASC')
           ->addOrderBy('e.fecha_alta', 'ASC');
        break;

    case 'fac_fecha_desc':
    default:
        $qb->addOrderBy('CASE WHEN e.fecha_alta IS NULL THEN 1 ELSE 0 END', 'ASC')
           ->addOrderBy('e.fecha_alta', 'DESC');
        break;


    // Backward compatibility por si te quedan links viejos
    case 'fecha_asc':
        $qb->orderBy('e.fecha_alta', 'ASC');
        break;
    case 'fecha_desc':
        $qb->orderBy('e.fecha_alta', 'DESC');
        break;
}


// Evitar duplicados por joins
$qb->distinct();

// Clonar para contar total (DISTINCT)
$countQb = clone $qb;

$total   = (int) $countQb
    ->select('COUNT(DISTINCT e.id)')
    ->getQuery()
    ->getSingleScalarResult();

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages); // evita page fuera de rango

// Paginación
$qb->setFirstResult(($page - 1) * $perPage)
   ->setMaxResults($perPage);
$empresas = $qb->getQuery()->getResult();

// Si seleccionaron ver usuarios:
$empresaId          = isset($_GET['empresa_id']) ? (int) $_GET['empresa_id'] : null;
$empresaObj         = null;
$personasPorEmpresa = [];
if ($empresaId) {
    $empresaObj         = $entityManager->getRepository(Empresas::class)->find($empresaId);
    if ($empresaObj) {
        $personasPorEmpresa = [];
        if ($empresaObj) {
            // Trae Personas asociadas por la intermedia
            $personasPorEmpresa = $entityManager->createQueryBuilder()
              ->select('DISTINCT p')
              ->from(Personas::class, 'p')
              ->leftJoin('Entities\EmpresasPersonas', 'ep', 'WITH', 'ep.persona = p AND ep.empresa = :emp')
              ->where('ep.id IS NOT NULL OR p.empresa = :emp')
              ->setParameter('emp', $empresaObj)
              ->getQuery()
              ->getResult();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Malkoni Hnos - Servicios Online</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap y FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../styles/dashboard_soporteStyles.css">
</head>
<body class="container py-4" data-theme="light">

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <img src="../../logo.png" alt="Malkoni Hnos" class="header-logo">
  <div>
    <!-- Modo claro/oscuro -->
    <button id="btnMode" class="btn btn-mode" title="Modo claro/oscuro">
      <i class="fa fa-moon"></i>
    </button>
    <!-- Cerrar sesión -->
    <a href="../../logout.php" class="btn btn-outline-secondary ms-2">
      <i class="fa fa-sign-out-alt"></i> Cerrar sesión
    </a>
    <!-- Iniciar Sesión Clientes -->
    <button id="btnClientLogin" class="btn btn-outline-secondary ms-2">
      <i class="fa fa-sign-in-alt"></i> Iniciar Sesión Clientes
    </button>
    <!-- Sitio Web -->
    <a href="https://www.malkoni.com.ar"
       target="_blank" rel="noopener"
       class="btn btn-outline-secondary ms-2">
      <i class="fa fa-globe"></i> Sitio Web
    </a>
  </div>
</div>

  <!-- Filtro de búsqueda + orden -->
  <form method="get" class="row g-2 align-items-center mb-4">
      <div class="col-auto">
        <label class="form-label mb-0 small">Buscar por:</label>
        <?php $by = $_GET['by'] ?? 'razon_social'; ?>
        <select name="by" class="form-select by-select">
          <option value="razon_social" <?= $by==='razon_social'?'selected':'' ?>>Razón Social</option>
          <option value="cuit"     <?= $by==='cuit'?'selected':'' ?>>CUIT</option>
          <option value="email"    <?= $by==='email'?'selected':'' ?>>Mail</option>
          <option value="telefono" <?= $by==='telefono'?'selected':'' ?>>Teléfono</option>
        </select>
      </div>
    
      <div class="col-auto">
        <label class="form-label mb-0 small">&nbsp;</label>
        <input type="text" name="q" class="form-control search-q"
           placeholder="Buscar..."
           value="<?= htmlspecialchars($busqueda) ?>">
      </div>
    
      <div class="col-auto">
        <label class="form-label mb-0 small">Ordenar por:</label>
        <select name="order" class="form-select order-select" onchange="this.form.submit()">
          <option value="fac_fecha_desc" <?= $order==='fac_fecha_desc' || $order==='fecha_desc' ?'selected':'' ?>>
            Fecha de alta en sistema facturación ↓
          </option>
          <option value="fac_fecha_asc"  <?= $order==='fac_fecha_asc' || $order==='fecha_asc' ?'selected':'' ?>>
            Fecha de alta en sistema facturación ↑
          </option>
    
          <option value="web_fecha_desc" <?= $order==='web_fecha_desc'?'selected':'' ?>>
            Fecha de alta en sistema web ↓
          </option>
          <option value="web_fecha_asc"  <?= $order==='web_fecha_asc'?'selected':'' ?>>
            Fecha de alta en sistema web ↑
          </option>
    
          <option value="razon_asc"  <?= $order==='razon_asc' ?'selected':'' ?>>Razón A-Z</option>
          <option value="razon_desc" <?= $order==='razon_desc'?'selected':'' ?>>Razón Z-A</option>
        </select>
      </div>
    
      <div class="col-auto">
        <label class="form-label mb-0 small">&nbsp;</label>
        <button class="btn btn-primary" type="submit">Buscar</button>
      </div>
    </form>


  
  <?php
      // — Calcula start/end para ventana de páginas —
      $maxButtons = 5;
      $half       = floor($maxButtons / 2);
      $start      = max(1, $page - $half);
      $end        = min($totalPages, $start + $maxButtons - 1);
      if ($end - $start + 1 < $maxButtons) {
          $start = max(1, $end - $maxButtons + 1);
      }
    ?>
    <nav aria-label="Paginación soporte" class="mb-3">
      <ul class="pagination justify-content-center">
        <!-- Ir al primero -->
        <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="?<?= http_build_query(['by'=>$by,'q'=>$busqueda,'order'=>$order,'page'=>1])  ?>"
             aria-label="Primera">&laquo;&laquo;</a>
        </li>
    
        <!-- Ir al anterior -->
        <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
          <a class="page-link"
             href="?<?= http_build_query(['by'=>$by,'q'=>$busqueda,'order'=>$order,'page'=> max(1, $page-1) ]) ?>"
             aria-label="Anterior">&laquo;</a>
        </li>
    
        <!-- Botones de página -->
        <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link"
             href="?<?= http_build_query(['by'=>$by,'q'=>$busqueda,'order'=>$order,'page'=>$i]) ?>">
            <?= $i ?>
          </a>
        </li>
        <?php endfor; ?>
    
        <!-- Ir al siguiente -->
        <li class="page-item <?= $page === $totalPages ? 'disabled' : '' ?>">
          <a class="page-link"
             href="?<?= http_build_query(['by'=>$by,'q'=>$busqueda,'order'=>$order,'page'=> min($totalPages, $page+1) ]) ?>"
             aria-label="Siguiente">&raquo;</a>
        </li>
    
        <!-- Ir al último -->
        <li class="page-item <?= $page === $totalPages ? 'disabled' : '' ?>">
          <a class="page-link"
             href="?<?= http_build_query(['by'=>$by,'q'=>$busqueda,'order'=>$order,'page'=> $totalPages ]) ?>"
             aria-label="Última">&raquo;&raquo;</a>
        </li>
      </ul>
    </nav>

  <!-- Tabla de Empresas -->
  <?php if ($total === 0): ?>
      <div class="alert alert-warning">No se encontraron empresas.</div>
    <?php else: ?>
      <h5 class="mb-3">
        Mostrando <?= count($empresas) ?> de <?= $total ?> empresas
      </h5>
      <table class="table table-bordered align-middle mb-3">
        <thead>
          <tr>
            <th>Cód. Cliente</th>
            <th>Razón Social</th>
            <th>CUIT</th>
            <th>IVA</th>
            <th>Email</th>
            <th>Fecha alta (web)</th>
            <th>Fecha alta (facturación)</th>
            <th>Teléfono</th>
            <th class="text-center">Acción</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($empresas as $emp):
            // 1) Primera dirección (si existe)
            $dir = $emp->getDirecciones()->isEmpty()
                   ? null
                   : $emp->getDirecciones()->first();

            // 1.a) Resolver IDs de país/provincia/localidad de forma segura
            $paisId = '';
            $provId = '';
            $locId  = '';
            $locNom = '';

            if ($dir) {
              if (method_exists($dir, 'getPais') && $dir->getPais()) {
                  $paisId = (string) $dir->getPais()->getId();
              }
              if (method_exists($dir, 'getProvincia') && $dir->getProvincia()) {
                  $prov   = $dir->getProvincia();
                  $provId = (string) $prov->getId();
                  if ($paisId === '' && method_exists($prov, 'getPais') && $prov->getPais()) {
                      $paisId = (string) $prov->getPais()->getId();
                  }
              }
              if (method_exists($dir, 'getLocalidad') && $dir->getLocalidad()) {
                    try {
                        $loc = $dir->getLocalidad(); // proxy
                        $locId = (string)$loc->getId();
                
                        // fuerza existencia real (evita EntityNotFoundException al pedir getNombre)
                        $locReal = $entityManager->find(Localidades::class, (int)$locId);
                        if ($locReal) {
                            $locNom = (string)$locReal->getNombre();
                        } else {
                            $locId = '';
                            $locNom = '';
                        }
                    } catch (EntityNotFoundException $e) {
                        $locId = '';
                        $locNom = '';
                    }
                }
            }

            // 2) Armo el array con TODOS los campos
            $data = [
              'id'                => $emp->getId(),
              'cod_cliente'       => $emp->getCodCliente(),
              'razon_social'      => $emp->getRazonSocial(),
              'cuit'              => $emp->getCuit(),
              'email'             => $emp->getEmail(),
              'telefono'          => $emp->getNumTel(),
              'codcondiva'        => $emp->getCodCondIVA(),
              // Dirección:
              'pais'              => $paisId,
              'provincia'         => $provId,
              'localidad_id'      => $locId,
              'localidad_nombre'  => $locNom,
              'barrio'            => $dir ? ($dir->getBarrio() ?? '')        : '',
              'cp'                => $dir ? ($dir->getCp() ?? '')            : '',
              'domicilio'         => $dir ? ($dir->getDomicilio() ?? '')     : '',
              'observaciones'     => $dir ? ($dir->getObservaciones() ?? '') : '',
            ];
            $fechaWeb = method_exists($emp, 'getFechaInicial') ? $emp->getFechaInicial() : null;
            $webOk = ($fechaWeb !== null);
            $fechaFac = $emp->getFechaAlta();
          ?>
          <tr>
            <td><?= $data['cod_cliente']!==null 
                     ? htmlspecialchars($data['cod_cliente']) 
                     : '<em>Sin especificar</em>' ?></td>
            <td><?= htmlspecialchars($data['razon_social']) ?></td>
            <td><?= htmlspecialchars($data['cuit']) ?></td>
            <td><?= htmlspecialchars((string)$data['codcondiva']) ?></td>
            <td><?= htmlspecialchars($data['email']) ?></td>
            <td class="text-center">
              <?= $fechaWeb ? $fechaWeb->format('d/m/Y') : '<em>---</em>' ?>
            </td>
            
            <td class="text-center">
              <?= $fechaFac ? $fechaFac->format('d/m/Y') : '<em>---</em>' ?>
            </td>
            
            <td><?= htmlspecialchars($data['telefono']) ?></td>
            <td class="text-center action-buttons">
              <!-- Ver usuarios -->
                <button
                  type="button"
                  class="btn btn-info btn-sm"
                  title="Ver usuarios"
                  onclick="location.href='?<?= http_build_query([
                      'by'=>$by,
                      'q'=>$busqueda,
                      'order'=>$order,
                      'page'=>$page,
                      'empresa_id'=>$emp->getId()
                    ]) ?>#usuarios'">
                  <i class="fa-solid fa-users"></i>
                </button>
                
             <?php if ($webOk): ?>
             
              <!-- Agregar usuario -->
              <button
                class="btn btn-success btn-sm btn-crear-persona"
                title="Agregar usuario"
                data-bs-toggle="modal"
                data-bs-target="#modalNuevaPersona"
                data-empresa-id="<?= $emp->getId() ?>">
                <i class="fa fa-user-plus"></i>
              </button>
            <?php else: ?>
              <span class="tooltip-wrap d-inline-block" data-tooltip="Esta empresa no esta dada de alta en el sistema web">
                <button class="btn btn-success btn-sm" type="button" disabled style="pointer-events:none; opacity:.55;">
                  <i class="fa fa-user-plus"></i>
                </button>
              </span>
            <?php endif; ?>

    
              <!-- Editar empresa -->
              <button
                class="btn btn-warning btn-sm btn-editar-empresa"
                title="Editar empresa"
                data-bs-toggle="modal"
                data-bs-target="#modalEditarEmpresa"
                data-empresa='<?= json_encode($data, JSON_UNESCAPED_UNICODE) ?>'>
                <i class="fa fa-edit"></i>
              </button>
              
              <!-- Eliminar empresa -->
                <button
                  type="button"
                  class="btn btn-danger btn-sm btn-eliminar-empresa"
                  title="Eliminar empresa"
                  data-empresa-id="<?= (int)$emp->getId() ?>"
                  data-empresa-nombre="<?= htmlspecialchars((string)$emp->getRazonSocial()) ?>">
                  <i class="fa fa-trash"></i>
                </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    
  <!-- Tabla de Usuarios -->
  <?php if ($empresaId && $empresaObj): ?>
  <?php
  $empresaFechaWeb = method_exists($empresaObj, 'getFechaInicial') ? $empresaObj->getFechaInicial() : null;
  $empresaWebOk = ($empresaFechaWeb !== null);
?>
  <div id="usuarios"></div>
    <div class="usuarios-header mb-3">
      <h5 class="mb-0">Usuarios de la empresa <?= htmlspecialchars($empresaObj->getRazonSocial()) ?></h5>
    
      <div class="usuarios-actions">
          <?php if ($empresaObj->getCodCondIVA() !== 'CF'): ?>
        
            <?php if ($empresaWebOk): ?>
              <button class="btn btn-success btn-sm btn-crear-persona"
                      data-bs-toggle="modal" data-bs-target="#modalNuevaPersona"
                      data-empresa-id="<?= $empresaId ?>">
                <i class="fa fa-user-plus"></i> Nuevo usuario
              </button>
            <?php else: ?>
              <span class="tooltip-wrap d-inline-block" data-tooltip="Esta empresa no esta dada de alta en el sistema web">
                <button class="btn btn-success btn-sm" type="button" disabled style="pointer-events:none; opacity:.55;">
                  <i class="fa fa-user-plus"></i> Nuevo usuario
                </button>
              </span>
            <?php endif; ?>
        
          <?php endif; ?>
        
          <?php if ($empresaWebOk): ?>
            <button class="btn btn-outline-primary btn-sm"
                    id="btnAsociarUsuarioExistente"
                    data-empresa-id="<?= $empresaId ?>"
                    data-empresa-nombre="<?= htmlspecialchars($empresaObj->getRazonSocial()) ?>">
              <i class="fa fa-link"></i> Asociar usuario existente
            </button>
          <?php else: ?>
            <span class="tooltip-wrap d-inline-block" data-tooltip="Esta empresa no esta dada de alta en el sistema web">
              <button class="btn btn-outline-primary btn-sm" type="button" disabled style="pointer-events:none; opacity:.55;">
                <i class="fa fa-link"></i> Asociar usuario existente
              </button>
            </span>
          <?php endif; ?>
        </div>

    <?php if ($personasPorEmpresa): ?>
      <?php
        // Si tu rol 2 es "Operario", usamos este mapeo:
        $rolesMap  = [1=>'Admin',2=>'Operario',3=>'Soporte'];
        $estadoMap = [1=>'Activo',2=>'Inactivo',3=>'Pendiente'];
      ?>
      <table class="table table-hover table-bordered mb-4">
        <thead>
          <tr>
            <th>Nombre</th><th>Apellido</th>
            <th>DNI</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acción</th>
          </tr>
        </thead>
        <tbody>
         <?php foreach ($personasPorEmpresa as $p):
              $row = [
                'id'       => $p->getId(),
                'nombre'   => $p->getNombre(),
                'apellido' => $p->getApellido(),
                'dni'      => $p->getDni(),
                'email'    => $p->getEmail(),
                'telefono' => $p->getNumTel(),
                'rol'      => $p->getRol(),
                'estado'   => $p->getEstadoPersona()
              ];
            ?>
            <tr data-persona='<?= json_encode($row, JSON_UNESCAPED_UNICODE) ?>'>
            <td><?= htmlspecialchars($p->getNombre()) ?></td>
            <td><?= htmlspecialchars($p->getApellido()) ?></td>
            <td><?= htmlspecialchars((string)$p->getDni()) ?></td>
            <td><?= htmlspecialchars($p->getEmail()) ?></td>
            <td><?= htmlspecialchars($rolesMap[$p->getRol()] ?? '—') ?></td>
            <td><?= htmlspecialchars($estadoMap[$p->getEstadoPersona()] ?? '—') ?></td>
            <td>
              <button class="btn btn-info btn-sm btn-editar-persona"><i class="fa fa-edit"></i></button>
              <button class="btn btn-danger btn-sm btn-eliminar-persona ms-1"><i class="fa fa-trash"></i></button>
            
              <button class="btn btn-dark btn-sm ms-1 btn-ver-empresas-persona"
                      title="Ver en qué otras empresas está"
                      data-persona-id="<?= (int)$p->getId() ?>"
                      data-persona-label="<?= htmlspecialchars(trim($p->getNombre().' '.$p->getApellido().' ('.$p->getEmail().')')) ?>">
                <i class="fa fa-building"></i>
              </button>
            
              <?php if ((int)$p->getRol() === 2): ?>
                <button class="btn btn-secondary btn-sm ms-1 btn-asociar-otra-empresa">
                  <i class="fa fa-link"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-secondary">No hay usuarios.</div>
    <?php endif; ?>
  <?php endif; ?>


  <!-- Modal Crear Persona -->
  <div class="modal fade" id="modalNuevaPersona" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <form id="form-nueva-persona" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Crear usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="empresa_id" value="">
          <div class="mb-2"><label class="form-label">Nombre</label>
            <input name="nombre" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Apellido</label>
            <input name="apellido" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Género</label>
            <select name="genero" class="form-select" required>
              <option value="">--Seleccione--</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
            </select></div>
          <div class="mb-2"><label class="form-label">DNI</label>
            <input name="dni" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Teléfono</label>
            <input name="telefono" class="form-control"></div>
          <div class="mb-2">
              <label class="form-label">Contraseña</label>
              <div class="input-group">
                <input name="password" type="password" class="form-control js-pass" required>
                <button class="btn btn-outline-secondary js-toggle-pass" type="button" tabindex="-1">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success" type="submit">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>

  <!-- Modal Editar Persona -->
  <div class="modal fade" id="modalEditarPersona" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <form id="form-editar-persona" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Editar usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit-id" name="id">
          <div class="mb-2"><label class="form-label">Nombre</label>
            <input id="edit-nombre" name="nombre" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Apellido</label>
            <input id="edit-apellido" name="apellido" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">DNI</label>
            <input id="edit-dni" name="dni" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Email</label>
            <input id="edit-email" name="email" type="email" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Teléfono</label>
            <input id="edit-telefono" name="telefono" class="form-control"></div>
          <div class="mb-2">
              <label class="form-label">Nueva contraseña</label>
              <div class="input-group">
                <input id="edit-password" name="password" type="password" class="form-control js-pass" placeholder="Dejar vacío">
                <button class="btn btn-outline-secondary js-toggle-pass" type="button" tabindex="-1">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
            </div>
          <div class="mb-2"><label class="form-label">Rol</label>
            <select id="edit-rol" name="rol" class="form-select" required>
              <option value="1">Admin</option>
              <option value="2">Operario</option>
              <option value="3">Soporte</option>
            </select></div>
          <div class="mb-2"><label class="form-label">Estado</label>
            <select id="edit-estado" name="estado" class="form-select" required>
              <option value="1">Activo</option>
              <option value="2">Inactivo</option>
              <option value="3">Pendiente</option>
            </select></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>

<!-- Modal Editar Empresa + Dirección -->
<div class="modal fade" id="modalEditarEmpresa" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="form-editar-empresa" autocomplete="off">
        <!-- Hidden ID -->
        <input type="hidden" id="edit-empresa-id" name="id">

        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">Editar Empresa y Dirección</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <!-- === Datos de Empresa === -->
          <h6>Datos de Empresa</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="edit-empresa-razon_social" class="form-label">Razón Social</label>
              <input id="edit-empresa-razon_social"
                     name="razon_social"
                     class="form-control"
                     required>
            </div>
            <div class="col-md-3">
              <label for="edit-empresa-cuit" class="form-label">CUIT</label>
              <input id="edit-empresa-cuit"
                     name="cuit"
                     class="form-control"
                     required>
            </div>
            <div class="col-md-3">
              <label for="edit-empresa-telefono" class="form-label">Teléfono</label>
              <input id="edit-empresa-telefono"
                     name="telefono"
                     class="form-control">
            </div>
            <div class="col-md-6">
              <label for="edit-empresa-email" class="form-label">Email</label>
              <input id="edit-empresa-email"
                     name="email"
                     type="email"
                     class="form-control"
                     required>
            </div>
            <div class="col-md-6">
              <label for="edit-empresa-codcondiva" class="form-label">Condición IVA</label>
              <select id="edit-empresa-codcondiva"
                      name="codcondiva"
                      class="form-select"
                      required>
                <option value="MT">Responsable Monotributista</option>
                <option value="RI">Responsable Inscripto</option>
                <option value="EX">Exento</option>
                <option value="CF">Consumidor Final</option>
              </select>
            </div>
          </div>

          <hr>

          <!-- === Datos de Dirección === -->
          <h6>Datos de Dirección</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="edit-empresa-pais" class="form-label">País</label>
              <select id="edit-empresa-pais" name="pais" class="form-select" required>
                <option value="">--Seleccione País--</option>
                <?php foreach($listaPaises as $pais): ?>
                  <option value="<?= $pais->getId() ?>"><?= htmlspecialchars($pais->getNombre()) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label for="edit-empresa-provincia" class="form-label">Provincia</label>
              <select id="edit-empresa-provincia" name="provincia" class="form-select" required>
                <option value="">--Seleccione Provincia--</option>
                <?php foreach($listaProvincias as $prov): ?>
                  <option value="<?= $prov->getId() ?>" data-pais="<?= $prov->getPais() ? $prov->getPais()->getId() : '' ?>">
                    <?= htmlspecialchars($prov->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label for="edit-empresa-localidad-nombre" class="form-label">Localidad</label>
              <input list="datalist-localidades"
                     id="edit-empresa-localidad-nombre"
                     name="localidad_nombre"
                     class="form-control"
                     placeholder="Escriba o seleccione">
              <datalist id="datalist-localidades">
                <?php foreach($listaLocalidades as $loc): ?>
                  <?php $prov = $loc->getProvincia(); if (!$prov) continue; ?>
                  <option data-provincia="<?= $prov->getId() ?>"
                          data-id="<?= $loc->getId() ?>"
                          value="<?= htmlspecialchars($loc->getNombre()) ?>">
                  </option>
                <?php endforeach; ?>
              </datalist>
              <input type="hidden" id="edit-empresa-localidad-id" name="localidad_id">
            </div>

            <div class="col-md-4">
              <label for="edit-empresa-barrio" class="form-label">Barrio</label>
              <input id="edit-empresa-barrio" name="barrio" class="form-control">
            </div>
            
            <div class="col-md-2">
              <label for="edit-empresa-cp" class="form-label">C.P.</label>
              <input id="edit-empresa-cp" name="cp" class="form-control" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit-empresa-domicilio" class="form-label">Domicilio</label>
              <input id="edit-empresa-domicilio" name="domicilio" class="form-control">
            </div>
            
            <div class="col-12">
              <label for="edit-empresa-observaciones" class="form-label">Observaciones</label>
              <textarea id="edit-empresa-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-outline-secondary"
                  data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="submit"
                  class="btn btn-warning">
            Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Asociar a otra empresa -->
<div class="modal fade" id="modalAsociarOtraEmpresa" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="form-asociar-otra-empresa" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Asociar usuario a otra empresa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="persona_id" id="aso-persona-id">
          <input type="hidden" name="empresa_id" id="aso-empresa-id">

          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input class="form-control" id="aso-persona-label" disabled>
          </div>

          <div class="mb-2">
            <label class="form-label">Buscar empresa destino (razón / cuit / email / tel)</label>
            <div class="input-group">
              <input class="form-control" id="aso-empresa-q" placeholder="Escribí para buscar..." autocomplete="off">
              <button class="btn btn-outline-secondary" type="button" id="aso-clear" title="Limpiar">
                <i class="fa fa-times"></i>
              </button>
            </div>
            <div class="form-text">Mínimo 2 caracteres. Se listan las primeras coincidencias.</div>
          </div>

          <div class="mt-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <strong>Resultados</strong>
              <div id="aso-loading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></div>
              <span id="aso-count" class="text-muted small"></span>
            </div>

            <div id="aso-results" class="list-group" style="max-height: 320px; overflow:auto;">
              <div class="text-muted small px-2 py-2">Escribí arriba para buscar…</div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Seleccionado</label>
            <input class="form-control" id="aso-selected-label" placeholder="Todavía no seleccionaste ninguna empresa" disabled>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit" id="aso-submit" disabled>
            <i class="fa fa-link"></i> Asociar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal Asociar Usuario existente a otra empresa -->
<div class="modal fade" id="modalAsociarUsuarioExistente" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="form-asociar-usuario-existente" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Asociar usuario existente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="empresa_id" id="asoe-empresa-id">
          <input type="hidden" name="persona_id" id="asoe-persona-id">

          <div class="mb-3">
            <label class="form-label">Empresa</label>
            <input class="form-control" id="asoe-empresa-label" disabled>
          </div>

          <div class="mb-2">
            <label class="form-label">Buscar operario (email / nombre / dni / teléfono)</label>
            <div class="input-group">
              <input class="form-control" id="asoe-persona-q" placeholder="Escribí para buscar..." autocomplete="off">
              <button class="btn btn-outline-secondary" type="button" id="asoe-clear" title="Limpiar">
                <i class="fa fa-times"></i>
              </button>
            </div>
            <div class="form-text">Solo se muestran usuarios con rol=2 (Operario). Mínimo 2 caracteres.</div>
          </div>

          <div class="mt-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <strong>Resultados</strong>
              <div id="asoe-loading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></div>
              <span id="asoe-count" class="text-muted small"></span>
            </div>

            <div id="asoe-results" class="list-group" style="max-height: 320px; overflow:auto;">
              <div class="text-muted small px-2 py-2">Escribí arriba para buscar…</div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Seleccionado</label>
            <input class="form-control" id="asoe-selected-label" placeholder="Todavía no seleccionaste ninguno" disabled>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit" id="asoe-submit" disabled>
            <i class="fa fa-link"></i> Asociar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Para ver Empresas de un usuario -->
<div class="modal fade" id="modalVerEmpresasPersona" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Empresas asociadas al usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Usuario</label>
          <input class="form-control" id="vep-persona-label" disabled>
        </div>

        <div class="d-flex align-items-center gap-2 mb-2">
          <strong>Empresas</strong>
          <div id="vep-loading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></div>
          <span id="vep-count" class="text-muted small"></span>
        </div>

        <div id="vep-list" class="list-group" style="max-height: 360px; overflow:auto;">
          <div class="text-muted small px-2 py-2">Cargando…</div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

  <script>
  // JSON con todas las localidades agrupadas por provincia
  const localidadesPorProvincia = <?= json_encode($localidadesPorProvincia, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Dependencias JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', ()=> {
  // Elementos del DOM
  const selPais      = document.getElementById('edit-empresa-pais');
  const selProv      = document.getElementById('edit-empresa-provincia');
  const inpLocNombre = document.getElementById('edit-empresa-localidad-nombre');
  const inpLocId     = document.getElementById('edit-empresa-localidad-id');
  const datalistLoc  = document.getElementById('datalist-localidades');

  /** Filtra provincias por país **/
  function filtrarProvincias(){
    const p = selPais.value;
    document.querySelectorAll('#edit-empresa-provincia option').forEach(o=>{
      o.style.display = (!o.dataset.pais || o.dataset.pais===p) ? 'block' : 'none';
    });
    selProv.value = '';
    inpLocNombre.value = '';
    inpLocId.value = '';
    datalistLoc.innerHTML = '';
  }

  /** Pinta sólo las localidades de la provincia seleccionada **/
  function populateLocalidades(provId) {
    datalistLoc.innerHTML = '';
    const lista = localidadesPorProvincia[provId] || [];
    lista.forEach(loc => {
      const opt = document.createElement('option');
      opt.value      = loc.nombre;
      opt.dataset.id = loc.id;
      datalistLoc.appendChild(opt);
    });
  }

  // Al cambiar país
  selPais?.addEventListener('change', filtrarProvincias);

  // Al cambiar provincia
  selProv?.addEventListener('change', ()=>{
    populateLocalidades(selProv.value);
    inpLocNombre.value = '';
    inpLocId.value     = '';
  });

  // Al escribir en localidad
  inpLocNombre?.addEventListener('input', ()=>{
    const match = Array.from(datalistLoc.options).find(o => o.value === inpLocNombre.value);
    inpLocId.value = match ? match.dataset.id : '';
  });

  // Al abrir modal de editar empresa
  document.querySelectorAll('.btn-editar-empresa').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const data = JSON.parse(btn.getAttribute('data-empresa'));

      const assign = (idOrName, val) => {
        const byId = document.getElementById('edit-empresa-'+idOrName);
        if (byId) { byId.value = val ?? ''; return; }
        const byName = document.querySelector('[name="'+idOrName+'"]');
        if (byName) { byName.value = val ?? ''; }
      };

      assign('id', data.id);
      assign('razon_social', data.razon_social);
      assign('cuit', data.cuit);
      assign('telefono', data.telefono);
      assign('email', data.email);
      assign('codcondiva', data.codcondiva);
      assign('domicilio', data.domicilio);
      assign('cp', data.cp);

      selPais.value = data.pais || '';
      filtrarProvincias();
      selProv.value = data.provincia || '';
      populateLocalidades(data.provincia || '');

      inpLocNombre.value = data.localidad_nombre || '';
      inpLocId.value     = data.localidad_id   || '';

      document.getElementById('edit-empresa-barrio').value        = data.barrio        || '';
      document.getElementById('edit-empresa-observaciones').value = data.observaciones || '';
    });
  });

  // Envío AJAX: editar empresa
  document.getElementById('form-editar-empresa')?.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const res  = await fetch('ajax_editar_empresa.php', { method: 'POST', body: new FormData(e.target) });
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'No se pudo guardar');
      await Swal.fire('¡Guardado!','','success');
      location.reload();
    } catch(err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

  // Crear persona (set empresa_id)
  document.querySelectorAll('.btn-crear-persona').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.querySelector('#form-nueva-persona input[name="empresa_id"]');
      if (input) input.value = btn.dataset.empresaId;
    });
  });

  // Envío AJAX: crear persona
  document.getElementById('form-nueva-persona')?.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const res  = await fetch('ajax_crear_persona.php', { method:'POST', body: new FormData(e.target) });
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'No se pudo crear el usuario');
      await Swal.fire('¡Usuario creado!','','success');
      location.reload();
    } catch(err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

  // Abrir modal editar persona
  document.querySelectorAll('.btn-editar-persona').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = JSON.parse(btn.closest('tr').dataset.persona);
      Object.entries(data).forEach(([k,v])=>{
        const fld = document.getElementById('edit-'+k)
                  || document.querySelector('#form-editar-persona [name="'+k+'"]');
        if (fld) fld.value = v ?? '';
      });
      new bootstrap.Modal(document.getElementById('modalEditarPersona')).show();
    });
  });

  // Envío AJAX: editar persona
  document.getElementById('form-editar-persona')?.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const res  = await fetch('ajax_editar_persona.php', { method:'POST', body: new FormData(e.target) });
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'No se pudo editar el usuario');
      await Swal.fire('¡Usuario editado!','','success');
      location.reload();
    } catch(err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

  // Eliminar (desasociar) persona
  document.querySelectorAll('.btn-eliminar-persona').forEach(btn => {
    btn.addEventListener('click', () => {
      const personaData = JSON.parse(btn.closest('tr').dataset.persona);

      Swal.fire({
        title: '¿Quitar usuario de esta empresa?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, quitar'
      }).then(async result => {
        if (!result.isConfirmed) return;

        try {
          const res  = await fetch('ajax_eliminar_persona.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(personaData.id) +
                  '&empresa_id=' + encodeURIComponent(<?= (int)$empresaId ?>)
          });
          const json = await res.json();
          if (!json.success) throw new Error(json.error || 'No se pudo quitar el usuario');
          await Swal.fire('Listo','','success');
          location.reload();
        } catch(err) {
          Swal.fire('Error', err.message, 'error');
        }
      });
    });
  });

  // ===== Ojito ver/ocultar password (GLOBAL) =====
  document.querySelectorAll('.js-toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement.querySelector('.js-pass');
      const icon  = btn.querySelector('i');
      if (!input) return;

      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      icon.classList.toggle('fa-eye', !isPass);
      icon.classList.toggle('fa-eye-slash', isPass);
    });
  });

    // ===== Eliminar EMPRESA (preview + confirm) =====
    document.querySelectorAll('.btn-eliminar-empresa').forEach(btn => {
      btn.addEventListener('click', async () => {
        const empresaId = btn.dataset.empresaId;
        const empresaNombre = btn.dataset.empresaNombre || 'Empresa';
    
        // 1) Preview
        let preview;
        try {
          const res = await fetch('ajax_eliminar_empresa.php?empresa_id=' + encodeURIComponent(empresaId), {
            cache: 'no-store',
            credentials: 'same-origin'
          });
          preview = await res.json();
          if (!preview.success) throw new Error(preview.error || 'No se pudo obtener el detalle');
        } catch (e) {
          Swal.fire('Error', (e.message || e), 'error');
          return;
        }
    
        const users = preview.users || [];
        const total = users.length;
    
        const rowsHtml = total
          ? users.map(u => {
              const nombre = `${u.nombre || ''} ${u.apellido || ''}`.trim() || '(Sin nombre)';
              const email = u.email ? ` · ${u.email}` : '';
              const tel   = u.telefono ? ` · Tel ${u.telefono}` : '';
              const badge = (u.accion === 'eliminar')
                ? `<span class="badge text-bg-danger ms-2">Se elimina</span>`
                : `<span class="badge text-bg-secondary ms-2">Se desasocia</span>`;
              return `<li style="text-align:left; margin:6px 0;">
                        <strong>${nombre}</strong>${email}${tel}
                        ${badge}
                      </li>`;
            }).join('')
          : `<div class="text-muted">No hay usuarios asociados.</div>`;
    
        const html = `
          <div style="text-align:left">
            <div><strong>Empresa:</strong> ${preview.empresa?.razon_social || empresaNombre}</div>
            <div class="mt-2"><strong>Usuarios afectados:</strong> ${total}</div>
            <ul class="mt-2" style="padding-left:18px;">${rowsHtml}</ul>
            <div class="mt-2 text-muted" style="font-size:13px;">
              * Si un usuario está en otras empresas, solo se desasocia. Si está solo en esta, se elimina.
            </div>
          </div>
        `;
    
        // 2) Confirm
        const result = await Swal.fire({
          title: '¿Eliminar empresa?',
          icon: 'warning',
          html,
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#dc3545'
        });
    
        if (!result.isConfirmed) return;
    
        // 3) Delete
        try {
          const fd = new FormData();
          fd.append('empresa_id', empresaId);
    
          const res = await fetch('ajax_eliminar_empresa.php', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
          });
          const json = await res.json();
          if (!json.success) throw new Error(json.error || 'No se pudo eliminar');
    
          await Swal.fire('Eliminada', 'La empresa se eliminó correctamente.', 'success');
    
          // Volver sin empresa_id seleccionado (por si estabas viendo usuarios)
          const url = new URL(window.location.href);
          url.searchParams.delete('empresa_id');
          window.location.href = url.toString();
        } catch (e) {
          Swal.fire('Error', (e.message || e), 'error');
        }
      });
    });

  // ===== Asociar este usuario a otra empresa =====
  const asoQ       = document.getElementById('aso-empresa-q');
  const asoResults = document.getElementById('aso-results');
  const asoLoading = document.getElementById('aso-loading');
  const asoCount   = document.getElementById('aso-count');
  const asoHidden  = document.getElementById('aso-empresa-id');      // hidden empresa_id
  const asoSelLbl  = document.getElementById('aso-selected-label');  // texto seleccionado
  const asoSubmit  = document.getElementById('aso-submit');
  const asoClear   = document.getElementById('aso-clear');

  let tEmp = null;
  let empAbort = null;

  function resetAsoSelection() {
    asoHidden.value = '';
    asoSelLbl.value = '';
    asoSubmit.disabled = true;
  }

  function renderAsoMessage(msg) {
    asoResults.innerHTML = `<div class="text-muted small px-2 py-2">${msg}</div>`;
    asoCount.textContent = '';
  }

  function renderAsoItems(items) {
  if (!items.length) {
    renderAsoMessage('Sin resultados.');
    return;
  }

  asoResults.innerHTML = '';
  asoCount.textContent = `${items.length} resultado(s)`;

  items.forEach(it => {
    const selectable = (it.selectable !== false); // default true

    const a = document.createElement('button');
    a.type = 'button';
    a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-start';

    // Si NO seleccionable: estilo + bloqueo
    if (!selectable) {
      a.classList.add('disabled');
      a.setAttribute('aria-disabled', 'true');
      a.style.opacity = '0.6';
      a.style.cursor = 'not-allowed';
    }

    const left = document.createElement('div');
    left.className = 'me-2';
    left.innerHTML = `
      <div class="fw-semibold">${it.razon_social || it.label || 'Empresa'}</div>
      <div class="small text-muted">
        ${it.cuit ? 'CUIT ' + it.cuit : ''}
        ${it.email ? ' · ' + it.email : ''}
        ${it.telefono ? ' · Tel ' + it.telefono : ''}
        ${it.cod_cliente ? ' · CodCli ' + it.cod_cliente : ''}
      </div>
      ${!selectable ? `<div class="small text-danger mt-1">Empresa no validada (estado = 1)</div>` : ``}
    `;

    const right = document.createElement('span');

    if (selectable) {
      right.className = 'badge text-bg-primary';
      right.textContent = 'Seleccionar';
    } else {
      right.className = 'badge text-bg-secondary';
      right.textContent = 'No validada';
    }

    a.appendChild(left);
    a.appendChild(right);

    // Solo si es seleccionable dejamos click
    if (selectable) {
      a.addEventListener('click', () => {
        asoHidden.value = it.id;
        asoSelLbl.value = it.label || it.razon_social || '';
        asoSubmit.disabled = false;

        Array.from(asoResults.querySelectorAll('.list-group-item')).forEach(x => x.classList.remove('active'));
        a.classList.add('active');
        right.className = 'badge text-bg-light';
        right.textContent = 'Seleccionado';
      });
    } else {
      a.addEventListener('click', () => {
        // feedback opcional
        Swal.fire('No permitido', 'Esa empresa no está validada (estado = 1).', 'warning');
      });
    }

    asoResults.appendChild(a);
  });
}

// ===== Ver empresas donde está la persona =====
const vepModalEl  = document.getElementById('modalVerEmpresasPersona');
const vepLabel    = document.getElementById('vep-persona-label');
const vepList     = document.getElementById('vep-list');
const vepLoading  = document.getElementById('vep-loading');
const vepCount    = document.getElementById('vep-count');

function renderVep(items) {
  if (!items || !items.length) {
    vepList.innerHTML = `<div class="text-muted small px-2 py-2">No está asociado a ninguna empresa.</div>`;
    vepCount.textContent = '';
    return;
  }

  vepCount.textContent = `${items.length} empresa(s)`;
  vepList.innerHTML = '';

  items.forEach(it => {
    const a = document.createElement('div');
    a.className = 'list-group-item';

    const validTxt = (String(it.validado) === '1') ? 'Validada' : 'No validada';
    const validCls = (String(it.validado) === '1') ? 'text-success' : 'text-danger';

    a.innerHTML = `
      <div class="fw-semibold">${it.razon_social || 'Empresa'}</div>
      <div class="small text-muted">
        ${it.cuit ? 'CUIT ' + it.cuit : ''}
        ${it.email ? ' · ' + it.email : ''}
        ${it.telefono ? ' · Tel ' + it.telefono : ''}
        ${it.cod_cliente ? ' · CodCli ' + it.cod_cliente : ''}
      </div>
      <div class="small ${validCls} mt-1">${validTxt}</div>
    `;
    vepList.appendChild(a);
  });
}

async function cargarEmpresasPersona(personaId) {
  vepLoading.classList.remove('d-none');
  try {
    const res = await fetch('ajax_persona_empresas.php?persona_id=' + encodeURIComponent(personaId), { cache: 'no-store' });
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'No se pudo cargar');
    renderVep(json.items || []);
  } catch (e) {
    vepList.innerHTML = `<div class="text-danger small px-2 py-2">Error: ${(e.message || e)}</div>`;
    vepCount.textContent = '';
  } finally {
    vepLoading.classList.add('d-none');
  }
}

document.querySelectorAll('.btn-ver-empresas-persona').forEach(btn => {
  btn.addEventListener('click', () => {
    const personaId = btn.dataset.personaId;
    vepLabel.value = btn.dataset.personaLabel || '';
    vepList.innerHTML = `<div class="text-muted small px-2 py-2">Cargando…</div>`;
    vepCount.textContent = '';
    new bootstrap.Modal(vepModalEl).show();
    cargarEmpresasPersona(personaId);
  });
});

async function buscarEmpresas(q) {
  if (empAbort) empAbort.abort();
  empAbort = new AbortController();

  asoLoading.classList.remove('d-none');
  try {
    const url = 'ajax_buscar_empresas.php?q=' + encodeURIComponent(q);

    const res = await fetch(url, {
      signal: empAbort.signal,
      credentials: 'same-origin',
      cache: 'no-store'
    });

    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const dbg = res.headers.get('x-debug-file') || '(sin x-debug-file)';
    const finalUrl = res.url;

    const raw = await res.text();

    if (!res.ok) {
      throw new Error(`HTTP ${res.status} ${res.statusText} | CT=${ct} | DBG=${dbg} | URL=${finalUrl} | ${raw.slice(0, 300)}`);
    }
    if (raw.trim() === '') {
      throw new Error(`Respuesta vacía | HTTP ${res.status} | CT=${ct} | DBG=${dbg} | URL=${finalUrl}`);
    }
    if (!ct.includes('application/json')) {
      throw new Error(`No es JSON | CT=${ct} | DBG=${dbg} | URL=${finalUrl} | ${raw.slice(0, 300)}`);
    }

    const json = JSON.parse(raw);
    if (!json.success) throw new Error(json.error || 'No se pudo buscar');

    renderAsoItems(json.items || []);
  } catch (e) {
    if (e.name !== 'AbortError') {
      console.error(e);
      renderAsoMessage('Error buscando: ' + (e.message || e));
    }
  } finally {
    asoLoading.classList.add('d-none');
  }
}



  // Abrir modal desde el botón link de cada operario
  document.querySelectorAll('.btn-asociar-otra-empresa').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('aso-persona-id').value = btn.dataset.personaId;
      document.getElementById('aso-persona-label').value =
        `${btn.dataset.personaNombre} (${btn.dataset.personaEmail})`;

      asoQ.value = '';
      resetAsoSelection();
      renderAsoMessage('Escribí arriba para buscar…');

      new bootstrap.Modal(document.getElementById('modalAsociarOtraEmpresa')).show();
      setTimeout(() => asoQ.focus(), 250);
    });
  });

  asoClear?.addEventListener('click', () => {
    asoQ.value = '';
    resetAsoSelection();
    renderAsoMessage('Escribí arriba para buscar…');
    asoQ.focus();
  });

  asoQ?.addEventListener('input', () => {
    clearTimeout(tEmp);
    resetAsoSelection();

    const q = asoQ.value.trim();
    if (q.length < 2) {
      renderAsoMessage('Mínimo 2 caracteres…');
      return;
    }

    tEmp = setTimeout(() => buscarEmpresas(q), 250);
  });


  // Submit asociar (usuario -> empresa)
  document.getElementById('form-asociar-otra-empresa')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const res = await fetch('ajax_asociar_persona_empresa.php', { method:'POST', body:new FormData(e.target) });
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'No se pudo asociar');
      await Swal.fire('¡Asociado!','','success');
      location.reload();
    } catch (err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

    // ===== Asociar usuario existente a esta empresa =====
  const asoeQ       = document.getElementById('asoe-persona-q');
  const asoeResults = document.getElementById('asoe-results');
  const asoeLoading = document.getElementById('asoe-loading');
  const asoeCount   = document.getElementById('asoe-count');
  const asoeHidden  = document.getElementById('asoe-persona-id');
  const asoeSelLbl  = document.getElementById('asoe-selected-label');
  const asoeSubmit  = document.getElementById('asoe-submit');
  const asoeClear   = document.getElementById('asoe-clear');

  let tPer = null;
  let perAbort = null;

  function resetAsoeSelection() {
    asoeHidden.value = '';
    asoeSelLbl.value = '';
    asoeSubmit.disabled = true;
  }

  function renderAsoeMessage(msg) {
    asoeResults.innerHTML = `<div class="text-muted small px-2 py-2">${msg}</div>`;
    asoeCount.textContent = '';
  }

  function renderAsoeItems(items) {
    if (!items.length) {
      renderAsoeMessage('Sin resultados.');
      return;
    }

    asoeResults.innerHTML = '';
    asoeCount.textContent = `${items.length} resultado(s)`;

    items.forEach(it => {
      const a = document.createElement('button');
      a.type = 'button';
      a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-start';

      const left = document.createElement('div');
      left.className = 'me-2';
      left.innerHTML = `
        <div class="fw-semibold">${(it.nombre || 'Sin nombre')}</div>
        <div class="small text-muted">${(it.email || '')}${it.dni ? ' · DNI ' + it.dni : ''}${it.telefono ? ' · Tel ' + it.telefono : ''}</div>
      `;

      const right = document.createElement('span');
      right.className = 'badge text-bg-primary';
      right.textContent = 'Seleccionar';

      a.appendChild(left);
      a.appendChild(right);

      a.addEventListener('click', () => {
        asoeHidden.value = it.id;
        asoeSelLbl.value = it.label || `${it.nombre || ''} ${it.email || ''}`.trim();
        asoeSubmit.disabled = false;

        // marcamos visualmente el seleccionado
        Array.from(asoeResults.querySelectorAll('.list-group-item')).forEach(x => x.classList.remove('active'));
        a.classList.add('active');
        right.className = 'badge text-bg-light';
        right.textContent = 'Seleccionado';
      });

      asoeResults.appendChild(a);
    });
  }

  async function buscarOperarios(q) {
    if (perAbort) perAbort.abort();
    perAbort = new AbortController();

    asoeLoading.classList.remove('d-none');
    try {
      const res = await fetch('ajax_buscar_operarios.php?q=' + encodeURIComponent(q), { signal: perAbort.signal });
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'No se pudo buscar');
      renderAsoeItems(json.items || []);
    } catch (e) {
      if (e.name !== 'AbortError') renderAsoeMessage('Error buscando. Reintentá.');
    } finally {
      asoeLoading.classList.add('d-none');
    }
  }

  document.getElementById('btnAsociarUsuarioExistente')?.addEventListener('click', (e) => {
    const btn = e.currentTarget;
    document.getElementById('asoe-empresa-id').value = btn.dataset.empresaId;
    document.getElementById('asoe-empresa-label').value = btn.dataset.empresaNombre;

    asoeQ.value = '';
    resetAsoeSelection();
    renderAsoeMessage('Escribí arriba para buscar…');

    new bootstrap.Modal(document.getElementById('modalAsociarUsuarioExistente')).show();
    setTimeout(() => asoeQ.focus(), 250);
  });

  asoeClear?.addEventListener('click', () => {
    asoeQ.value = '';
    resetAsoeSelection();
    renderAsoeMessage('Escribí arriba para buscar…');
    asoeQ.focus();
  });

  asoeQ?.addEventListener('input', () => {
    clearTimeout(tPer);
    resetAsoeSelection();

    const q = asoeQ.value.trim();
    if (q.length < 2) {
      renderAsoeMessage('Mínimo 2 caracteres…');
      return;
    }

    tPer = setTimeout(() => buscarOperarios(q), 250);
  });


  // Submit asociar (operario existente -> empresa actual)
  document.getElementById('form-asociar-usuario-existente')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const res = await fetch('ajax_asociar_persona_empresa.php', { method:'POST', body:new FormData(e.target) });
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'No se pudo asociar');
      await Swal.fire('¡Asociado!','','success');
      location.reload();
    } catch (err) {
      Swal.fire('Error', err.message, 'error');
    }
  });
});
</script>

<!-- Script de Dark/Light Mode (sin cambios) -->
<script>
document.getElementById('btnClientLogin').addEventListener('click', async () => {
  await fetch('../../logout.php');
  window.location.href = '../../login.php';
});
document.addEventListener('DOMContentLoaded', () => {
  const btnMode = document.getElementById('btnMode');
  const icon    = btnMode.querySelector('i');
  const savedTheme = localStorage.getItem('dashboardSoporteTheme') || 'light';
  document.body.setAttribute('data-theme', savedTheme);
  if (savedTheme === 'dark') icon.classList.replace('fa-moon','fa-sun');
  btnMode.addEventListener('click', () => {
    const current = document.body.getAttribute('data-theme')==='light'?'dark':'light';
    document.body.setAttribute('data-theme', current);
    localStorage.setItem('dashboardSoporteTheme', current);
    icon.classList.toggle('fa-moon');
    icon.classList.toggle('fa-sun');
  });
});
</script>
</body>
</html>
