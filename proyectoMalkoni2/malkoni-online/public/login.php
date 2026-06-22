<?php
// DEBUG: mostrar errores en pantalla (quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

$entityManager = require __DIR__ . '/../config/doctrine.php';

$bloqueoEmpresa        = false;
$bloqueoEstado         = false;
$pendienteValidacion   = false;
$error                 = null;
$mensajeAdmin          = '';
$registroExitoso       = false;

// =========================================================
// ✅ DESTINO POR DEFECTO (ya NO existe dashboard.php)
// =========================================================
$defaultRedirect = '/public/Dashboard/opt.php';

/**
 * En el futuro podés habilitar más destinos acá.
 * Por ahora, todo cae en OPT.
 */
$redirectMap = [
    'opt'     => '/public/Dashboard/opt.php',
    'pedidos' => '/public/Dashboard/opt.php', // por ahora también va a OPT
    // 'cot'  => '/public/Dashboard/cotizaciones.php', // cuando exista
];

// Capturar origen por GET (entrada desde botones)
if (isset($_GET['from'])) {
    $from = (string)$_GET['from'];
    if (isset($redirectMap[$from])) {
        $_SESSION['post_login_redirect'] = $redirectMap[$from];
        $_SESSION['post_login_from']     = $from;
    }
}

// Capturar origen por POST (por si se perdió el GET al enviar el form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['from'])) {
    $fromPost = (string)$_POST['from'];
    if (isset($redirectMap[$fromPost])) {
        $_SESSION['post_login_redirect'] = $redirectMap[$fromPost];
        $_SESSION['post_login_from']     = $fromPost;
    }
}

// Si viene del registro exitoso
if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $registroExitoso = true;
}

// Si ya está logueado, redirigir automáticamente al destino guardado o default (OPT)
if (isset($_SESSION['usuario'])) {
    $dest = $_SESSION['post_login_redirect'] ?? $defaultRedirect;
    header('Location: ' . $dest);
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $clave = $_POST['pass'] ?? '';

    // --- LOGIN SOPORTE HARDCODEADO ---
    if ($email === 'soporte@online.malkoni.com.ar' && $clave === 'SoporteMalko25') {
        $_SESSION['usuario']  = $email;
        $_SESSION['rol']      = 3;
        $_SESSION['nombre']   = 'Soporte';
        $_SESSION['apellido'] = '';
        header('Location: /public/Dashboard/Soporte/dashboard_soporte.php');
        exit;
    }
    // --- FIN SOPORTE ---

    /** @var Personas|null $persona */
    $persona = $entityManager
        ->getRepository(Personas::class)
        ->findOneBy(['email' => $email]);

    if ($persona) {
        $hashGuardado = $persona->getPass();

        if (password_verify($clave, $hashGuardado)) {

            $estado = $persona->getEstadoPersona();

            // 1) Pendiente de validación (estado = 4)
            if ($estado === 4) {
                $pendienteValidacion = true;

            // 2) Bloqueo por estado de usuario (2 o 3)
            } elseif ($estado === 2 || $estado === 3) {

                $admin = $entityManager
                    ->getRepository(Personas::class)
                    ->findOneBy([
                        'empresa' => $persona->getEmpresa(),
                        'rol'     => 1
                    ]);

                if ($admin) {
                    $adminName  = trim($admin->getNombre() . ' ' . $admin->getApellido());
                    $adminEmail = $admin->getEmail();
                    $mensajeAdmin = "Tu cuenta está inactiva. Para activarla, contacta al administrador <strong>({$adminName} – {$adminEmail})</strong>.";
                } else {
                    $mensajeAdmin = "Tu cuenta está inactiva. Contacta al administrador de tu empresa para activarla.";
                }
                $bloqueoEstado = true;

            // 3) Bloqueo por empresa principal no validada
            } elseif (!$persona->getEmpresa() || !$persona->getEmpresa()->isValidado()) {
                $bloqueoEmpresa = true;

            // 4) Login exitoso
            } else {
                $_SESSION['usuario']  = $persona->getEmail();
                $_SESSION['id']       = $persona->getId();
                $_SESSION['nombre']   = $persona->getNombre();
                $_SESSION['apellido'] = $persona->getApellido();
                $_SESSION['rol']      = $persona->getRol();

                // =========================================================
                // ✅ Setear empresa activa en sesión (persistida si existe)
                // =========================================================
                $empresaIdActiva = 0;

                // Solo aplica a usuario común (rol=2)
                if ((int)$persona->getRol() === 2 && method_exists($persona, 'getEmpresaActiva')) {
                    $ea = $persona->getEmpresaActiva(); // ManyToOne a Empresas (empresa_activa_id)
                    if ($ea instanceof Empresas) {
                        if ($ea->isValidado()) {
                            $principal = $persona->getEmpresa();
                            $principalId = $principal ? (int)$principal->getId() : 0;

                            $asociado = ($principalId === (int)$ea->getId());

                            if (!$asociado) {
                                $v = $entityManager->getRepository(EmpresasPersonas::class)->findOneBy([
                                    'persona' => $persona,
                                    'empresa' => $ea,
                                    'estado'  => 1
                                ]);
                                $asociado = (bool)$v;
                            }

                            if ($asociado) {
                                $empresaIdActiva = (int)$ea->getId();
                            }
                        }
                    }
                }

                // Fallback: empresa principal
                if ($empresaIdActiva <= 0) {
                    $empresaIdActiva = (int)$persona->getEmpresa()->getId();
                }

                $_SESSION['empresa_id'] = $empresaIdActiva;

                // =========================================================
                // ✅ Redirección final (SIEMPRE OPT por defecto)
                // =========================================================
                $dest = $_SESSION['post_login_redirect'] ?? $defaultRedirect;
                header('Location: ' . $dest);
                exit;
            }

        } else {
            $error = 'La contraseña ingresada es incorrecta.';
        }
    } else {
        $error = 'No encontramos una cuenta asociada a ese correo electrónico.';
    }
}

// Para mantener el "from" en el form (y no perderlo al hacer POST)
$currentFrom = $_SESSION['post_login_from'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Malkoni Hnos - Servicios Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/LogInStyles.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if ($registroExitoso): ?>
<script>
Swal.fire({
    icon: 'success',
    title: '¡Registro exitoso!',
    html: '<p>¡Gracias por registrarte en <strong>Malkoni Hnos</strong>!<br>Se ha enviado un email de validación a <em><?= htmlspecialchars($_GET['email'] ?? '') ?></em>.<br>Debes validar tu cuenta antes de iniciar sesión.</p>',
    confirmButtonColor: '#166379',
    confirmButtonText: 'Entendido'
});
</script>
<?php endif; ?>

<div class="login-container">
    <div class="left-panel">
        <div class="logo">
            <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
        </div>
    </div>

    <div class="right-panel">
        <div class="form-container">
            <h1>BIENVENIDO</h1>
            <p>Por favor, ingresa tu correo y contraseña para continuar.</p>

            <?php if ($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error, ENT_QUOTES) ?>
            </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <!-- Mantener origen (opt/pedidos), hoy ambos van a OPT -->
                <input type="hidden" name="from" value="<?= htmlspecialchars($currentFrom) ?>">

                <div class="form-group">
                    <input type="email" name="email" placeholder="Correo electrónico" required autofocus>
                </div>

                <div class="form-group password-wrapper">
                    <input type="password" name="pass" id="pass" placeholder="Contraseña" required>
                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>

                <div class="form-submit">
                    <button type="submit" class="btn btn-teal">Iniciar Sesión</button>
                </div>

                <div class="help-links">
                    <a href="../recuperar_clave.php"><strong>¿Olvidaste tu contraseña?</strong></a>
                </div>

                <div class="separator">
                    <hr><span>o</span><hr>
                </div>

                <p class="bottom-text">¿No tienes cuenta?</p>
                <div class="form-create">
                    <a href="tipo_identidad.php" class="btn btn-orange">Crear Usuario</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($pendienteValidacion): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Cuenta pendiente de validación',
    html: '<p>Debes validar tu cuenta antes de iniciar sesión.<br>Revisa tu correo electrónico para activar tu cuenta.</p>',
    confirmButtonColor: '#D88429',
    confirmButtonText: 'Entendido'
});
</script>
<?php endif; ?>

<?php if ($bloqueoEstado): ?>
<script>
Swal.fire({
    icon: 'info',
    title: 'Acceso denegado',
    html: '<p><?= $mensajeAdmin ?></p>',
    confirmButtonColor: '#166379',
    confirmButtonText: 'Entendido'
});
</script>
<?php endif; ?>

<?php if ($bloqueoEmpresa): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Empresa no validada',
    html: '<p>Tu empresa aún no ha sido validada.<br>Revisa tu correo electrónico y valídala para iniciar sesión.</p>',
    confirmButtonColor: '#D88429',
    confirmButtonText: 'Entendido'
});
</script>
<?php endif; ?>

<script>
const toggleBtn = document.querySelector('.toggle-password');
const passInput = document.getElementById('pass');
const icon      = toggleBtn.querySelector('i');

toggleBtn.addEventListener('click', () => {
    const isPwd = passInput.type === 'password';
    passInput.type = isPwd ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
    toggleBtn.setAttribute('aria-label', isPwd ? 'Ocultar contraseña' : 'Mostrar contraseña');
});
</script>

</body>
</html>
