<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use Entities\Personas;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = require __DIR__ . '/config/doctrine.php';

$sent       = false;
$error      = null;
$noValidada = false;

// Determinar página de origen
$origen = $_GET['origen'] ?? $_POST['origen'] ?? 'login'; // Valor por defecto si no se pasa

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Por favor ingresa un correo electrónico.';
    } else {
        /** @var Personas|null $persona */
        $persona = $entityManager
            ->getRepository(Personas::class)
            ->findOneBy(['email' => $email]);

        if ($persona) {
            $empresa = $persona->getEmpresa();
            if (!$empresa->isValidado()) {
                $noValidada = true;
            } else {
                $resetToken = bin2hex(random_bytes(16));
                $persona->setResetToken($resetToken);
                $entityManager->flush();

                $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
                $host     = $_SERVER['HTTP_HOST'];
                $urlToken = urlencode($resetToken);
                $resetUrl = "{$scheme}://{$host}/public/restablecer_contraseña.php?token={$urlToken}";

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'mail.malkoni.com.ar';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'no-reply@online.malkoni.com.ar';
                    $mail->Password   = '#$Mcp4n3lI$#';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    $mail->Encoding   = 'base64';

                    $mail->setFrom('no-reply@online.malkoni.com.ar', 'Malkoni Hnos');
                    $mail->addAddress($email, $persona->getNombre() . ' ' . $persona->getApellido());
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperar contraseña - Malkoni Hnos';

                    $mail->Body = "
                      <div style='background:#f4f4f4;padding:20px;font-family:sans-serif;'>
                        <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;'>
                          <div style='background:#166379;color:#fff;padding:20px;text-align:center;'>
                            <h2>Restablecer contraseña</h2>
                          </div>
                          <div style='padding:30px;text-align:center;color:#333;'>
                            <p>Hola <strong>{$persona->getNombre()}</strong>,</p>
                            <p>Has solicitado restablecer tu contraseña.</p>
                            <p>Haz clic en el botón para elegir una nueva contraseña:</p>
                            <a href='{$resetUrl}'
                               style='display:inline-block;padding:12px 24px;
                                      background:#D88429;color:#fff;border-radius:30px;
                                      text-decoration:none;font-weight:bold;'>
                              Restablecer mi contraseña
                            </a>
                          </div>
                          <div style='background:#f1f1f1;color:#888;padding:10px;text-align:center;
                                      font-size:0.8em;'>
                            © Malkoni Hnos
                          </div>
                        </div>
                      </div>";

                    $mail->send();
                    $sent = true;
                } catch (Exception $e) {
                    error_log("Mail error: " . $mail->ErrorInfo);
                    $error = 'No se pudo enviar el correo. Intenta nuevamente más tarde.';
                }
            }
        } else {
            $error = 'No existe una cuenta asociada a ese correo electrónico.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="/public/styles/recuperar_contraseñaStyles.css?v=<?= time() ?>">
  <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
  <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if ($noValidada): ?>
<script>
  Swal.fire({
    icon: 'warning',
    title: 'Cuenta no validada',
    text: 'Debes activar tu cuenta antes de recuperar la contraseña. Revisa tu correo de validación.',
    confirmButtonColor: '#d33',
    confirmButtonText: 'Entendido'
  });
</script>
<?php endif; ?>

<?php if ($sent): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Correo enviado',
    html: 'Hemos enviado un enlace de recuperación a <strong><?= htmlspecialchars($email) ?></strong>.',
    confirmButtonColor: '#166379',
    confirmButtonText: 'Entendido'
  }).then(() => {
    window.location.href = '/public/login.php';
  });
</script>
<?php endif; ?>

<div class="login-container">
  <div class="left-panel">
    <div class="logo">
      <img src="/public/logo.png" alt="Malkoni Hnos" class="logo-img">
    </div>
  </div>
  <div class="right-panel">
    <div class="form-container">
      <h1>Recuperar contraseña</h1>
      <p>Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
      <?php endif; ?>

      <form method="post" action="recuperar_clave.php">
         <input type="hidden" name="origen" value="<?= htmlspecialchars($origen) ?>">
          <div class="form-group">
            <input
              type="email"
              name="email"
              placeholder="Correo electrónico"
              required
              autofocus
            >
          </div>
          <div class="form-submit" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
            <a href="<?= $origen === 'dashboard' ? '/public/dashboard.php' : '/public/login.php' ?>" 
               class="btn-volver">
              Volver
            </a>
            <button type="submit" class="btn-enviar">Enviar</button>
          </div>
        </form>
        
        </div>
  </div>
</div>

</body>
</html>
