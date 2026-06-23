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
    // Variables de sesión inyectadas de forma segura para uso en JS
    const SESSION_USER_ID = <?php echo json_encode($userId); ?>;
    const SESSION_EMPRESA_ID = <?php echo json_encode($empresaIdActiva); ?>;
    const TOKEN_OPT = <?php echo json_encode($tokenOpt); ?>;
    const INTEGRATION_API_URL = 'http://127.0.0.1:8000/api/v1/cotizaciones/importar';
    const INTEGRATION_TOKEN = <?php echo json_encode(getenv('INTEGRATION_TOKEN') ?: 'e40bee85d1d3c3de02b085f7a93210115778f7c14757b674dfa26c62ad1bb704'); ?>;

    // Datos de identidad reales extraídos de Doctrine (Persona y Empresa Activa)
    const PERSONA_NOMBRE = <?php echo json_encode($persona ? $persona->getNombre() : ''); ?>;
    const PERSONA_APELLIDO = <?php echo json_encode($persona ? $persona->getApellido() : ''); ?>;
    const PERSONA_EMAIL = <?php echo json_encode($persona ? $persona->getEmail() : ''); ?>;
    const PERSONA_DNI = <?php echo json_encode($persona ? $persona->getDni() : ''); ?>;
    const PERSONA_GENERO = <?php echo json_encode($persona ? $persona->getGenero() : ''); ?>;
    const PERSONA_TEL = <?php echo json_encode($persona ? $persona->getNumTel() : ''); ?>;
    
    const EMPRESA_RAZON_SOCIAL = <?php echo json_encode($empresaActiva ? $empresaActiva->getRazonSocial() : ''); ?>;
    const EMPRESA_CUIT = <?php echo json_encode($empresaActiva ? $empresaActiva->getCuit() : ''); ?>;
    const EMPRESA_IVA = <?php echo json_encode($empresaActiva ? $empresaActiva->getCodCondIVA() : ''); ?>;

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

    // Botón "Cotizar" -> integración real con API Laravel
    (function(){
      function extractPedidoContext(button) {
        const pedidoId = button?.dataset?.pedidoId || '';

        if (!pedidoId) {
          return null;
        }

        return {
          pedidoId,
          pdfUrl: `https://optionline-prod-files.s3.amazonaws.com/planos/${pedidoId}_.pdf`,
        };
      }

      function buildPayload(context) {
        return {
          persona_external_id: Number.parseInt(SESSION_USER_ID, 10),
          empresa_activa_external_id: Number.parseInt(SESSION_EMPRESA_ID, 10),
          token_opt: TOKEN_OPT,
          pedido_id: Number.parseInt(context.pedidoId, 10),
          pdf_url: context.pdfUrl,
          persona_nombre: PERSONA_NOMBRE,
          persona_apellido: PERSONA_APELLIDO,
          persona_email: PERSONA_EMAIL,
          persona_dni: PERSONA_DNI,
          persona_genero: PERSONA_GENERO,
          persona_tel: PERSONA_TEL,
          empresa_razon_social: EMPRESA_RAZON_SOCIAL,
          empresa_cuit: EMPRESA_CUIT,
          empresa_iva: EMPRESA_IVA
        };
      }

      async function importarCotizacion(payload) {
        const response = await fetch(INTEGRATION_API_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Integration-Token': INTEGRATION_TOKEN
          },
          body: JSON.stringify(payload)
        });

        let data;
        try {
          data = await response.json();
        } catch (_e) {
          data = null;
        }

        if (!response.ok) {
          const details = data?.message || `HTTP ${response.status}`;
          throw new Error(details);
        }

        return data;
      }

      document.querySelectorAll('.btn-cotizar').forEach(btn => {
        btn.addEventListener('click', async () => {
          const context = extractPedidoContext(btn);

          if (!context || !context.pedidoId) {
            Swal.fire({
              icon: 'error',
              title: 'No se pudo procesar el pedido',
              text: 'No se encontró el ID del pedido seleccionado.'
            });
            return;
          }

          const payload = buildPayload(context);

          try {
            Swal.fire({
              title: 'Sincronizando pedido...',
              text: `Generando cotización para el pedido #${context.pedidoId}`,
              showConfirmButton: false,
              allowOutsideClick: false,
              allowEscapeKey: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const data = await importarCotizacion(payload);
            Swal.close();

            if (data?.redirect_url) {
              window.location.href = data.redirect_url;
              return;
            }

            throw new Error('La API no devolvió una redirect_url válida.');
          } catch (error) {
            Swal.close();
            Swal.fire({
              icon: 'error',
              title: 'No se pudo sincronizar el pedido',
              text: error instanceof Error ? error.message : 'Ocurrió un error inesperado durante la integración.'
            });
          }
        });
      });
    })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
