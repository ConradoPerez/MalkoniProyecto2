<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';

use Entities\Empresas;

/* ===== Validar CUIT ===== */
function validarCuit(string $cuit): bool {
    $cuit = preg_replace('/\D/', '', $cuit);
    if (strlen($cuit) !== 11) return false;

    $digits = str_split($cuit);
    $mult   = [5,4,3,2,7,6,5,4,3,2];

    $sum = 0;
    for ($i = 0; $i < 10; $i++) $sum += ((int)$digits[$i]) * $mult[$i];

    $mod = 11 - ($sum % 11);
    if ($mod === 11) $mod = 0;
    if ($mod === 10) $mod = 9;

    return (int)$digits[10] === $mod;
}

$error = '';
$showExistsAlert = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawCuit   = trim($_POST['cuit'] ?? '');
    $cleanCuit = preg_replace('/\D/', '', $rawCuit);

    if (!validarCuit($cleanCuit)) {
        $error = 'CUIT inválido. Verificá el formato.';
    } else {
        /** @var Empresas|null $empresa */
        $empresa = $entityManager
            ->getRepository(Empresas::class)
            ->findOneBy(['cuit' => $cleanCuit]);

        // Limpiar registro previo
        $_SESSION['registro'] = [];

        if ($empresa) {
            // Empresa existente → precarga completa
            $_SESSION['registro']['empresa'] = [
                'empresa_id'       => $empresa->getId(),
                'razon_social'     => $empresa->getRazonSocial(),
                'cuit'             => $empresa->getCuit(),
                'cod_cond_iva'     => $empresa->getCodCondIVA(),
                'email_empresa'    => $empresa->getEmail(),
                'telefono_empresa' => $empresa->getNumTel(),
            ];

            // Mostrar cartelito y luego seguir el rumbo
            $showExistsAlert = true;
        } else {
            // Empresa nueva → solo CUIT
            $_SESSION['registro']['empresa'] = [
                'cuit' => $cleanCuit
            ];

            header('Location: registro.php?paso=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar CUIT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- Usamos el mismo CSS del registro para que los botones queden iguales -->
    <link rel="stylesheet" href="styles/registroStyles.css?v=<?= time() ?>">

    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
      /* Solo para ajustar este paso al layout del registro (sin tocar tu css global) */
      .form-container { width: 100%; max-width: 520px; }
      .form-title { margin-top: 6px; }
    </style>
</head>
<body>

<div class="container">
  <div class="left-panel">
    <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
    <h1 style="font-family:'Syncopate',sans-serif;font-weight:700;color:#E1DFD9;font-size:2.7rem;">Empresa</h1>
    <p style="font-family:'Syncopate',sans-serif;font-weight:300;color:#E1DFD9;font-size:1rem;">Ingresá el CUIT</p>
  </div>

  <div class="right-panel">
    <div class="form-header">
      <h1 class="form-title">CUIT</h1>
      <p class="form-subtitle">Escriba el CUIT de la empresa para continuar.</p>
    </div>

    <div class="form-container">
      <form method="post" autocomplete="off">
        <div class="form-group" style="position: relative;">
          <label class="form-label">CUIT <span style="color:red">*</span></label>
          <input
            class="form-input1"
            name="cuit"
            type="text"
            required
            placeholder="ej. 20-12345678-6"
            value="<?= htmlspecialchars($_POST['cuit'] ?? '') ?>">
          <?php if ($error): ?>
            <div class="error-message" style="color:red; margin-top:6px;">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Botones como tu imagen -->
        <div class="btn-group">
          <button type="button" onclick="location.href='tipo_identidad.php'" class="submit-btn back">Volver</button>
          <button type="submit" class="submit-btn">Siguiente</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($showExistsAlert): ?>
<script>
  Swal.fire({
    icon: 'info',
    title: 'Este CUIT ya está registrado',
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#166379',
    allowOutsideClick: false,
    allowEscapeKey: false
  }).then(() => {
    window.location.assign('registro.php?paso=1');
  });
</script>
<?php endif; ?>

</body>
</html>
