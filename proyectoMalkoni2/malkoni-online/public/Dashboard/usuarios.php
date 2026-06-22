<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

// Generar token OPT
define('TOKEN_LENGTH', 20);
function generarTokenOPT(int $length = TOKEN_LENGTH): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $token;
}

$entityManager = require __DIR__ . '/../../config/doctrine.php';

// Usuario actual y empresa
$usuarioId     = $_SESSION['id'] ?? null;
$usuarioActual = $usuarioId
    ? $entityManager->find(Personas::class, $usuarioId)
    : null;

$empresaObj = $usuarioActual && $usuarioActual->getEmpresa()
    ? $usuarioActual->getEmpresa()
    : null;

$idEmpresa = $empresaObj ? (int)$empresaObj->getId() : null;

if (!$idEmpresa) {
    die('Acceso no autorizado');
}

/**
 * Devuelve true si la persona pertenece a la empresa:
 * - por Personas.empresa (legacy)
 * - o por la intermedia EmpresasPersonas
 */
function personaPerteneceEmpresa($entityManager, Personas $p, int $idEmpresa): bool
{
    // 1) vínculo directo
    if ($p->getEmpresa() && (int)$p->getEmpresa()->getId() === $idEmpresa) {
        return true;
    }

    // 2) vínculo por intermedia
    $ep = $entityManager->getRepository(EmpresasPersonas::class)->findOneBy([
        'persona' => $p,
        'empresa' => $idEmpresa,
    ]);

    return $ep !== null;
}

/**
 * Asegura que exista la relación en la intermedia (empresa-persona).
 * Si ya existe, no hace nada.
 */
function asegurarRelacionIntermedia($entityManager, Personas $p, $empresaObj): void
{
    $repoEp = $entityManager->getRepository(EmpresasPersonas::class);

    $ex = $repoEp->findOneBy([
        'persona' => $p,
        'empresa' => $empresaObj,
    ]);

    if (!$ex) {
        $ep = new EmpresasPersonas();
        $ep->setPersona($p);
        $ep->setEmpresa($empresaObj);
        $entityManager->persist($ep);
    }
}

/**
 * Desasocia persona de empresa (borra fila de intermedia).
 * Devuelve true si borró algo.
 */
function desasociarDeEmpresa($entityManager, Personas $p, $empresaObj): bool
{
    $repoEp = $entityManager->getRepository(EmpresasPersonas::class);
    $ex = $repoEp->findOneBy([
        'persona' => $p,
        'empresa' => $empresaObj,
    ]);

    if ($ex) {
        $entityManager->remove($ex);
        return true;
    }
    return false;
}

// =====================================================
// ELIMINAR
// - Si pertenece por intermedia => desasocia (NO borra la persona)
// =====================================================
// DESASOCIAR (NO ELIMINAR de Personas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $toDel = (int)$_POST['eliminar_id'];

    /** @var Personas|null $p */
    $p = $entityManager->find(Personas::class, $toDel);

    if ($p) {
        // Empresa actual (objeto) para comparar
        $empresaActual = $usuarioActual->getEmpresa();

        // 1) Borrar relación en tabla intermedia (EmpresasPersonas)
        //    Puede haber 0 o 1 registro (según cómo lo estés usando)
        $repoEP = $entityManager->getRepository(EmpresasPersonas::class);

        // Si tu entidad EmpresasPersonas mapea como 'empresa' y 'persona' (muchos-a-uno)
        $ep = $repoEP->findOneBy([
            'empresa' => $empresaActual,
            'persona' => $p
        ]);

        if ($ep) {
            $entityManager->remove($ep);
        }

        // 2) Compatibilidad: si todavía existe el FK Personas->empresa y apunta a esta empresa, lo cortamos
        if (method_exists($p, 'getEmpresa') && $p->getEmpresa() && $p->getEmpresa()->getId() === $empresaActual->getId()) {
            if (method_exists($p, 'setEmpresa')) {
                $p->setEmpresa(null);
            }
        }

        $entityManager->flush();
        $_SESSION['mensaje'] = 'Usuario desasociado correctamente';
    }

    header('Location: usuarios.php');
    exit;
}


// =====================================================
// CAMBIO DE ESTADO
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id           = (int)$_POST['id_usuario'];
    $nuevo_estado = (int)$_POST['nuevo_estado'];
    $u            = $entityManager->find(Personas::class, $id);

    if ($u && personaPerteneceEmpresa($entityManager, $u, $idEmpresa)) {
        $u->setEstadoPersona($nuevo_estado);
        $entityManager->flush();
        $_SESSION['mensaje'] = $nuevo_estado === 1
            ? 'Usuario habilitado correctamente'
            : 'Usuario inhabilitado correctamente';
    }

    header('Location: usuarios.php');
    exit;
}

// =====================================================
// CREAR / EDITAR
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !isset($_POST['eliminar_id'])
    && !isset($_POST['cambiar_estado'])
) {
    $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni      = strlen($_POST['dni'] ?? '') ? (int)$_POST['dni'] : null;
    $genero   = $_POST['genero'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $pass     = $_POST['pass'] ?? '';
    if (!$id || $pass) {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $pass)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula y un número.';
            header('Location: usuarios.php');
            exit;
        }
    }
    $rol      = isset($_POST['rol']) ? (int)$_POST['rol'] : 2;
    $tel      = trim($_POST['telefono'] ?? '');

    $repo = $entityManager->getRepository(Personas::class);

    // Validaciones al crear
    if (!$id) {
        if ($repo->findOneBy(['email' => $email])) {
            $_SESSION['error'] = 'El email ya está registrado';
            header('Location: usuarios.php'); exit;
        }
        if ($dni && $repo->findOneBy(['dni' => $dni])) {
            $_SESSION['error'] = 'El DNI ya está registrado';
            header('Location: usuarios.php'); exit;
        }
        if ($tel && $repo->findOneBy(['num_tel' => $tel])) {
            $_SESSION['error'] = 'El teléfono ya está registrado';
            header('Location: usuarios.php'); exit;
        }
    }

    // Editar
    if ($id) {
        $u = $entityManager->find(Personas::class, $id);

        // Solo si pertenece a la empresa (directo o intermedia)
        if ($u && personaPerteneceEmpresa($entityManager, $u, $idEmpresa)) {

            // Impide dejar sin admin (cuenta admins por la empresa directa como antes)
            // (si querés que también cuente admins por intermedia, lo hacemos después)
            if ($u->getRol() === 1 && $rol !== 1) {
                $admins = $repo->count(['empresa' => $idEmpresa, 'rol' => 1]);
                if ($admins < 2) {
                    $_SESSION['error'] = 'Debe quedar al menos un Administrador';
                    header('Location: usuarios.php'); exit;
                }
            }

            // Validar email duplicado
            $otroEmail = $repo->findOneBy(['email' => $email]);
            if ($otroEmail && $otroEmail->getId() !== $u->getId()) {
                $_SESSION['error'] = 'El email ya está registrado por otro usuario';
                header('Location: usuarios.php'); exit;
            }

            // Validar DNI duplicado
            if ($dni) {
                $otroDni = $repo->findOneBy(['dni' => $dni]);
                if ($otroDni && $otroDni->getId() !== $u->getId()) {
                    $_SESSION['error'] = 'El DNI ya está registrado por otro usuario';
                    header('Location: usuarios.php'); exit;
                }
            }

            // Actualizar datos
            $u->setNombre($nombre)
              ->setApellido($apellido)
              ->setDni($dni)
              ->setGenero($genero)
              ->setEmail($email)
              ->setRol($rol)
              ->setNumTel($tel);

            if ($pass) {
                $u->setPass(password_hash($pass, PASSWORD_DEFAULT));
            }

            // Asegura que exista el vínculo en la intermedia también (por si venía legacy)
            asegurarRelacionIntermedia($entityManager, $u, $empresaObj);
        }
    }
    // Crear nuevo
    else {
        $u = new Personas();
        $u->setNombre($nombre)
          ->setApellido($apellido)
          ->setDni($dni)
          ->setGenero($genero)
          ->setEmail($email)
          ->setPass(password_hash($pass, PASSWORD_DEFAULT))
          ->setRol($rol)
          ->setNumTel($tel)
          ->setEmpresa($empresaObj) // legacy
          ->setTokenOpt(generarTokenOPT())
          ->setEstadoPersona(1);

        $entityManager->persist($u);

        // también lo agregamos a la intermedia
        asegurarRelacionIntermedia($entityManager, $u, $empresaObj);
    }

    $entityManager->flush();
    $_SESSION['mensaje'] = $id
        ? 'Usuario actualizado correctamente'
        : 'Usuario creado correctamente';
    header('Location: usuarios.php');
    exit;
}

// =====================================================
// LISTAR usuarios (directos + intermedia)
// =====================================================
$qb = $entityManager->createQueryBuilder();
$qb->select('DISTINCT p')
   ->from(Personas::class, 'p')
   ->leftJoin(EmpresasPersonas::class, 'ep', 'WITH', 'ep.persona = p')
   ->where('p.empresa = :emp OR ep.empresa = :emp')
   ->setParameter('emp', $empresaObj)
   ->orderBy('p.apellido', 'ASC')
   ->addOrderBy('p.nombre', 'ASC');

$usuarios = $qb->getQuery()->getResult();

// Solicitudes (pendientes) idem
$qb2 = $entityManager->createQueryBuilder();
$qb2->select('DISTINCT p')
    ->from(Personas::class, 'p')
    ->leftJoin(EmpresasPersonas::class, 'ep', 'WITH', 'ep.persona = p')
    ->where('(p.empresa = :emp OR ep.empresa = :emp) AND p.estadoPersona = 3')
    ->setParameter('emp', $empresaObj)
    ->orderBy('p.apellido', 'ASC')
    ->addOrderBy('p.nombre', 'ASC');

$solicitudes = $qb2->getQuery()->getResult();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Malkoni Hnos - Servicios Online</title>

  <!-- Bootstrap & Icons & SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css"
        rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"
        rel="stylesheet">

  <!-- Tus estilos -->
  <link rel="stylesheet" href="styles/navbarStyles.css">
  <link rel="stylesheet" href="styles/usuariosStyles.css">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="main-container">
    <!-- CARD: Gestión de Usuarios -->
    <div class="card table-card mb-5">
      <div class="card-header">
        <i class="bi bi-people-fill me-2"></i>Gestión de Usuarios
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-dark">
              <tr>
                <th>Nombre</th><th>Apellido</th><th>DNI</th>
                <th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($usuarios as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u->getNombre()) ?></td>
                <td><?= htmlspecialchars($u->getApellido()) ?></td>
                <td><?= htmlspecialchars($u->getDni()) ?></td>
                <td><?= htmlspecialchars($u->getEmail()) ?></td>
                <td><?= $u->getRol() === 1 ? 'Administrador' : 'Operario' ?></td>
                <td>
                  <?php
                    switch ($u->getEstadoPersona()) {
                      case 1: echo 'Habilitado'; break;
                      case 2: echo 'Inhabilitado'; break;
                      case 3: echo 'Pendiente'; break;
                      default: echo 'Desconocido'; break;
                    }
                  ?>
                </td>
                <td>
                  <!-- Editar -->
                  <button class="btn btn-primary btn-sm btn-editar"
                          data-id="<?= $u->getId() ?>"
                          data-nombre="<?= htmlspecialchars($u->getNombre())?>"
                          data-apellido="<?= htmlspecialchars($u->getApellido())?>"
                          data-dni="<?= htmlspecialchars($u->getDni())?>"
                          data-genero="<?= htmlspecialchars($u->getGenero())?>"
                          data-email="<?= htmlspecialchars($u->getEmail())?>"
                          data-telefono="<?= htmlspecialchars($u->getNumTel())?>"
                          data-rol="<?= $u->getRol() ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#editarUsuarioModal">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <!-- Eliminar -->
                  <button class="btn btn-danger btn-sm btn-eliminar"
                          data-id="<?= $u->getId() ?>"
                          data-nombre="<?= htmlspecialchars($u->getNombre())?>"
                          data-apellido="<?= htmlspecialchars($u->getApellido())?>">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                  <!-- Cambiar estado -->
                  <?php
                    $esH = $u->getEstadoPersona() === 1;
                    $new = $esH ? 2 : 1;
                    $cls = $esH ? 'warning' : 'success';
                    $ico = $esH ? 'slash-circle' : 'check-circle';
                    $txt = $esH ? 'inhabilitar' : 'habilitar';
                    $col = $esH ? 'warning' : 'success';
                  ?>
                  <button class="btn btn-<?= $cls ?> btn-sm btn-cambiar-estado"
                          data-id="<?= $u->getId() ?>"
                          data-nombre="<?= htmlspecialchars($u->getNombre())?>"
                          data-apellido="<?= htmlspecialchars($u->getApellido())?>"
                          data-nuevo-estado="<?= $new ?>"
                          data-color="<?= $col ?>"
                          data-texto="<?= $txt ?>"
                          title="<?= ucfirst($txt) ?>">
                    <i class="bi bi-<?= $ico ?>-fill"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- CARD: Crear Usuario -->
    <div class="card form-card">
      <div class="card-header">
        <i class="bi bi-person-plus-fill me-2"></i>Crear Usuario
      </div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <div class="col-md-6">
            <input tabindex="1" type="text" name="nombre" class="form-control" placeholder="Nombre" required>
          </div>
          <div class="col-md-6">
            <select tabindex="5" name="genero" class="form-select" required>
              <option value="">Género</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
            </select>
          </div>

          <div class="col-md-6">
            <input tabindex="2" type="text" name="apellido" class="form-control" placeholder="Apellido" required>
          </div>
          <div class="col-md-6">
            <select tabindex="6" name="rol" class="form-select" required>
              <option value="">Rol</option>
              <option value="1">Administrador</option>
              <option value="2">Operario</option>
            </select>
          </div>

          <div class="col-md-6">
            <input tabindex="3" type="text" name="dni" class="form-control" placeholder="DNI" required>
          </div>
          <div class="col-md-6">
            <input tabindex="7" type="email" name="email" class="form-control" placeholder="Correo" required>
          </div>

          <div class="col-md-6">
            <input tabindex="4" type="text" name="telefono" class="form-control" placeholder="Teléfono" required>
          </div>
          <div class="col-md-6">
            <input tabindex="8" type="password" name="pass" class="form-control" placeholder="Contraseña" required>
          </div>

          <div class="col-12 text-end">
            <button tabindex="9" type="submit" class="btn btn-orange px-5">Crear</button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <!-- Modal Editar Usuario -->
  <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" id="form-editar">
          <input type="hidden" name="id" id="editar-id">
          <div class="modal-header">
            <h5 class="modal-title">Editar Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" id="editar-nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Apellido</label>
              <input type="text" name="apellido" id="editar-apellido" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">DNI</label>
              <input type="text" name="dni" id="editar-dni" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Género</label>
              <select name="genero" id="editar-genero" class="form-select" required>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Correo</label>
              <input type="email" name="email" id="editar-email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" id="editar-telefono" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Rol</label>
              <select name="rol" id="editar-rol" class="form-select" required>
                <option value="1">Administrador</option>
                <option value="2">Operario</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña (nuevo)</label>
              <input type="password" name="pass" class="form-control" placeholder="(Opcional)">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
              Cancelar
            </button>
            <button type="submit" class="btn btn-warning btn-sm">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Mensajes
    <?php if(isset($_SESSION['mensaje'])): ?>
    Swal.fire({
      icon: 'success',
      title: '¡Éxito!',
      text: '<?= $_SESSION['mensaje'] ?>',
      timer: 2500
    });
    <?php unset($_SESSION['mensaje']); endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: '<?= $_SESSION['error'] ?>'
    });
    <?php unset($_SESSION['error']); endif; ?>

    // Editar
    document.querySelectorAll('.btn-editar').forEach(btn => {
      btn.onclick = () => {
        ['id','nombre','apellido','dni','genero','email','telefono','rol']
          .forEach(f => {
            document.getElementById('editar-'+f).value = btn.dataset[f];
          });
      };
    });

    // Eliminar con SweetAlert2
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
      btn.onclick = e => {
        e.preventDefault();
        const id   = btn.dataset.id;
        const name = btn.dataset.nombre + ' ' + btn.dataset.apellido;

        Swal.fire({
          title: '¿Confirmas que deseas eliminar al usuario?',
          text: `Usuario: ${name}`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then(result => {
          if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'eliminar_id';
            input.value = id;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
          }
        });
      };
    });

    // Cambiar estado
    document.querySelectorAll('.btn-cambiar-estado').forEach(btn => {
      btn.onclick = e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const name = btn.dataset.nombre + ' ' + btn.dataset.apellido;
        const newState = btn.dataset.nuevoEstado;
        const color = btn.dataset.color;
        const text = btn.dataset.texto;

        Swal.fire({
          title: `¿Confirmas que deseas ${text} al usuario?`,
          text: `Usuario: ${name}`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: `#${color === 'success' ? '198754' : 'ffc107'}`,
          cancelButtonColor: '#6c757d',
          confirmButtonText: `Sí, ${text}`,
          cancelButtonText: 'Cancelar'
        }).then(res => {
          if (res.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST'; form.style.display = 'none';
            [['id_usuario', id], ['nuevo_estado', newState], ['cambiar_estado', '1']]
              .forEach(([name,val]) => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = name; inp.value = val;
                form.appendChild(inp);
              });
            document.body.appendChild(form);
            form.submit();
          }
        });
      };
    });
  </script>
</body>
</html>
