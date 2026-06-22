<?php
// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires');

use Entities\Personas;
use Entities\Direcciones;
use Entities\Paises;
use Entities\Provincias;
use Entities\Localidades;

require_once __DIR__ . '/../../vendor/autoload.php';
/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
$entityManager = require __DIR__ . '/../../config/doctrine.php';

// 1) Verifico sesión y usuario
$userId = $_SESSION['id'] ?? null;
if (empty($_SESSION['usuario']) || !$userId) {
    header('Location: login.php');
    exit;
}
/** @var Personas|null $usuario */
$usuario = $entityManager->find(Personas::class, $userId);
if (!$usuario || !$usuario->getEmpresa()) {
    die('Acceso no autorizado');
}

// Helper mayúsculas UTF-8
function to_upper(?string $s): ?string {
    if ($s === null) return null;
    $s = trim($s);
    return $s === '' ? null : mb_strtoupper($s, 'UTF-8');
}

// 2) Procesar envíos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['id'] ?? '') == $userId) {
    $section = $_POST['section'] ?? '';

    // -- Sección personal --
    if ($section === 'personal') {
        $nombre   = to_upper($_POST['nombre']   ?? null);
        $apellido = to_upper($_POST['apellido'] ?? null);
        $email    = trim($_POST['email']        ?? '');
        $telefono = trim($_POST['telefono']     ?? '');

        // Si no mandan algo, se mantiene el valor actual
        $usuario
            ->setNombre(   $nombre   ?? $usuario->getNombre())
            ->setApellido( $apellido ?? $usuario->getApellido())
            ->setEmail(    $email    !== '' ? $email : $usuario->getEmail())
            ->setNumTel(   $telefono !== '' ? $telefono : null); // si vacío → NULL


        $entityManager->flush();
        $_SESSION['mensaje'] = 'Información personal actualizada';
        header('Location: perfil.php');
        exit;
    }

    // -- Sección dirección --
    if ($section === 'direccion') {
        // Sólo Admin puede editar dirección
        if ((int)$usuario->getRol() !== 1) {
            $_SESSION['mensaje'] = 'No tiene permisos para editar la dirección.';
            header('Location: perfil.php');
            exit;
        }

        // Repos y obtener/crear la Dirección de la empresa del usuario
        $repoDir   = $entityManager->getRepository(Direcciones::class);
        /** @var Direcciones|null $direccion */
        $direccion = $repoDir->findOneBy(['empresa'=>$usuario->getEmpresa()]) ?: new Direcciones();

        // Campos básicos de dirección (forzar MAYÚSCULAS en domicilio/barrio)
        $domicilio = to_upper($_POST['domicilio'] ?? null);
        $barrio    = to_upper($_POST['barrio']    ?? null);
        $cp        = trim($_POST['cp']            ?? '');

        $direccion
            ->setDomicilio($domicilio)
            ->setBarrio($barrio)
            ->setCp($cp !== '' ? $cp : null);

        // ===== País / Provincia / Localidad (solo texto para localidad) =====
        $paisId = (int)($_POST['pais'] ?? 0);
        $provId = (int)($_POST['provincia'] ?? 0);
        // Usuario escribe localidad en texto; buscamos/creamos por nombre+provincia
        $locNomUpper = to_upper($_POST['localidad_nombre'] ?? null);

        /** @var Paises|null $pais */
        $pais = $paisId ? $entityManager->find(Paises::class, $paisId) : null;

        /** @var Provincias|null $prov */
        $prov = $provId ? $entityManager->find(Provincias::class, $provId) : null;

        // Si no enviaron país, intentar inferirlo desde la provincia
        if (!$pais && $prov && method_exists($prov, 'getPais')) {
            $pais = $prov->getPais();
        }

        // Coherencia país↔provincia: si difieren, alinear al de la provincia
        if ($pais && $prov && method_exists($prov, 'getPais') && $prov->getPais()
            && $prov->getPais()->getId() !== $pais->getId()) {
            $pais = $prov->getPais();
        }

        // Localidad: buscar por nombre (mayúsculas) dentro de la provincia, si la hay
        /** @var Localidades|null $loc */
        $loc = null;
        if ($locNomUpper && $prov) {
            // Intentar encontrar coincidencia exacta en esa provincia
            $loc = $entityManager->getRepository(Localidades::class)->findOneBy([
                'nombre'    => $locNomUpper,
                'provincia' => $prov
            ]);
            if (!$loc) {
                // Crear si no existe
                $loc = new Localidades();
                $loc->setNombre($locNomUpper)->setProvincia($prov);
                $entityManager->persist($loc);
            }
        }

        // Setear en la Dirección (con País directo en Direcciones)
        if (method_exists($direccion, 'setPais')) {
            $direccion->setPais($pais);
        }
        $direccion
            ->setProvincia($prov)
            ->setLocalidad($loc)
            ->setEmpresa($usuario->getEmpresa());

        $entityManager->persist($direccion);
        $entityManager->flush();

        $_SESSION['mensaje'] = 'Dirección actualizada';
        header('Location: perfil.php');
        exit;
    }
}

// 3) Cargo la dirección para mostrar
/** @var Direcciones|null $direccion */
$direccion = $entityManager
    ->getRepository(Direcciones::class)
    ->findOneBy(['empresa' => $usuario->getEmpresa()]);

// 3bis) Listas para selects
$listaPaises      = $entityManager->getRepository(Paises::class)->findAll();
$listaProvincias  = $entityManager->getRepository(Provincias::class)->findAll();

// 4) Avatar según género
$g      = $usuario->getGenero();
$avatar = $g === 'F'
    ? 'https://img.icons8.com/bubbles/150/000000/user-female-circle.png'
    : 'https://img.icons8.com/bubbles/150/000000/user-male-circle.png';

// Helpers para valores iniciales de selects
$initPaisId = '';
$initProvId = '';
$initLocNm  = '';

if ($direccion) {
    if (method_exists($direccion, 'getPais') && $direccion->getPais()) {
        $initPaisId = $direccion->getPais()->getId();
    } elseif ($direccion->getProvincia() && method_exists($direccion->getProvincia(), 'getPais') && $direccion->getProvincia()->getPais()) {
        $initPaisId = $direccion->getProvincia()->getPais()->getId();
    }
    if ($direccion->getProvincia()) {
        $initProvId = $direccion->getProvincia()->getId();
    }
    if ($direccion->getLocalidad()) {
        $initLocNm = $direccion->getLocalidad()->getNombre();
    }
}

// Flags de permisos
$isAdmin    = ((int)$usuario->getRol() === 1);
$isOperario = ((int)$usuario->getRol() === 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Malkoni Hnos - Servicios Online</title>
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Estilos de perfil -->
  <link rel="stylesheet" href="styles/navbarStyles.css">
  <link rel="stylesheet" href="styles/perfilStyles.css">
  <style>
    .edit-icon.disabled { opacity: .4; cursor: not-allowed; }
    .modal-disabled .form-control,
    .modal-disabled .form-select,
    .modal-disabled button[type="submit"] { pointer-events: none; opacity: .6; }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container profile-container">
    <div class="row gx-4 align-items-stretch">
      <!-- Panel izquierdo -->
      <div class="col-12 col-md-4 mb-4 d-flex">
        <div class="card profile-sidebar flex-fill h-100">
          <div class="card-body d-flex flex-column justify-content-center text-center">
            <img src="<?= $avatar ?>" class="profile-avatar mb-3" alt="Avatar">
            <h3 class="profile-name">
              <?= htmlspecialchars($usuario->getNombre().' '.$usuario->getApellido()) ?>
            </h3>
            <p class="profile-title">Mi Perfil</p>
          </div>
        </div>
      </div>

      <!-- Panel derecho -->
      <div class="col-12 col-md-8 d-flex flex-column">
        <!-- Información Personal -->
        <div class="card section-card mb-4 flex-shrink-0">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Información Personal</span>
            <i class="bi bi-pencil-square edit-icon"
               data-bs-toggle="modal" data-bs-target="#modalPersonal"></i>
          </div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-sm-4">Nombre</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($usuario->getNombre()) ?></dd>
              <dt class="col-sm-4">Apellido</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($usuario->getApellido()) ?></dd>
              <dt class="col-sm-4">Correo Electrónico</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($usuario->getEmail()) ?></dd>
              <dt class="col-sm-4">Teléfono</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($usuario->getNumTel()) ?></dd>
            </dl>
          </div>
        </div>

        <!-- Información de Dirección -->
        <div class="card section-card mb-4 flex-shrink-0">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Información de Dirección</span>
            <?php if ($isAdmin): ?>
              <i class="bi bi-pencil-square edit-icon"
                 data-bs-toggle="modal" data-bs-target="#modalDireccion"></i>
            <?php else: ?>
              <i class="bi bi-pencil-square edit-icon disabled" title="Sólo un administrador puede editar"></i>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <?php if ($direccion): ?>
              <dl class="row mb-0">
                <dt class="col-sm-4">Domicilio</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($direccion->getDomicilio() ?: '–') ?></dd>

                <dt class="col-sm-4">Barrio</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($direccion->getBarrio() ?: '–') ?></dd>

                <dt class="col-sm-4">CP</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($direccion->getCp() ?: '–') ?></dd>

                <dt class="col-sm-4">Localidad</dt>
                <dd class="col-sm-8">
                  <?= htmlspecialchars($direccion->getLocalidad() ? $direccion->getLocalidad()->getNombre() : '–') ?>
                </dd>

                <dt class="col-sm-4">Provincia</dt>
                <dd class="col-sm-8">
                  <?= htmlspecialchars($direccion->getProvincia() ? $direccion->getProvincia()->getNombre() : '–') ?>
                </dd>

                <dt class="col-sm-4">País</dt>
                <dd class="col-sm-8">
                  <?php
                    $paisNombre = '–';
                    if (method_exists($direccion, 'getPais') && $direccion->getPais()) {
                        $paisNombre = $direccion->getPais()->getNombre();
                    } elseif ($direccion->getProvincia() && method_exists($direccion->getProvincia(), 'getPais') && $direccion->getProvincia()->getPais()) {
                        $paisNombre = $direccion->getProvincia()->getPais()->getNombre();
                    }
                    echo htmlspecialchars($paisNombre);
                  ?>
                </dd>
              </dl>
            <?php else: ?>
              <p class="text-muted mb-0">No hay dirección registrada.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Actividad -->
        <div class="card section-card mb-0 flex-shrink-0">
          <div class="card-header">Actividad</div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-sm-4">Último acceso</dt>
              <dd class="col-sm-8"><?= date('d/m/Y H:i:s', $_SESSION['ultimo_acceso'] ?? time()) ?></dd>
              <?php if ($usuario->getRol() === 1): ?>
                <dt class="col-sm-4">Rol</dt>
                <dd class="col-sm-8">Administrador</dd>
              <?php endif; ?>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Editar Información Personal -->
  <div class="modal fade" id="modalPersonal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
      <form method="POST" action="perfil.php">
        <input type="hidden" name="id"      value="<?= (int)$userId ?>">
        <input type="hidden" name="section" value="personal">
        <div class="modal-header">
          <h5 class="modal-title">Editar Información Personal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="nombre" type="text" class="form-control"
                   value="<?= htmlspecialchars($usuario->getNombre()) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input name="apellido" type="text" class="form-control"
                   value="<?= htmlspecialchars($usuario->getApellido()) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo Electrónico</label>
            <input name="email" type="email" class="form-control"
                   value="<?= htmlspecialchars($usuario->getEmail()) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <?php
              $telValue = $usuario->getNumTel();
              $telValue = ($telValue === null || $telValue === '' || $telValue === '0' || $telValue === 0) ? '' : $telValue;
            ?>
            <input name="telefono" type="text" class="form-control"
                   value="<?= htmlspecialchars($telValue) ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary btn-sm">Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div></div>
  </div>

  <!-- Modal: Editar Información de Dirección -->
  <div class="modal fade" id="modalDireccion" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content <?= $isAdmin ? '' : 'modal-disabled' ?>">
      <form method="POST" action="perfil.php">
        <input type="hidden" name="id"      value="<?= (int)$userId ?>">
        <input type="hidden" name="section" value="direccion">
        <div class="modal-header">
          <h5 class="modal-title">Editar Información de Dirección</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- País -->
          <div class="mb-3">
            <label class="form-label">País</label>
            <select id="pais" name="pais" class="form-select" <?= $isAdmin ? '' : 'disabled' ?> required>
              <option value="">--Seleccione País--</option>
              <?php foreach ($listaPaises as $pais): ?>
                <option value="<?= $pais->getId() ?>"
                  <?= ($initPaisId && (int)$initPaisId === (int)$pais->getId()) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($pais->getNombre()) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Provincia -->
          <div class="mb-3">
            <label class="form-label">Provincia</label>
            <select id="provincia" name="provincia" class="form-select" <?= $isAdmin ? '' : 'disabled' ?> required>
              <option value="">--Seleccione Provincia--</option>
              <?php foreach ($listaProvincias as $prov): ?>
                <option value="<?= $prov->getId() ?>"
                        data-pais="<?= $prov->getPais() ? $prov->getPais()->getId() : '' ?>"
                  <?= ($initProvId && (int)$initProvId === (int)$prov->getId()) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($prov->getNombre()) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Localidad (solo texto, sin datalist) -->
          <div class="mb-3">
            <label class="form-label">Localidad</label>
            <input id="localidad-nombre"
                   name="localidad_nombre"
                   class="form-control"
                   placeholder="Escriba la localidad"
                   value="<?= htmlspecialchars($initLocNm) ?>"
                   <?= $isAdmin ? '' : 'disabled' ?>
                   required>
          </div>
          <!-- Domicilio, Barrio, CP -->
          <div class="mb-3">
            <label class="form-label">Domicilio</label>
            <input name="domicilio" type="text" class="form-control"
                   value="<?= htmlspecialchars($direccion->getDomicilio() ?? '') ?>"
                   <?= $isAdmin ? '' : 'disabled' ?>>
          </div>
          <div class="mb-3">
            <label class="form-label">Barrio</label>
            <input name="barrio" type="text" class="form-control"
                   value="<?= htmlspecialchars($direccion->getBarrio() ?? '') ?>"
                   <?= $isAdmin ? '' : 'disabled' ?>>
          </div>
          <div class="mb-3">
            <label class="form-label">CP</label>
            <input name="cp" type="text" class="form-control"
                   value="<?= htmlspecialchars($direccion->getCp() ?? '') ?>"
                   <?= $isAdmin ? '' : 'disabled' ?>>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary btn-sm" <?= $isAdmin ? '' : 'disabled' ?>>Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </form>
    </div></div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
      // Filtro de provincias por país (sigue funcionando, pero ya no hay datalist de localidades)
      const paisSel = document.getElementById('pais');
      const provSel = document.getElementById('provincia');
    
      function filtrarProvincias(){
        if (!paisSel || !provSel) return;
        const p = paisSel.value;
        Array.from(provSel.options).forEach(o=>{
          o.style.display = (!o.dataset.pais || o.dataset.pais === p) ? 'block' : 'none';
        });
        // NO tocamos la selección aquí en carga inicial;
        // sólo se limpia cuando el usuario cambia el país manualmente:
      }
    
      // Si el usuario CAMBIA el país, entonces sí reseteamos provincia
      if (paisSel) paisSel.addEventListener('change', ()=>{
        filtrarProvincias();
        provSel.value = ''; // reset real sólo por acción del usuario
      });
    
      // Inicialización UI: setear país y luego provincia inicial
      document.addEventListener('DOMContentLoaded', ()=>{
        const initPais = <?= json_encode($initPaisId) ?>;
        const initProv = <?= json_encode($initProvId) ?>;
    
        if (paisSel && initPais) {
          paisSel.value = initPais;
          filtrarProvincias();
        }
        if (provSel && initProv) {
          provSel.value = initProv;
        }
      });
    </script>

  <!-- SweetAlert de éxito -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    <?php if (!empty($_SESSION['mensaje'])): ?>
      Swal.fire({ icon: 'info', title: '<?= addslashes($_SESSION['mensaje']); ?>', timer: 2200 });
      <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
  </script>
</body>
</html>
