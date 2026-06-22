<?php
// validar_usuario_cf.php – Malkoni CF
// ===================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

use Entities\Empresas;
use Entities\Personas;

/**
 * Normaliza un DNI: elimina caracteres no numéricos.
 * (Si querés, podés agregar validaciones de longitud, ej. 7–9 dígitos.)
 */
function normalizarDni(?string $dni): string {
    if ($dni === null) return '';
    return preg_replace('/\D/', '', $dni);
}

// === Handler AJAX para comprobar si ya existe usuario CF ===
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
) {
    header('Content-Type: application/json; charset=utf-8');

    $payload   = json_decode(file_get_contents('php://input'), true);
    $idEmpresa = $payload['id_empresa'] ?? null;

    if (!$idEmpresa) {
        echo json_encode(['error' => 'Falta ID']);
        exit;
    }

    try {
        /** @var Empresas|null $empresa */
        $empresa      = $entityManager->getRepository(Empresas::class)->find($idEmpresa);
        if (!$empresa) {
            echo json_encode(['error' => 'Empresa no encontrada']);
            exit;
        }
        $repoPers     = $entityManager->getRepository(Personas::class);
        $usuarios     = $repoPers->findBy(['empresa' => $empresa]);
        $tieneUsuario = count($usuarios) > 0;

        echo json_encode([
            'existe_usuario' => $tieneUsuario
        ]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
    }
    exit;
}

// === Búsqueda normal tras submit (no AJAX) ===
$coincidencias = [];
$rawDni        = trim($_POST['doc']      ?? '');
$telefono      = trim($_POST['telefono'] ?? '');
$email         = trim($_POST['email']    ?? '');
$rawName       = trim($_POST['nombre']   ?? '');

$errorTel = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación de teléfono (últimos 4 dígitos)
    if ($telefono !== '' && !preg_match('/^\d{4,}$/', $telefono)) {
        $errorTel = true;
    }

    // Si no hay errores y hay algún dato para buscar
    if (
        !$errorTel
        && (
            $rawDni   !== ''
         || $telefono !== ''
         || $email    !== ''
         || $rawName  !== ''
        )
    ) {
        $qb = $entityManager->createQueryBuilder()
            ->select('e')
            ->from(Empresas::class, 'e');

        $orX = [];

        // == CAMBIO PRINCIPAL: validar por DNI en Empresas ==
        if ($rawDni !== '') {
            $dniClean = normalizarDni($rawDni);
            if ($dniClean !== '') {
                // Comparación exacta por DNI (tabla Empresas)
                $orX[] = $qb->expr()->eq('e.dni', ':dni');
                $qb->setParameter('dni', $dniClean);
            }
        }

        if ($email !== '') {
            $orX[] = $qb->expr()->eq('e.email', ':email');
            $qb->setParameter('email', $email);
        }
        if ($telefono !== '') {
            // últimos 4 dígitos: usamos LIKE %xxxx
            $orX[] = $qb->expr()->like('e.num_tel', ':tel');
            $qb->setParameter('tel', "%$telefono");
        }
        if ($rawName !== '') {
            $orX[] = $qb->expr()->like('e.razon_social', ':nombre');
            $qb->setParameter('nombre', "%$rawName%");
        }

        if (count($orX)) {
            // aplicamos los filtros de búsqueda
            $qb->where(call_user_func_array([$qb->expr(), 'orX'], $orX));
            // y filtramos solo consumidores finales (CF)
            $qb->andWhere($qb->expr()->eq('e.codCondIVA', ':cf'))
               ->setParameter('cf', 'CF');

            $empresas = $qb->getQuery()->getResult();

            /** @var Empresas $e */
            foreach ($empresas as $e) {
                // Tomamos los campos (pueden ser null/empty)
                $dni      = method_exists($e, 'getDni')      ? (string) ($e->getDni() ?? '')      : '';
                $tel      = method_exists($e, 'getNumTel')   ? (string) ($e->getNumTel() ?? '')   : '';
                $mail     = method_exists($e, 'getEmail')    ? (string) ($e->getEmail() ?? '')    : '';
                $razon    = method_exists($e, 'getRazonSocial') ? (string) ($e->getRazonSocial() ?? '') : '';

                $coincidencias[] = [
                    'id'           => $e->getId(),
                    'razon_social' => $razon,
                    'dni'          => $dni,      // Se formatea en el front: "No especificado" si viene vacío
                    'telefono'     => $tel,      // Se formatea en el front: "No especificado" si viene vacío
                    'email'        => $mail      // Se muestra “—” si viene vacío
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
    <title>Validar Consumidor Final – Malkoni</title>
    <link rel="stylesheet" href="styles/validarUsuarioStyles.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if ($errorTel): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Formato de Teléfono inválido',
        text: 'Por favor ingresa los últimos 4 dígitos del teléfono (solo números).',
        confirmButtonColor: '#166379'
      });
    </script>
    <?php endif; ?>

    <div class="container">
        <div class="left-panel">
            <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
        </div>
        <div class="right-panel">
            <div class="form-header">
                <h2 class="form-title">CONSUMIDOR FINAL</h2>
                <p class="form-subtitle">
                    Ingresa alguno de los siguientes datos para verificar tu registro.
                </p>
            </div>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <input
                      type="text"
                      name="nombre"
                      class="form-input"
                      placeholder="Nombre y Apellido"
                      value="<?= htmlspecialchars($rawName) ?>">
                </div>
                <div class="form-group">
                    <input
                      type="text"
                      name="doc"
                      class="form-input"
                      placeholder="DNI"
                      value="<?= htmlspecialchars($rawDni) ?>">
                </div>
                <div class="form-group">
                    <input
                      type="text"
                      name="telefono"
                      class="form-input"
                      placeholder="Últimos 4 dígitos del teléfono"
                      value="<?= htmlspecialchars($telefono) ?>">
                </div>
                <div class="form-group">
                    <input
                      type="email"
                      name="email"
                      class="form-input"
                      placeholder="Email"
                      value="<?= htmlspecialchars($email) ?>">
                </div>
                <div class="button-group">
                    <button type="submit" class="submit-btn">Verificar</button>
                    <button type="button" onclick="location.href='tipo_identidad.php'" class="submit-btn back">Volver</button>
                    <button type="button" onclick="location.href='registro_cf.php'" class="submit-btn nueva">Nuevo usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de selección -->
    <div id="overlay"></div>
    <div id="modal">
        <div class="modal-header">Seleccioná tu consumidor final:</div>
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
  let empresaSeleccionada = null;

  function fmtNoEspecificado(v, placeholder = 'No especificado') {
    if (v === null || v === undefined) return placeholder;
    const s = String(v).trim();
    return s === '' ? placeholder : s;
  }
  function fmtEmail(v) {
    if (v === null || v === undefined) return '—';
    const s = String(v).trim();
    return s === '' ? '—' : s;
  }

  function mostrarModal() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('modal').style.display   = 'flex';
    document.body.style.overflow                      = 'hidden';
    const listado = document.getElementById('empresa-listado');
    listado.innerHTML = '';

    coincidencias.forEach(e => {
      const li = document.createElement('li');
      li.innerHTML = `
        <label class="empresa-btn">
          <input type="radio" name="empresa" value="${e.id}" onchange="guardarSeleccion(${e.id})">
          <div class="empresa-info">
            <div class="empresa-name">${e.razon_social ?? ''}</div>
            <div>DNI: ${fmtNoEspecificado(e.dni)}</div>
           <!-- <div>Email: ${fmtEmail(e.email)}</div> --> 
         <!--   <div>Teléfono: ${fmtNoEspecificado(e.telefono)}</div> -->
          </div>
        </label>`;
      listado.appendChild(li);
    });
  }

  function guardarSeleccion(id) {
    empresaSeleccionada = coincidencias.find(e => e.id === id);
  }

  function seleccionarEmpresa() {
    if (!empresaSeleccionada) {
      Swal.fire('Error', 'Debes seleccionar un consumidor final', 'warning');
      return;
    }
    fetch('validar_usuario_cf.php', {
      method: 'POST',
      headers: {
        'Content-Type':       'application/json',
        'X-Requested-With':   'XMLHttpRequest'
      },
      body: JSON.stringify({ id_empresa: empresaSeleccionada.id })
    })
    .then(res => res.ok ? res.json() : Promise.reject(`Status ${res.status}`))
    .then(data => {
      if (data.error) throw new Error(data.error);
      if (data.existe_usuario) {
        Swal.fire({
          icon: 'info',
          title: 'Usuario ya registrado',
          text: 'Ya has creado un usuario para ingresar a Malkoni Hnos. Por favor, inicia sesión.',
          confirmButtonText: 'Iniciar Sesión',
          confirmButtonColor: '#166379'
        }).then(result => {
          if (result.isConfirmed) {
            window.location.assign('login.php');
          }
        });
      } else {
        // Pasar empresa_id y limpiar flujo
        window.location.assign('registro_cf.php?clear=1&empresa_id=' + encodeURIComponent(empresaSeleccionada.id));
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire('Error interno', String(err), 'error');
    });
  }

  function cerrarModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('modal').style.display   = 'none';
    document.body.style.overflow                      = 'auto';
  }

  if ('<?= ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errorTel) ? '1' : '0' ?>' === '1') {
    const anyFilled = <?= ($rawDni !== '' || $telefono !== '' || $email !== '' || $rawName !== '') ? 'true' : 'false' ?>;
    if (coincidencias.length > 0) {
      mostrarModal();
    } else if (anyFilled) {
      Swal.fire({
        icon: 'info',
        html: `
          <h2 style="font-family: 'Syncopate', sans-serif; font-size:1.6rem; margin-bottom:.5em;">
            No se han encontrado coincidencias
          </h2>
          <p style="font-family: 'Satoshi', sans-serif; font-size:1.1rem;">
            Parece que no te hemos encontrado en nuestra base de datos.<br>¿Deseas registrarte?
          </p>`,
        showCancelButton: true,
        confirmButtonText: 'Registrarme',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#166379',
        cancelButtonColor: '#6c757d',
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(result => {
        if (result.isConfirmed) {
          window.location.assign('registro_cf.php');
        }
      });
    } else {
      Swal.fire({
        icon: 'warning',
        html: `
          <h2 style="font-family: 'Syncopate', sans-serif; font-size:1.6rem; margin-bottom:.5em;">
            No se ingresó ningún dato
          </h2>
          <p style="font-family: 'Satoshi', sans-serif; font-size:1rem;">
            Completa al menos un campo para verificar tu registro.
          </p>`
      });
    }
  }
</script>
</body>
</html>
