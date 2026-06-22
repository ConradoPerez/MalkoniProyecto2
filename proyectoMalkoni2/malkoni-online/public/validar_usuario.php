<?php
// validar_usuario.php – Malkoni
// ==============================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

use Entities\Empresas;
use Entities\Personas;
use Entities\EmpresasPersonas;

/**
 * Comprueba que el CUIT (solo dígitos) tenga 11 dígitos y dígito verificador correcto.
 */
function validarCuit(string $cuit): bool {
    $cuit = preg_replace('/\D/', '', $cuit);
    if (strlen($cuit) !== 11) {
        return false;
    }
    $digits      = str_split($cuit);
    $multipliers = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $sum         = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += intval($digits[$i]) * $multipliers[$i];
    }
    $mod      = $sum % 11;
    $expected = 11 - $mod;
    if ($expected === 11) {
        $expected = 0;
    } elseif ($expected === 10) {
        $expected = 9;
    }
    return intval($digits[10]) === $expected;
}

// === Handler AJAX para comprobar si ya existe usuario ===
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0
) {
    header('Content-Type: application/json; charset=utf-8');

    $payload   = json_decode(file_get_contents('php://input'), true);
    $idEmpresa = isset($payload['id_empresa']) ? (int)$payload['id_empresa'] : 0;

    if ($idEmpresa <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta ID de empresa válido']);
        exit;
    }

    try {
        /** @var Empresas|null $empresa */
        $empresa = $entityManager->find(Empresas::class, $idEmpresa);
        if (!$empresa) {
            http_response_code(404);
            echo json_encode(['error' => 'Empresa no encontrada']);
            exit;
        }

        // 1) Usuarios por tabla intermedia (asociaciones)
        $qb2 = $entityManager->createQueryBuilder();

        $usuariosJoin = $qb2
            ->select('p')
            ->from(Personas::class, 'p')
            ->innerJoin(EmpresasPersonas::class, 'ep', 'WITH', 'ep.persona = p')
            ->where('ep.empresa = :emp')
            ->andWhere('ep.estado = :estado')
            ->setParameter('emp', $empresa)
            ->setParameter('estado', 1)
            ->orderBy('p.apellido', 'ASC')
            ->addOrderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();

        // 2) (Opcional) Usuarios “legacy” por Personas.empresa
        $repoPers = $entityManager->getRepository(Personas::class);
        $usuariosDirectos = $repoPers->findBy(['empresa' => $empresa]);

        // 3) Unificar sin duplicados por ID
        $map = [];
        foreach (array_merge($usuariosJoin, $usuariosDirectos) as $u) {
            if (!$u instanceof Personas) continue;
            $map[(int)$u->getId()] = $u;
        }
        $usuariosEntities = array_values($map);

        $usuarios = array_map(function (Personas $u) {
            return [
                'nombre'   => $u->getNombre(),
                'apellido' => $u->getApellido(),
                'email'    => $u->getEmail(),
                'rol'      => $u->getRol(),
            ];
        }, $usuariosEntities);

        echo json_encode([
            'existe_usuario' => count($usuariosEntities) > 0,
            'usuarios'       => $usuarios,
        ]);
        exit;

    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            // dejalo así mientras debuggeás
            'error' => 'Error interno: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine()
        ]);
        exit;
    }
}


// === Búsqueda normal tras submit (no AJAX) ===
$coincidencias = [];
$rawDoc        = trim($_POST['doc']      ?? '');
$telefono      = trim($_POST['telefono'] ?? '');
$email         = trim($_POST['email']    ?? '');
$rawName       = trim($_POST['nombre']   ?? '');

$errorCuit     = false;
$errorTel      = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuitClean = preg_replace('/\D/', '', $rawDoc);

    if ($rawDoc !== '' && !validarCuit($cuitClean)) {
        $errorCuit = true;
    }
    if ($telefono !== '' && !preg_match('/^\d{4,}$/', $telefono)) {
        $errorTel = true;
    }

    if (
        !$errorCuit
        && !$errorTel
        && (
            $cuitClean !== ''
         || $telefono  !== ''
         || $email     !== ''
         || $rawName   !== ''
        )
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
        $qb->where($qb->expr()->orX(...$orX))
           ->andWhere($qb->expr()->in('e.codCondIVA', ':ivas'))
           ->setParameter('ivas', ['MT', 'RI', 'EX']);
           
            // ejecutamos
            $empresas = $qb->getQuery()->getResult();
        
            foreach ($empresas as $e) {
                $coincidencias[] = [
                    'id'           => $e->getId(),
                    'razon_social' => $e->getRazonSocial(),
                    'cuit'         => $e->getCuit(),
                    'cod_cond_iva' => $e->getCodCondIVA(),
                    'email'        => $e->getEmail(),
                    'num_tel'      => $e->getNumTel(),
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
    <title>Validar Empresa – Malkoni</title>
    <link rel="stylesheet" href="styles/validarUsuarioStyles.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if ($errorCuit): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Formato de CUIT inválido',
        text: 'Por favor ingrese un CUIT válido (11 dígitos).',
        confirmButtonColor: '#166379'
      });
    </script>
    <?php endif; ?>

    <?php if ($errorTel): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Formato de Teléfono inválido',
        text: 'Por favor ingrese los últimos 4 dígitos del teléfono (solo números).',
        confirmButtonColor: '#166379'
      });
    </script>
    <?php endif; ?>

    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
            </div>
        </div>
        <div class="right-panel">
            <div class="form-header">
                <h2 class="form-title">EMPRESAS</h2>
                <p class="form-subtitle">
                    Complete alguno de los siguientes campos para verificar el registro de su empresa en nuestros sistemas. De lo contrario, seleccione el botón "Nueva empresa".
                </p>
            </div>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <input
                      type="text"
                      name="nombre"
                      class="form-input"
                      placeholder="Nombre o Razón Social"
                      value="<?= htmlspecialchars($rawName) ?>">
                </div>
                <div class="form-group">
                    <input
                      type="text"
                      name="doc"
                      class="form-input"
                      placeholder="CUIT (ej. 20-12345678-6)"
                      value="<?= htmlspecialchars($rawDoc) ?>">
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
                    <button type="button" onclick="location.href='registro_cuit.php?paso=1&clear=1'" class="submit-btn nueva">Nueva empresa</button>
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

<?php if ($errorCuit): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Formato de CUIT inválido',
    text: 'Por favor ingrese un CUIT válido (11 dígitos).',
    confirmButtonColor: '#166379'
  });
</script>
<?php endif; ?>

<?php if ($errorTel): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Formato de Teléfono inválido',
    text: 'Por favor ingrese los últimos 4 dígitos del teléfono (solo números).',
    confirmButtonColor: '#166379'
  });
</script>
<?php endif; ?>

<script>
  const coincidencias = <?= json_encode($coincidencias, JSON_UNESCAPED_UNICODE) ?>;
  let empresaSeleccionada = null;

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
            <div class="empresa-name">${e.razon_social}</div>
            <div>CUIT: ${e.cuit}</div>
            <div>Cond. IVA: ${e.cod_cond_iva}</div>
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
      Swal.fire('Error', 'Debes seleccionar una empresa', 'warning');
      return;
    }
    fetch('validar_usuario.php', {
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
        const admin = data.usuarios.find(u => u.rol === 1);
        const usuariosHtml = `
          <div style="text-align:left; margin:1rem 0;">
            <ul style="padding-left:1.2rem; line-height:1.6;">
              ${data.usuarios.map(u => {
                const label = `${u.nombre} ${u.apellido} (${u.email})`;
                return u.rol === 1
                  ? `<li><strong>${label}</strong> <em>- Administrador</em></li>`
                  : `<li>${label}</li>`;
              }).join('')}
            </ul>
          </div>`;
        const htmlContent = `
          <p>
            Podés iniciar sesión o agregar un usuario adicional.
            ${admin ? 'Estos son los usuarios asociados a la cuenta:' : ''}
          </p>
          ${usuariosHtml}`;
        return Swal.fire({
          icon: 'info',
          title: 'La empresa ya tiene usuarios',
          html: htmlContent,
          confirmButtonText: 'Iniciar Sesión',
          confirmButtonColor: '#166379',
          showDenyButton: true,
          denyButtonText: 'Agregar usuario',
          denyButtonColor: '#8A9A9C',
          showCancelButton: true,
          cancelButtonText: 'Volver',
          cancelButtonColor: '#6c757d',
          focusConfirm: false,
          width: 650
        }).then(result => {
          if (result.isConfirmed) {
            window.location.assign('login.php');
          } else if (result.isDenied) {
            window.location.assign(
              'SolicitarUsuario.php?empresa_id=' + empresaSeleccionada.id
            );
          }
        });
      }
      // No había usuarios → seguimos al registro
      window.location.assign(
        'registro.php?clear=1&empresa_id=' + empresaSeleccionada.id + '&paso=1'
      );
    })
    .catch(err => {
      console.error(err);
      Swal.fire('Error interno', err.message, 'error');
    });
  }

  function cerrarModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('modal').style.display   = 'none';
    document.body.style.overflow                      = 'auto';
  }

  // Al enviar el formulario (POST normal)
  if ('<?= ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errorCuit && !$errorTel) ? '1' : '0' ?>' === '1') {
    const anyFilled = <?= ($rawDoc !== '' || $telefono !== '' || $email !== '' || $rawName !== '') ? 'true' : 'false' ?>;

    if (coincidencias.length > 0) {
      mostrarModal();
    }
    else if (anyFilled) {
      // No se han encontrado coincidencias — mismo formato que la alerta de inputs vacíos
      Swal.fire({
        icon: 'info',
        html: `
          <h2 style="
            font-family: 'Syncopate', sans-serif;
            font-size: 1.6rem;
            margin-bottom: .5em;
          ">
            No se han encontrado coincidencias
          </h2>
          <p style="
            font-family: 'Satoshi', sans-serif;
            font-size: 1rem;
            margin: 0;
          ">
            No se encontraron resultados para los datos ingresados.<br>
            ¿Deseas crear una nueva empresa?
          </p>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar empresa',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#166379',
        cancelButtonColor: '#6c757d',
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(result => {
        if (result.isConfirmed) {
          window.location.assign('registro.php?clear=1&cuit=' + encodeURIComponent(<?= json_encode($rawDoc) ?>) + '&paso=1');
        }
        // Al “Volver” simplemente cierra la alerta
      });
    }
    else {
      // Inputs vacíos: misma tipografía y tamaño que alertas de error
      Swal.fire({
        icon: 'warning',
        html: `
          <h2 style="
            font-family: 'Syncopate', sans-serif;
            font-size: 1.6rem;
            margin-bottom: .5em;
          ">
            No se completó ningún dato
          </h2>
          <p style="
            font-family: 'Satoshi', sans-serif;
            font-size: 1rem;
            margin:0;
          ">
            Si desea registrarse presione aquí debajo el botón 'Registrar empresa'.
          </p>`,
        showCancelButton: true,
        confirmButtonText: 'Registrar empresa',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#166379',
        cancelButtonColor: '#6c757d',
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(result => {
        if (result.isConfirmed) {
          window.location.assign('registro.php');
        }
      });
    }
  }
</script>
</body>
</html>
