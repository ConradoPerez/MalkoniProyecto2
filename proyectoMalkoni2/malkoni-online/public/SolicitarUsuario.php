<?php
// Activar display de errores para debugging (quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Entities\Personas;
use Entities\Empresas;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

session_start();

/**
 * Genera un token alfanumérico de longitud $length.
 */
function generarTokenOPT(int $length = 20): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $token;
}

$error = '';

// Obtener ID de empresa desde GET o sesión
$empresaId = $_GET['empresa_id']
           ?? $_SESSION['registro']['empresa']['empresa_id']
           ?? null;
if (!$empresaId) {
    die("No se encontraron datos de empresa para asociar el usuario.");
}

// Cargar datos de la empresa
/** @var Empresas|null $empresa */
$empresa = $entityManager->getRepository(Empresas::class)->find($empresaId);
if (!$empresa) {
    die("Empresa no encontrada");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Aprobación/Rechazo de solicitudes (administradores) ---
    if (isset($_POST['aprobar_id']) || isset($_POST['rechazar_id'])) {
        $usuarioActual = isset($_SESSION['id'])
            ? $entityManager->find(Personas::class, $_SESSION['id'])
            : null;
        if (!$usuarioActual || $usuarioActual->getRol() !== 1) {
            $error = "No tienes permisos para realizar esta acción";
        } else {
            $id      = $_POST['aprobar_id'] ?? $_POST['rechazar_id'];
            $usuario = $entityManager->find(Personas::class, $id);
            if ($usuario && $usuario->getEmpresa()->getId() === $empresa->getId()) {
                if (isset($_POST['aprobar_id'])) {
                    $usuario->setEstadoPersona(1); // activar usuario
                    $_SESSION['mensaje'] = 'Usuario aprobado correctamente';
                } else {
                    $entityManager->remove($usuario);
                    $_SESSION['mensaje'] = 'Solicitud rechazada';
                }
                $entityManager->flush();
                header("Location: SolicitarUsuario.php?empresa_id={$empresaId}");
                exit;
            } else {
                $error = "Usuario no encontrado o no pertenece a esta empresa";
            }
        }

    // --- Nueva solicitud de usuario ---
    } else {
        // Validar contraseñas
        if (($_POST['password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
            $error = "Las contraseñas no coinciden.";
        } else {
            try {
                $datos    = $_POST;
                $repoPers = $entityManager->getRepository(Personas::class);

                // Validar que el email de usuario sea distinto al email de la empresa
                $emailEmpresa = trim((string)$empresa->getEmail());
                $emailUsuario = trim((string)($datos['email'] ?? ''));
                if ($emailUsuario !== '' && $emailEmpresa !== '' && strcasecmp($emailUsuario, $emailEmpresa) === 0) {
                    throw new \Exception("El email del usuario no puede ser el mismo que el email de la empresa.");
                }

                // Verificar duplicados
                if ($repoPers->findOneBy(['email' => $datos['email'] ?? ''])) {
                    throw new \Exception("El email ya está registrado.");
                }
                if ($repoPers->findOneBy(['dni' => (int)($datos['dni'] ?? 0)])) {
                    throw new \Exception("El DNI ya está registrado en el sistema.");
                }
                if ($repoPers->findOneBy(['num_tel' => $datos['telefono'] ?? ''])) {
                    throw new \Exception("El número de teléfono ya está registrado en el sistema.");
                }

                // Generar token de validación de 64 caracteres
                $validToken = bin2hex(random_bytes(32));

                // Crear nueva persona con estado_persona = 4 (no validado)
                $persona = new Personas();
                $persona
                    ->setNombre($datos['nombre'] ?? '')
                    ->setApellido($datos['apellido'] ?? '')
                    ->setGenero($datos['genero'] ?? null)
                    ->setDni((int)($datos['dni'] ?? 0))
                    ->setEmail($datos['email'] ?? '')
                    ->setNumTel($datos['telefono'] ?? '')
                    ->setPass(password_hash($datos['password'], PASSWORD_DEFAULT))
                    ->setEmpresa($empresa)
                    ->setTokenOpt(generarTokenOPT())
                    ->setValidacionToken($validToken)
                    ->setEstadoPersona(4)  // pendiente validación
                    ->setRol(2);           // operario

                $entityManager->persist($persona);
                $entityManager->flush();

                // Enviar mail de validación al usuario nuevo
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
                    $mail->setFrom('no-reply@online.malkoni.com.ar','Malkoni Hnos');
                    $mail->addAddress($persona->getEmail());
                    $mail->isHTML(true);
                    $mail->Subject = 'Validación de cuenta – Malkoni Hnos';

                    $link = "https://online.malkoni.com.ar/validar_usuario_mail.php?token={$validToken}";
                    $mail->Body = "
                      <div style='background:#f4f4f4;padding:20px;font-family:sans-serif;'>
                        <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;'>
                          <div style='background:#166379;color:#fff;padding:20px;text-align:center;'>
                            <h2>Bienvenido a Malkoni Hnos</h2>
                          </div>
                          <div style='padding:30px;text-align:center;color:#333;'>
                            <p style='font-size:1rem;'>¡Gracias por registrarte! Para poder ingresar, debes validar tu cuenta:</p>
                            <a href='{$link}' style='display:inline-block;padding:12px 24px;
                               background:#D88429;color:#fff;border-radius:30px;text-decoration:none;font-weight:bold;'>
                              Validar cuenta
                            </a>
                            <p style='margin-top:20px;color:#333;'><strong>Gracias por usar los servicios online de Malkoni Hnos.</strong></p>
                          </div>
                          <div style='background:#f1f1f1;color:#888;padding:10px;text-align:center;font-size:0.8em;'>
                            © Malkoni Hnos
                          </div>
                        </div>
                      </div>";
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mail error: " . $mail->ErrorInfo);
                }

                // Redirigir al login mostrando alerta de registro exitoso
                header("Location: login.php?registro=ok&email=" . urlencode($datos['email'] ?? ''));
                exit;

            } catch (\Exception $e) {
                $error = "¡Error inesperado! " . $e->getMessage();
            }
        }
    }
}

// Guardar en sesión datos de la empresa para el formulario (sin web/ig)
$_SESSION['registro']['empresa'] = [
    'empresa_id'       => $empresa->getId(),
    'razon_social'     => $empresa->getRazonSocial(),
    'cuit'             => $empresa->getCuit(),
    'cod_cond_iva'     => $empresa->getCodCondIVA(),
    'email_empresa'    => $empresa->getEmail(),
    'telefono_empresa' => $empresa->getNumTel(),
    // 'web' y 'ig' eliminados porque ya no existen en la entidad
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Solicitar Usuario</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="/public/styles/solicitar_usuario.css?v=<?= time() ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container">
  <div class="left-panel">
    <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
    <h1 style="font-family: 'Syncopate', sans-serif; font-weight:700; color:#E1DFD9; font-size:2.7rem;">
      Usuario
    </h1>
    <p style="color:#E1DFD9; font-size:1rem;">Complete sus datos para registrarse</p>
    <p class="company-info">
      <strong>Empresa:</strong> <?= htmlspecialchars($empresa->getRazonSocial(), ENT_QUOTES) ?>
    </p>
  </div>

  <div class="right-panel">
    <div class="form-header">
      <h1 class="form-title">Datos Personales</h1>
      <p class="form-subtitle">Completa los datos del usuario.</p>
    </div>

    <form method="post" action="SolicitarUsuario.php?empresa_id=<?= htmlspecialchars($empresaId) ?>" id="form-paso3" autocomplete="off">
      <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($empresaId) ?>">
      <div class="two-columns-form">
        <div class="column">
          <div class="form-group">
            <label class="form-label">Apellido <span style="color:red">*</span></label>
            <input class="form-input" name="apellido" type="text" required
                   value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Nombre <span style="color:red">*</span></label>
            <input class="form-input" name="nombre" type="text" required
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Género de nacimiento <span style="color:red">*</span></label>
            <select class="form-input" name="genero" required>
              <option value="">--Seleccione--</option>
              <option value="M" <?= ($_POST['genero'] ?? '')==='M'?'selected':'' ?>>Masculino</option>
              <option value="F" <?= ($_POST['genero'] ?? '')==='F'?'selected':'' ?>>Femenino</option>
            </select>
          </div>
        </div>
        <div class="column">
          <div class="form-group">
            <label class="form-label">DNI <span style="color:red">*</span></label>
            <input class="form-input" name="dni" type="text" required
                   value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Teléfono <span style="color:red">*</span></label>
            <input class="form-input" name="telefono" type="tel" required
                   value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
          </div>
        </div>
      </div>

      <h2 class="section-title" style="font-family:'Syncopate',sans-serif;font-weight:700;color:#166379;font-size:1.7rem;margin-bottom:6px;">
        Datos de acceso al sistema
      </h2>

      <div class="form-container">
        <div class="form-group">
          <label class="form-label">Email Personal <span style="color:red">*</span></label>
          <input class="form-input" name="email" type="email" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          <small style="display:block;margin-top:4px;color:#6c757d;">
            Debe ser distinto del email de la empresa: <strong><?= htmlspecialchars($empresa->getEmail() ?? '') ?></strong>
          </small>
        </div>
        <div class="form-group password-wrapper">
          <label class="form-label">Contraseña <span style="color:red">*</span></label>
          <input id="password" class="form-input" name="password" type="password" required
                 pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}"
                 title="8+ caracteres, con mayúscula, minúscula y número">
          <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        <div class="form-group password-wrapper">
          <label class="form-label">Confirmar Contraseña <span style="color:red">*</span></label>
          <input id="confirm_password" class="form-input" name="confirm_password" type="password" required>
          <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>

        <button type="button" class="submit-btn back" onclick="window.location.href='validar_usuario.php'">Volver</button>
        <button type="submit" class="submit-btn">Agregar Usuario</button>
      </div>
    </form>

    <script>
      // Validación cliente de contraseña
      document.getElementById('form-paso3').addEventListener('submit', function(e) {
        const pwd = this.password.value, cpw = this.confirm_password.value;
        const re  = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
        if (!re.test(pwd)) {
          e.preventDefault();
          Swal.fire('Error','La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número','error');
        } else if (pwd !== cpw) {
          e.preventDefault();
          Swal.fire('Error','Las contraseñas no coinciden','error');
        }
        // Bloquear email igual al de empresa en cliente también
        const emailEmpresa = <?= json_encode($empresa->getEmail() ?? '') ?>.toLowerCase();
        const emailUsuario = (this.email.value || '').toLowerCase();
        if (emailEmpresa && emailUsuario && emailEmpresa === emailUsuario) {
          e.preventDefault();
          Swal.fire('Error','El email del usuario no puede ser el mismo que el email de la empresa','error');
        }
      });

      // Toggle mostrar/ocultar contraseña
      document.querySelectorAll('.toggle-password').forEach(btn => {
        const input = btn.parentElement.querySelector('input');
        const icon  = btn.querySelector('i');
        btn.addEventListener('click', () => {
          const isPwd = input.type === 'password';
          input.type = isPwd ? 'text' : 'password';
          icon.classList.toggle('fa-eye-slash');
        });
      });

      // Flash messages
      <?php if (isset($_SESSION['mensaje'])): ?>
      Swal.fire('¡Listo!','<?= addslashes($_SESSION['mensaje']) ?>','success');
      <?php unset($_SESSION['mensaje']); endif; ?>

      <?php if ($error): ?>
      Swal.fire('¡Atención!','<?= addslashes($error) ?>','error');
      <?php endif; ?>
    </script>

  </div>
</div>
</body>
</html>
