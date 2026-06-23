<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../config/doctrine.php';

require_once __DIR__ . '/../../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Entities\Personas;
use Entities\Empresas;
use Entities\EmpresasPersonas;

$tokenAutologin = trim((string) ($_GET['token'] ?? ''));

if (!isset($_SESSION['usuario']) && $tokenAutologin !== '') {
  /** @var Personas|null $personaPorToken */
  $personaPorToken = $entityManager
    ->getRepository(Personas::class)
    ->findOneBy(['tokenOpt' => $tokenAutologin]);

  if ($personaPorToken && $personaPorToken->getEmpresa() && $personaPorToken->getEmpresa()->isValidado()) {
    $estado = (int) $personaPorToken->getEstadoPersona();

    if (!in_array($estado, [2, 3, 4], true)) {
      $_SESSION['usuario']  = $personaPorToken->getEmail();
      $_SESSION['id']       = $personaPorToken->getId();
      $_SESSION['nombre']   = $personaPorToken->getNombre();
      $_SESSION['apellido'] = $personaPorToken->getApellido();
      $_SESSION['rol']      = $personaPorToken->getRol();

      $empresaIdActiva = 0;

      if ((int) $personaPorToken->getRol() === 2 && method_exists($personaPorToken, 'getEmpresaActiva')) {
        $ea = $personaPorToken->getEmpresaActiva();
        if ($ea instanceof Empresas && $ea->isValidado()) {
          $principal = $personaPorToken->getEmpresa();
          $principalId = $principal ? (int) $principal->getId() : 0;
          $asociado = ($principalId === (int) $ea->getId());

          if (!$asociado) {
            $vinculo = $entityManager->getRepository(EmpresasPersonas::class)->findOneBy([
              'persona' => $personaPorToken,
              'empresa' => $ea,
              'estado' => 1,
            ]);
            $asociado = (bool) $vinculo;
          }

          if ($asociado) {
            $empresaIdActiva = (int) $ea->getId();
          }
        }
      }

      if ($empresaIdActiva <= 0) {
        $empresaIdActiva = (int) $personaPorToken->getEmpresa()->getId();
      }

      $_SESSION['empresa_id'] = $empresaIdActiva;
    }
  }
}

if (!isset($_SESSION['usuario'])) {
  header('Location: ../login.php');
  exit;
}

// --- Datos del usuario ---
$repo     = $entityManager->getRepository(Personas::class);
$persona  = $repo->findOneBy(['email' => $_SESSION['usuario']]);
$tokenOpt = $persona ? ($persona->getTokenOpt() ?? '') : '';

// URL dinámica del iframe
$iframeUrl = 'https://www.optimizadoronline.com/empresa/malkoni/opti?access_token=' . urlencode($tokenOpt);

// --- ENVÍO DE SOPORTE ---
$mailSent  = false;
$mailError = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['name'], $_POST['email'], $_POST['issue'], $_POST['message'])
) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.malkoni.com.ar';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'soporte@online.malkoni.com.ar';
        $mail->Password   = 'SoporteMalko25';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        $mail->setFrom('soporte@online.malkoni.com.ar', 'Malkoni Hnos');
        $mail->addAddress('soporte@online.malkoni.com.ar');
        $mail->addReplyTo($_POST['email'], $_POST['name']);

        $mail->isHTML(true);
        $mail->Subject = htmlspecialchars($_POST['issue'], ENT_QUOTES, 'UTF-8');

        $body  = '<p><strong>Nombre:</strong> ' . htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') . '</p>';
        $body .= '<p><strong>Email:</strong> ' . htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') . '</p>';
        $body .= '<p><strong>Asunto:</strong> ' . htmlspecialchars($_POST['issue'], ENT_QUOTES, 'UTF-8') . '</p>';
        $body .= '<p><strong>Mensaje:</strong><br>' . nl2br(htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8')) . '</p>';
        $mail->Body = $body;

        $mail->send();
        $mailSent = true;
    } catch (Exception $e) {
        $mailError = $mail->ErrorInfo;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Malkoni Hnos - OPT</title>

  <link rel="stylesheet" href="styles/navbarStyles.css">
  <link rel="stylesheet" href="styles/optStyles.css?v=<?= time() ?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    /* Protocolo Lepton: navbar fijo 10vh y body sin scroll */
    body { overflow: hidden; }

    /* Iframe justo debajo del navbar */
    .iframe-container{
      position: absolute;
      top: 10vh;
      left: 0;
      width: 100%;
      height: 90vh;
      overflow: hidden;
    }

    .iframe-container iframe{
      width: 100%;
      height: 100%;
      min-height: 700px;
      border: none;
    }
  </style>
</head>

<body>

<?php if ($mailSent): ?>
  <script>
    Swal.fire({ icon:'success', title:'¡Enviado!', text:'Tu mensaje de soporte se ha enviado correctamente.' });
  </script>
<?php elseif ($mailError): ?>
  <script>
    Swal.fire({ icon:'error', title:'Error', text:'No se pudo enviar el mensaje: <?= addslashes($mailError) ?>' });
  </script>
<?php endif; ?>

<?php
  // ✅ Config del navbar SOLO para OPT (antes del require)
  $navbarContext = 'opt';
  $navbarTitle = 'Optimizador de Cortes';
  $navbarLogoHref = 'opt.php';
  $navbarShowOptButtons = true;
  
  $navbarShowCotizarBtn = true;
  $navbarCotizarHref = 'cotizar_mis_pedidos.php';

  require __DIR__ . '/navbar.php';
?>

<!-- IFRAME -->
<div class="iframe-container">
  <iframe
    src="<?= htmlspecialchars($iframeUrl, ENT_QUOTES, 'UTF-8') ?>#/"
    scrolling="yes"
  ></iframe>
</div>

<!-- OVERLAY DE SOPORTE -->
<div id="supportOverlay" class="overlay" style="display:none;">
  <div class="overlay-content">
    <h2>Soporte</h2>
    <form method="post" id="supportForm" class="support-form">
      <div class="form-group">
        <label for="supportName">Nombre</label>
        <input type="text" id="supportName" name="name" required>
      </div>
      <div class="form-group">
        <label for="supportEmail">Email</label>
        <input type="email" id="supportEmail" name="email" required>
      </div>
      <div class="form-group">
        <label for="supportIssue">Asunto</label>
        <input type="text" id="supportIssue" name="issue" required>
      </div>
      <div class="form-group">
        <label for="supportMessage">Mensaje</label>
        <textarea id="supportMessage" name="message" rows="4" required></textarea>
      </div>
      <div class="form-actions">
        <button type="button" id="closeSupport" class="btn-secondary">Cancelar</button>
        <button type="submit" class="btn-primary">Enviar</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  // Botón Sitio Web
  const siteBtn = document.getElementById('siteBtn');
  if (siteBtn) {
    siteBtn.addEventListener('click', () => {
      Swal.fire({
        title: '¿Desea salir del optimizador?',
        text: 'Si continúa perderá la información y el pedido que está realizando.',
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No, quedarme',
        confirmButtonText: 'Sí, continuar'
      }).then(result => {
        if (result.isConfirmed) window.location.href = 'https://www.malkoni.com.ar';
      });
    });
  }

  // Soporte
  const supportBtn = document.getElementById('supportBtn');
  const overlay = document.getElementById('supportOverlay');
  const closeSupport = document.getElementById('closeSupport');

  if (supportBtn && overlay) supportBtn.addEventListener('click', () => overlay.style.display = 'flex');
  if (closeSupport && overlay) closeSupport.addEventListener('click', () => overlay.style.display = 'none');

})();
</script>

</body>
</html>
