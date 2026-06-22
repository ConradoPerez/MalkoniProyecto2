<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Entities\Personas;

// Carga de Doctrine
$entityManager = require __DIR__ . '/../config/doctrine.php';

$token   = $_GET['token'] ?? '';
$error   = null;
$success = false;

// Si no hay token en URL
if (!$token) {
    $error = 'Enlace inválido o expirado.';
} else {
    // Buscar persona por resetToken
    /** @var Personas|null $persona */
    $persona = $entityManager
        ->getRepository(Personas::class)
        ->findOneBy(['resetToken' => $token]);

    if (!$persona) {
        $error = 'Enlace inválido o expirado.';
    }
}

// Procesar el formulario de nueva contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($persona)) {
    $pass1 = $_POST['pass1'] ?? '';
    $pass2 = $_POST['pass2'] ?? '';

    if (strlen($pass1) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres, una mayúscula y una minúscula.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Guardar nueva contraseña y limpiar el token
        $persona->setPass(password_hash($pass1, PASSWORD_DEFAULT));
        $persona->setResetToken(null);
        $entityManager->flush();
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
    <link rel="stylesheet" href="styles/recuperar_contraseñaStyles.css?v=<?= time() ?>">
    <!-- Font Awesome para los ojitos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if ($success): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: '¡Contraseña actualizada!',
    text: 'Ya puedes iniciar sesión con tu nueva contraseña.',
    confirmButtonColor: '#166379',
    confirmButtonText: 'Ingresar'
  }).then(() => {
    window.location.href = 'login.php';
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
      <h1>Restablecer contraseña</h1>

      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php elseif (!isset($persona)): ?>
        <div class="error-message">No es posible procesar este enlace.</div>
      <?php endif; ?>

      <?php if (isset($persona) && !$success): ?>
      <form method="post" action="restablecer_contraseña.php?token=<?= urlencode($token) ?>">
        <div class="form-group password-wrapper">
          <input
            id="pass1"
            type="password"
            name="pass1"
            placeholder="Nueva contraseña"
            required
            autofocus
          >
          <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        <div class="form-group password-wrapper">
          <input
            id="pass2"
            type="password"
            name="pass2"
            placeholder="Repetir contraseña"
            required
          >
          <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        <div class="form-submit">
          <button type="submit" class="btn btn-teal">Cambiar contraseña</button>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  // Funcionalidad de “ojitos” para mostrar/ocultar contraseña
  document.querySelectorAll('.toggle-password').forEach(btn => {
    const input = btn.parentElement.querySelector('input');
    const icon  = btn.querySelector('i');
    btn.addEventListener('click', () => {
      const mostrar = input.type === 'password';
      input.type = mostrar ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
      btn.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
  });
</script>

</body>
</html>
