<?php
declare(strict_types=1);

session_start();
if (empty($_SESSION['usuario'])) {
    header('Location: ./login.php');
    exit;
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;

// =====================
// CONFIG
// =====================
$EMPRESA_OPT = 'malkoni';
$OPT_BASE    = 'https://www.optimizadoronline.com';
$S3_BASE     = 'https://optionline-prod-files.s3.amazonaws.com';

// =====================
// Helpers
// =====================
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function planoPdfUrl(string $s3Base, int $idPedido): string {
    return rtrim($s3Base, '/') . '/planos/' . $idPedido . '_.pdf';
}

function unixMsToDate(?int $ms): string {
    if (!$ms) return '';
    $sec = (int) floor($ms / 1000);
    return date('d/m/Y H:i', $sec);
}

function httpGetJson(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $body = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException('cURL error: ' . ($err ?: 'desconocido'));
    }
    if ($http !== 200) {
        throw new RuntimeException("HTTP $http al llamar Opt");
    }

    $data = json_decode((string)$body, true);
    if (!is_array($data)) {
        throw new RuntimeException('Respuesta no es JSON válido');
    }
    return $data;
}

// =====================
// Obtener persona
// =====================
$userId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

$repoPersona = $entityManager->getRepository(Personas::class);
/** @var Personas|null $persona */
$persona = null;

if ($userId > 0) {
    $persona = $entityManager->find(Personas::class, $userId);
}
if (!$persona) {
    $persona = $repoPersona->findOneBy(['email' => $_SESSION['usuario']]);
}
if (!$persona) {
    http_response_code(401);
    echo 'Acceso no autorizado.';
    exit;
}

$tokenOpt = (string)($persona->getTokenOpt() ?? '');
if ($tokenOpt === '') {
    http_response_code(500);
    echo 'Error: este usuario no tiene token_opt cargado.';
    exit;
}

// =====================
// Empresa activa (por si el navbar la muestra)
// =====================
$empresaNombre = '';
$empresaActiva = null;

$empresaIdActiva = isset($_SESSION['empresa_id']) ? (int)$_SESSION['empresa_id'] : 0;
if ($empresaIdActiva > 0) {
    $empresaActiva = $entityManager->getRepository(Empresas::class)->find($empresaIdActiva);
}
if (!$empresaActiva && method_exists($persona, 'getEmpresa')) {
    $empresaActiva = $persona->getEmpresa();
}
if ($empresaActiva && method_exists($empresaActiva, 'getRazonSocial')) {
    $empresaNombre = (string)($empresaActiva->getRazonSocial() ?? '');
}

// =====================
// Traer pedidos
// =====================
$apiUrl = $OPT_BASE . '/empresa/' . rawurlencode($EMPRESA_OPT) . '/proyectos?access_token=' . rawurlencode($tokenOpt);

$pedidos = [];
$errorMsg = null;

try {
    $pedidos = httpGetJson($apiUrl);
} catch (Throwable $e) {
    $errorMsg = $e->getMessage();
}

$rows = [];
if (!$errorMsg && !empty($pedidos)) {
    foreach ($pedidos as $p) {
        $id = (int)($p['id'] ?? 0);
        if ($id <= 0) continue;

        $cant = (int)($p['cant_placas'] ?? 0);
        if ($cant <= 0) continue;

        $rows[] = [
            'id'      => $id,
            'fecha'   => unixMsToDate(isset($p['createdDate']) ? (int)$p['createdDate'] : null),
            'project' => (string)($p['project'] ?? ''),
            'mat'     => (string)($p['mat_descri'] ?? ''),
            'cant'    => $cant,
            'pdf'     => planoPdfUrl($S3_BASE, $id),
        ];
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Malkoni Hnos - Generar Cotización</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Estilos -->
  <link rel="stylesheet" href="styles/navbarStyles.css?v=<?= time() ?>">
  <link rel="stylesheet" href="styles/cotizarMisPedidosStyles.css?v=<?= time() ?>">
</head>

<body class="cotizar-page">

  <?php
    // Navbar para esta pantalla:
    $navbarTitle = 'Generar Cotización';
    $navbarLogoHref = 'opt.php';

    // ✅ Importante: en esta pantalla NO mostramos el botón de "Generar Cotización" del OPT
    $navbarShowCotizarBtn = false;

    // No es contexto opt (así usa altura normal 145px)
    $navbarContext = '';

    require __DIR__ . '/navbar.php';
  ?>

  <div class="container cotizar-container">

    <div class="card section-card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span><i class="bi bi-list-check me-2"></i>Elija el pedido que desea cotizar</span>
        
          <!-- Acciones derecha: Volver + Nuevo Pedido -->
          <div class="d-flex align-items-center gap-2">
            <a href="opt.php" class="btn btn-volver-opt btn-sm">
              <i class="bi bi-arrow-left"></i> Volver
            </a>
          </div>
        </div>


      <div class="card-body">
        <?php if ($errorMsg): ?>
          <div class="err">
            <div class="err-title"><i class="bi bi-exclamation-triangle-fill"></i> No se pudieron traer pedidos</div>
            <div class="err-sub"><?= h($errorMsg) ?></div>
            <div class="err-foot">
              <span class="muted">URL usada:</span>
              <code><?= h($apiUrl) ?></code>
            </div>
          </div>
        <?php else: ?>

          <div class="tools">
            <div class="tool-left">
              <div class="search">
                <i class="bi bi-search"></i>
                <input id="searchInput" type="text" placeholder="Buscar por ID, proyecto o material...">
              </div>
              <div class="count" id="countInfo"></div>
            </div>
          </div>

          <?php if (empty($rows)): ?>
            <div class="empty">
              <div class="empty-icon"><i class="bi bi-inbox"></i></div>
              <div class="empty-title">No hay pedidos para mostrar.</div>
              <div class="empty-sub">Revisá que existan proyectos con placas cargadas en Optimizador Online.</div>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="tabla" id="pedidosTable">
                <thead>
                  <tr>
                    <th>ID Pedido</th>
                    <th>Fecha</th>
                    <th>Proyecto</th>
                    <th>Material</th>
                    <th>Cantidad</th>
                    <th class="th-actions">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $r): ?>
                    <tr>
                      <td class="mono">#<?= (int)$r['id'] ?></td>
                      <td class="cell-tight"><?= h($r['fecha'] ?: '—') ?></td>
                      <td class="cell-tight"><?= h($r['project'] ?: '—') ?></td>
                      <td class="cell-wrap"><?= h($r['mat'] ?: '—') ?></td>
                      <td><span class="pill"><?= (int)$r['cant'] ?></span></td>

                      <td class="acciones">
                        <a class="chip" target="_blank" href="<?= h($r['pdf']) ?>" title="Ver plano">
                          <i class="bi bi-file-earmark-pdf"></i> Plano
                        </a>

                        <button
                          type="button"
                          class="btn btn-primary btn-sm btn-cotizar"
                          data-pedido-id="<?= (int)$r['id'] ?>"
                          title="Cotizar pedido"
                        >
                          <i class="bi bi-receipt-cutoff"></i> Cotizar
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>

      <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="footer-tip">
          <i class="bi bi-lightbulb"></i>
          Tip: podés buscar por proyecto o material para encontrar el pedido correcto.
        </span>
      </div>
    </div>

  </div>

  <script>
    // Buscador + contador
    (function(){
      const input = document.getElementById('searchInput');
      const table = document.getElementById('pedidosTable');
      const info  = document.getElementById('countInfo');
      if (!input || !table) return;

      const rows = Array.from(table.querySelectorAll('tbody tr'));
      const total = rows.length;

      function update(){
        const q = (input.value || '').trim().toLowerCase();
        let shown = 0;

        rows.forEach(tr => {
          const text = tr.innerText.toLowerCase();
          const ok = !q || text.includes(q);
          tr.style.display = ok ? '' : 'none';
          if (ok) shown++;
        });

        if (info) info.textContent = `Mostrando ${shown} de ${total}`;
      }

      input.addEventListener('input', update);
      update();
    })();

    // Botón "Cotizar" -> SweetAlert pro
    (function(){
      document.querySelectorAll('.btn-cotizar').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-pedido-id') || '';

          Swal.fire({
            icon: 'info',
            title: 'Cotización online en desarrollo',
            html: `
              <div class="swal-body">
                <p class="swal-p">
                  Estamos finalizando la implementación para que puedas <b>cotizar tus pedidos de manera online</b>
                  de forma simple y segura.
                </p>
                <p class="swal-p">
                  Muy pronto vas a poder generar la cotización directamente desde este panel.
                </p>
                ${id ? `<div class="swal-chip">Pedido seleccionado: <b>#${id}</b></div>` : ``}
              </div>
            `,
            confirmButtonText: 'Entendido',
            buttonsStyling: false,
            customClass: {
              popup: 'malkoni-swal',
              title: 'malkoni-swal-title',
              htmlContainer: 'malkoni-swal-html',
              confirmButton: 'malkoni-swal-confirm'
            }
          });
        });
      });
    })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
