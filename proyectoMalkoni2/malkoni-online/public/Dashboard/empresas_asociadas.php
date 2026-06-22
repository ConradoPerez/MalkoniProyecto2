<?php
// empresas_asociadas.php
// Muestra empresas asociadas (rol=2, no CF), indicando principal y activa
// + Permite desasociarse de una empresa (estado=0 en EmpresasPersonas)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

// 1) Verifico sesión
$userId = $_SESSION['id'] ?? null;
if (empty($_SESSION['usuario']) || !$userId) {
    header('Location: login.php');
    exit;
}

// Mensajes
$mensajeError   = $_SESSION['errorMensaje']   ?? null;
$mensajeSuccess = $_SESSION['successMensaje'] ?? null;
unset($_SESSION['errorMensaje'], $_SESSION['successMensaje']);

// 2) Cargo persona
/** @var Personas|null $usuario */
$usuario = $entityManager->find(Personas::class, (int)$userId);
if (!$usuario) {
    die('Acceso no autorizado');
}

// 3) Solo rol=2
if ((int)$usuario->getRol() !== 2) {
    header('Location: opt.php');
    exit;
}

// 4) Empresa principal (Personas.id_empresa)
$empresaPrincipal = $usuario->getEmpresa();
if (!$empresaPrincipal) {
    die('Acceso no autorizado');
}
$empresaPrincipalId = (int)$empresaPrincipal->getId();

// 5) Empresa activa (sesión) con fallback a principal
$empresaIdActiva = (int)($_SESSION['empresa_id'] ?? 0);
if ($empresaIdActiva <= 0) {
    $empresaIdActiva = $empresaPrincipalId;
    $_SESSION['empresa_id'] = $empresaIdActiva;
}

$empresaActiva = $entityManager->getRepository(Empresas::class)->find($empresaIdActiva);
if (!$empresaActiva) {
    $empresaActiva = $empresaPrincipal;
    $_SESSION['empresa_id'] = $empresaPrincipalId;
    $empresaIdActiva = $empresaPrincipalId;
}

// 6) Restringir CF
$codCondIVA = $empresaActiva->getCodCondIVA() ?: ($empresaPrincipal->getCodCondIVA() ?: '');
$isConsumidorFinal = ($codCondIVA === 'CF');
if ($isConsumidorFinal) {
    header('Location: opt.php');
    exit;
}

// ============================
// ✅ NUEVO: DESASOCIAR EMPRESA
// ============================
if (isset($_GET['desasociar_id'])) {
    $desId = (int)$_GET['desasociar_id'];

    // No se puede desasociar la principal
    if ($desId === $empresaPrincipalId) {
        $_SESSION['errorMensaje'] = 'No podés desasociarte de tu empresa principal.';
        header('Location: empresas_asociadas.php');
        exit;
    }

    /** @var Empresas|null $empresaADes */
    $empresaADes = $entityManager->getRepository(Empresas::class)->find($desId);
    if (!$empresaADes) {
        $_SESSION['errorMensaje'] = 'Empresa inexistente.';
        header('Location: empresas_asociadas.php');
        exit;
    }

    $repoEP = $entityManager->getRepository(EmpresasPersonas::class);

    /** @var EmpresasPersonas|null $vinculo */
    $vinculo = $repoEP->findOneBy([
        'persona' => $usuario,
        'empresa' => $empresaADes,
        'estado'  => 1
    ]);

    if (!$vinculo) {
        $_SESSION['errorMensaje'] = 'No existe un vínculo activo con esa empresa.';
        header('Location: empresas_asociadas.php');
        exit;
    }

    // Si es la empresa activa, volvemos a la principal antes de desasociar
    if ($desId === $empresaIdActiva) {
        $_SESSION['empresa_id'] = $empresaPrincipalId;

        // Persistir empresa activa (si tenés el campo)
        if (method_exists($usuario, 'setEmpresaActiva')) {
            $usuario->setEmpresaActiva($empresaPrincipal);
        }
    }

    // Desasociar => estado=0
    if (method_exists($vinculo, 'setEstado')) {
        $vinculo->setEstado(0);
    } else {
        // si tu entidad no tiene setter (raro), al menos avisamos
        $_SESSION['errorMensaje'] = 'No se pudo desasociar (falta setEstado en la entidad).';
        header('Location: empresas_asociadas.php');
        exit;
    }

    $entityManager->flush();

    $_SESSION['successMensaje'] = 'Te desasociaste de «' . ($empresaADes->getRazonSocial() ?: 'Empresa') . '».';
    header('Location: empresas_asociadas.php');
    exit;
}

// 7) Traer empresas asociadas desde tabla intermedia
$repoEP = $entityManager->getRepository(EmpresasPersonas::class);
$vinculos = $repoEP->findBy(['persona' => $usuario, 'estado' => 1]);

$empresasMap = [];
$empresasMap[$empresaPrincipalId] = $empresaPrincipal;

foreach ($vinculos as $v) {
    $e = $v->getEmpresa();
    if ($e) {
        $empresasMap[(int)$e->getId()] = $e;
    }
}

// Ordenar por razón social
$empresas = array_values($empresasMap);
usort($empresas, function(Empresas $a, Empresas $b) {
    return strcmp((string)$a->getRazonSocial(), (string)$b->getRazonSocial());
});

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Malkoni Hnos - Empresas asociadas</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Estilos -->
  <link rel="stylesheet" href="styles/navbarStyles.css">
  <link rel="stylesheet" href="styles/empresasAsociadasStyles.css?v=<?= time() ?>">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <?php if ($mensajeError): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?= h($mensajeError) ?>',
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
        text: '<?= h($mensajeSuccess) ?>',
        timer: 1800,
        showConfirmButton: false
      });
    </script>
  <?php endif; ?>

  <div class="container empresas-container">
    <div class="row gx-4 align-items-stretch">

      <!-- Panel izquierdo -->
      <div class="col-12 col-md-4 mb-4 d-flex">
        <div class="card summary-card flex-fill h-100">
          <div class="card-body text-center d-flex flex-column justify-content-center">
            <h3 class="summary-title mb-2">Empresas asociadas</h3>
            <p class="summary-sub mb-0">
              Podés ver tu empresa principal, la empresa activa y cambiar el contexto sin perder asociaciones.
            </p>
          </div>
        </div>
      </div>

      <!-- Panel derecho -->
      <div class="col-12 col-md-8 d-flex flex-column">
        <div class="card section-card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Listado de empresas</span>
            <span class="badge bg-light text-dark">
              Activa: <?= h($empresaActiva->getRazonSocial()) ?>
            </span>
          </div>

          <div class="card-body">
            <?php if (count($empresas) === 0): ?>
              <p class="text-muted mb-0">No tenés empresas asociadas.</p>
            <?php else: ?>
              <div class="list-group empresas-list">
                <?php foreach ($empresas as $e):
                  $id = (int)$e->getId();
                  $isPrincipal = ($empresaPrincipalId === $id);
                  $isActiva    = ($empresaIdActiva === $id);
                ?>
                  <div class="list-group-item empresa-item">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div class="empresa-info">
                        <div class="empresa-title">
                          <?= h($e->getRazonSocial() ?: '—') ?>
                        </div>
                        <div class="empresa-meta">
                          <span><strong>CUIT:</strong> <?= h($e->getCuit() ?: '—') ?></span>
                          <span class="dot">•</span>
                          <span><strong>IVA:</strong> <?= h($e->getCodCondIVA() ?: '—') ?></span>
                        </div>

                        <div class="empresa-badges mt-2">
                          <?php if ($isPrincipal): ?>
                            <span class="pill pill-principal"><i class="bi bi-star-fill"></i> Principal</span>
                          <?php endif; ?>
                          <?php if ($isActiva): ?>
                            <span class="pill pill-activa"><i class="bi bi-check-circle-fill"></i> Activa</span>
                          <?php endif; ?>
                          <?php if (!$e->isValidado()): ?>
                            <span class="pill pill-no"><i class="bi bi-exclamation-triangle-fill"></i> No validada</span>
                          <?php else: ?>
                            <span class="pill pill-ok"><i class="bi bi-patch-check-fill"></i> Validada</span>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="empresa-actions d-flex gap-2">
                        <?php if ($isActiva): ?>
                          <button class="btn btn-secondary btn-sm" disabled>En uso</button>
                        <?php elseif (!$e->isValidado()): ?>
                          <button class="btn btn-outline-danger btn-sm" disabled>No disponible</button>
                        <?php else: ?>
                          <a class="btn btn-primary btn-sm"
                             href="set_empresa_activa.php?empresa_id=<?= $id ?>">
                             Usar
                          </a>
                        <?php endif; ?>
                          <button
                              type="button"
                              class="btn btn-outline-danger btn-sm desasociar-btn <?= $isPrincipal ? 'is-disabled' : '' ?>"
                              <?= $isPrincipal
                                  ? 'disabled title="No te podés desasociar de esta empresa porque es la principal"'
                                  : 'onclick="confirmarDesasociar(' . $id . ', \'' . h($e->getRazonSocial() ?: 'Empresa') . '\', ' . ($isActiva ? 'true' : 'false') . ')"'
                              ?>
                            >
                              Desasociar
                            </button>
                      </div>

                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="card-footer d-flex justify-content-between align-items-center">
            <a href="opt.php" class="btn btn-outline-light btn-sm">Volver</a>
            <a href="cambiar_empresa.php" class="btn btn-outline-warning btn-sm">Buscar otra empresa</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    function confirmarDesasociar(id, razon, eraActiva) {
      const texto = eraActiva
        ? `Estás usando "${razon}". Si confirmás, se cambiará a tu empresa principal y luego se desasociará.`
        : `¿Seguro que querés desasociarte de "${razon}"?`;

      Swal.fire({
        icon: 'warning',
        title: 'Confirmar desasociación',
        text: texto,
        showCancelButton: true,
        confirmButtonText: 'Sí, desasociar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
      }).then(r => {
        if (r.isConfirmed) {
          window.location.href = 'empresas_asociadas.php?desasociar_id=' + encodeURIComponent(id);
        }
      });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
