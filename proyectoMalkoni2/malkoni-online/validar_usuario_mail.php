<?php
require_once __DIR__ . '/vendor/autoload.php';
$entityManager = require __DIR__ . '/config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;

$token   = $_GET['token'] ?? '';
$estado  = '';
$mensaje = '';

if (!$token) {
    $estado  = 'error';
    $mensaje = 'Falta el token para validar.';
} else {
    // 1) Intento validar usuario (Personas)
    $repoPer = $entityManager->getRepository(Personas::class);
    $user = $repoPer->findOneBy(['validacion_token' => $token]);

    if ($user) {
        // Ya validado?
        if ($user->getEstadoPersona() === 1) {
            $estado  = 'info';
            $mensaje = 'Este usuario ya fue validado previamente.';
        } else {
            $user->setEstadoPersona(1)
                 ->setValidacionToken(null);
            $entityManager->flush();
            $estado  = 'success';
            $mensaje = '✔ Tu cuenta ha sido activada correctamente.';
        }
    } else {
        // 2) Si no es persona, pruebo empresa
        $repoEmp = $entityManager->getRepository(Empresas::class);
        $empresa = $repoEmp->findOneBy(['validacion_token' => $token]);

        if (!$empresa) {
            $estado  = 'error';
            $mensaje = 'Token inválido o cuenta no encontrada.';
        } elseif ($empresa->isValidado()) {
            $estado  = 'info';
            $mensaje = 'Esta cuenta ya fue validada previamente.';
        } else {
            $empresa->setValidado(true)
                     ->setValidacionToken(null);
            $entityManager->flush();
            $estado  = 'success';
            $mensaje = '✔ La empresa ha sido validada exitosamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Validación de Cuenta – Malkoni Hnos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
  <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/styles/validar_mailStyles.css?v=<?= time() ?>">
</head>
<body>

<div class="login-container">
  <div class="left-panel">
    <img src="/public/logo.png" alt="Malkoni Hnos" class="logo-img">
  </div>

  <div class="right-panel">
    <div class="message-container">
      <h2>Validación de Cuenta</h2>
      
      <?php if ($estado === 'success'): ?>
        <div class="success-message"><?= htmlspecialchars($mensaje) ?></div>
      <?php elseif ($estado === 'error'): ?>
        <div class="error-message"><?= htmlspecialchars($mensaje) ?></div>
      <?php else: ?>
        <div class="info-message"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>
    </div>

    <div class="action-container">
      <a href="https://deherrajes.com.ar/auth/login" class="btn-validate">Ir al inicio de sesión</a>
      <p class="instruction">Serás redirigido al inicio de sesión al presionar el botón.</p>
    </div>

    <div class="footer">© Malkoni Hnos</div>
  </div>
</div>

</body>
</html>
