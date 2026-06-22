<?php
// cambiar_empresa.php – Malkoni (Opción A: multi-empresa solo para rol=2)
// ======================================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../config/doctrine.php';

use Entities\Empresas;
use Entities\Personas;
use Entities\EmpresasPersonas;

// --- Guard: login ---
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php'); // ajustá si tu login está en otra ruta
    exit;
}

/**
 * Comprueba que el CUIT (solo dígitos) tenga 11 dígitos y dígito verificador correcto.
 */
function validarCuit(string $cuit): bool {
    $cuit = preg_replace('/\D/', '', $cuit);
    if (strlen($cuit) !== 11) return false;

    $digits      = str_split($cuit);
    $multipliers = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $sum         = 0;

    for ($i = 0; $i < 10; $i++) {
        $sum += intval($digits[$i]) * $multipliers[$i];
    }

    $mod      = $sum % 11;
    $expected = 11 - $mod;

    if ($expected === 11) $expected = 0;
    elseif ($expected === 10) $expected = 9;

    return intval($digits[10]) === $expected;
}

// --- Handler: seleccionar empresa activa (y asociar si es rol=2) ---
if (isset($_GET['empresa_id'])) {
    $nuevaId = (int)$_GET['empresa_id'];

    $nuevaEmpresa = $entityManager->getRepository(Empresas::class)->find($nuevaId);
    if (!$nuevaEmpresa) {
        $_SESSION['errorMensaje'] = 'Empresa inexistente.';
        header('Location: cambiar_empresa.php');
        exit;
    }

    // Sólo empresas validadas
    if (!$nuevaEmpresa->isValidado()) {
        $_SESSION['errorMensaje'] = 'La empresa seleccionada no está validada.';
        header('Location: cambiar_empresa.php');
        exit;
    }

    $persona = $entityManager->getRepository(Personas::class)->findOneBy(['email' => $_SESSION['usuario']]);
    if (!$persona) {
        $_SESSION['errorMensaje'] = 'Usuario no encontrado.';
        header('Location: cambiar_empresa.php');
        exit;
    }

    // Solo comunes pueden "agregarse" a otra empresa
    if ((int)$persona->getRol() !== 2) {
        $_SESSION['errorMensaje'] = 'Esta acción es solo para usuarios comunes.';
        header('Location: dashboard.php');
        exit;
    }

    // Si ya es la empresa activa, no hacemos nada
    $currentEmpresaId = (int)($_SESSION['empresa_id'] ?? 0);
    if ($currentEmpresaId === $nuevaId) {
        $_SESSION['successMensaje'] = 'Ya estás usando esa empresa.';
        header('Location: cambiar_empresa.php');
        exit;
    }

    // 1) Setear empresa activa en sesión
    $_SESSION['empresa_id'] = $nuevaId;
    
    // 1bis) Persistir empresa activa en DB (para que quede al re-loguear)
    if (method_exists($persona, 'setEmpresaActiva')) {
        $persona->setEmpresaActiva($nuevaEmpresa);
        $entityManager->flush();
    }

    // 2) Si NO es la empresa principal (Personas.id_empresa), aseguramos vínculo en la intermedia
    $empresaPrincipal    = $persona->getEmpresa(); // ManyToOne actual (id_empresa)
    $empresaPrincipalId  = $empresaPrincipal ? (int)$empresaPrincipal->getId() : 0;

    if ($empresaPrincipalId !== $nuevaId) {
        $repoEP = $entityManager->getRepository(EmpresasPersonas::class);

        $vinculo = $repoEP->findOneBy([
            'empresa' => $nuevaEmpresa,
            'persona' => $persona
        ]);

        if ($vinculo) {
            // Si existía pero estaba inactivo, reactivar
            if ((int)$vinculo->getEstado() !== 1) {
                $vinculo->setEstado(1);
                $entityManager->flush();
            }
        } else {
            // Crear vínculo nuevo
            $v = new EmpresasPersonas();
            $v->setEmpresa($nuevaEmpresa)
              ->setPersona($persona)
              ->setEstado(1);

            $entityManager->persist($v);
            $entityManager->flush();
        }
    }

    $_SESSION['successMensaje'] = 'Empresa seleccionada: «' . $nuevaEmpresa->getRazonSocial() . '».';
    header('Location: cambiar_empresa.php');
    exit;
}

// Manejo de mensajes
$errorCuit      = false;
$errorTel       = false;
$mensajeError   = $_SESSION['errorMensaje']   ?? null;
$mensajeSuccess = $_SESSION['successMensaje'] ?? null;
unset($_SESSION['errorMensaje'], $_SESSION['successMensaje']);

// === Preparar datos para la búsqueda ===
$coincidencias = [];
$rawDoc        = trim($_POST['doc']      ?? '');
$telefono      = trim($_POST['telefono'] ?? '');
$email         = trim($_POST['email']    ?? '');
$rawName       = trim($_POST['nombre']   ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuitClean = preg_replace('/\D/', '', $rawDoc);

    if ($rawDoc !== '' && !validarCuit($cuitClean)) $errorCuit = true;
    if ($telefono !== '' && !preg_match('/^\d{4,}$/', $telefono)) $errorTel = true;

    // Si no hay errores y al menos un campo lleno, buscamos
    if (
        !$errorCuit &&
        !$errorTel &&
        ($cuitClean !== '' || $telefono !== '' || $email !== '' || $rawName !== '')
    ) {
        $qb = $entityManager->createQueryBuilder()
            ->select('e')
            ->from(Empresas::class, 'e');

        $orX = [];

        if ($cuitClean !== '') {
            $orX[] = $qb->expr()->eq('e.cuit', ':cuit');
            $qb->setParameter('cuit', $cuitClean);
        }
        if ($email !== '') {
            $orX[] = $qb->expr()->eq('e.email', ':email');
            $qb->setParameter('email', $email);
        }
        if ($telefono !== '') {
            $orX[] = $qb->expr()->like('e.num_tel', ':tel');
            $qb->setParameter('tel', "%$telefono");
        }
        if ($rawName !== '') {
            $orX[] = $qb->expr()->like('e.razon_social', ':nombre');
            $qb->setParameter('nombre', "%$rawName%");
        }

        if (count($orX)) {
            $qb->where(call_user_func_array([$qb->expr(), 'orX'], $orX))
               // Filtrar sólo MT, RI y EX (excluir CF)
               ->andWhere($qb->expr()->in('e.codCondIVA', ':ivas'))
               ->setParameter('ivas', ['MT', 'RI', 'EX']);

            $results = $qb->getQuery()->getResult();
            foreach ($results as $e) {
                $coincidencias[] = [
                    'id'           => $e->getId(),
                    'razon_social' => $e->getRazonSocial(),
                    'cuit'         => $e->getCuit(),
                    'cod_cond_iva' => $e->getCodCondIVA(),
                    'email'        => $e->getEmail(),
                    'num_tel'      => $e->getNumTel(),
                    'validado'     => $e->isValidado(),
                ];
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
    <link rel="stylesheet" href="styles/cambiar_empresa.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php if ($mensajeError): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Error',
  text: '<?= htmlspecialchars($mensajeError, ENT_QUOTES) ?>',
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#166379'
});
</script>
<?php endif; ?>

<?php if ($mensajeSuccess): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Listo',
  text: '<?= htmlspecialchars($mensajeSuccess, ENT_QUOTES) ?>',
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true
});
setTimeout(() => { window.location.href = 'opt.php'; }, 2500);
</script>
<?php endif; ?>

<?php if ($errorCuit): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'CUIT inválido',
  text: 'Por favor ingrese un CUIT válido (11 dígitos).',
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#166379'
});
</script>
<?php endif; ?>

<?php if ($errorTel): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Teléfono inválido',
  text: 'Ingrese los últimos 4 dígitos (solo números).',
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#166379'
});
</script>
<?php endif; ?>

<div class="container">
  <div class="left-panel">
    <div class="logo">
      <img src="../logo.png" alt="Malkoni Hnos" class="logo-img">
    </div>
  </div>

  <div class="right-panel">
    <div class="form-header">
      <h2 class="form-title">ASOCIARSE A OTRA EMPRESA</h2>
      <p class="form-subtitle">
        Completa alguno de los siguientes campos para buscar la empresa a la que se quiere asociar.
      </p>
    </div>

    <form method="post" autocomplete="off">
      <div class="form-group">
        <input type="text" name="nombre" class="form-input"
          placeholder="Nombre o Razón Social"
          value="<?= htmlspecialchars($rawName) ?>">
      </div>
      <div class="form-group">
        <input type="text" name="doc" class="form-input"
          placeholder="CUIT (ej. 20-12345678-6)"
          value="<?= htmlspecialchars($rawDoc) ?>">
      </div>
      <div class="form-group">
        <input type="text" name="telefono" class="form-input"
          placeholder="Últimos 4 dígitos del teléfono"
          value="<?= htmlspecialchars($telefono) ?>">
      </div>
      <div class="form-group">
        <input type="email" name="email" class="form-input"
          placeholder="Email"
          value="<?= htmlspecialchars($email) ?>">
      </div>
      <div class="button-group">
        <button type="button" onclick="location.href='/public/Dashboard/opt.php'"
                class="submit-btn back">Volver</button>
        <button type="submit" class="submit-btn">Buscar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de selección -->
<div id="overlay"></div>
<div id="modal">
  <div class="modal-header">Seleccioná tu empresa:</div>
  <div class="modal-body">
    <ul id="empresa-listado"></ul>
  </div>
  <div class="modal-footer">
    <button class="close-btn" onclick="cerrarModal()">Cerrar</button>
    <button class="select-btn" onclick="seleccionarEmpresa()">Seleccionar</button>
  </div>
</div>

<script>
const coincidencias = <?= json_encode($coincidencias, JSON_UNESCAPED_UNICODE) ?>;
const currentEmpresaId = <?= json_encode($_SESSION['empresa_id'] ?? 0) ?>;
let empresaSeleccionada = null;

function mostrarModal() {
  document.getElementById('overlay').style.display = 'block';
  document.getElementById('modal').style.display = 'flex';
  document.body.style.overflow = 'hidden';

  const list = document.getElementById('empresa-listado');
  list.innerHTML = '';

  coincidencias.forEach(e => {
    const li = document.createElement('li');
    li.innerHTML = `
      <label class="empresa-btn">
        <input type="radio" name="empresa" value="${e.id}" onchange="guardarSeleccion(${e.id})">
        <div class="empresa-info">
          <div class="empresa-name">${e.razon_social}</div>
          <div>CUIT: ${e.cuit}</div>
          <div>Cond. IVA: ${e.cod_cond_iva}</div>
          ${e.validado ? '' : '<div style="color:#dc3545;font-weight:bold;">(No validada)</div>'}
        </div>
      </label>`;
    list.appendChild(li);
  });
}

function guardarSeleccion(id) {
  empresaSeleccionada = coincidencias.find(e => e.id === id);
}

function seleccionarEmpresa() {
  if (!empresaSeleccionada) {
    Swal.fire('Error', 'Debes seleccionar una empresa', 'warning');
    return;
  }
  if (empresaSeleccionada.id === currentEmpresaId) {
    Swal.fire({
      icon: 'info',
      title: 'Sin cambios',
      text: `Ya estás usando '${empresaSeleccionada.razon_social}'.`,
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#166379'
    });
    return;
  }
  if (!empresaSeleccionada.validado) {
    Swal.fire({
      icon: 'error',
      title: 'Empresa no validada',
      text: 'La empresa seleccionada aún no ha completado la validación.',
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#166379'
    });
    return;
  }

  Swal.fire({
    title: '¿Usar esta empresa?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#166379',
    cancelButtonColor: '#6c757d'
  }).then(result => {
    if (result.isConfirmed) {
      window.location.href = '?empresa_id=' + empresaSeleccionada.id;
    }
  });
}

function cerrarModal() {
  document.getElementById('overlay').style.display = 'none';
  document.getElementById('modal').style.display = 'none';
  document.body.style.overflow = 'auto';
}

<?php if (
  $_SERVER['REQUEST_METHOD'] === 'POST'
  && !$errorCuit
  && !$errorTel
  && $rawDoc === '' && $telefono === '' && $email === '' && $rawName === ''
): ?>
Swal.fire({
  icon: 'warning',
  title: 'Atención',
  text: 'Debes completar al menos un campo para buscar.',
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#166379'
});
<?php endif; ?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && count($coincidencias) > 0): ?>
mostrarModal();
<?php elseif (
  $_SERVER['REQUEST_METHOD'] === 'POST'
  && !$errorCuit
  && !$errorTel
  && ($rawDoc !== '' || $telefono !== '' || $email !== '' || $rawName !== '')
  && count($coincidencias) === 0
): ?>
Swal.fire({
  icon: 'info',
  title: 'No se encontraron resultados',
  text: 'Comprueba que los datos ingresados sean correctos.',
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#166379',
  showCancelButton: true,
  cancelButtonText: 'Cerrar'
});
<?php endif; ?>
</script>

</body>
</html>
