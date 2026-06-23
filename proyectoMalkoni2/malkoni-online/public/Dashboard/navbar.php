<?php
// public/Dashboard/navbar.php  (INCLUDE ONLY)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
$entityManager = require __DIR__ . '/../../config/doctrine.php';

use Entities\Personas;
use Entities\Empresas;

// ===============================
// Config opcional desde la página que incluye
// ===============================
$navbarTitle          = $navbarTitle ?? 'SERVICIOS ONLINE';
$navbarLogoHref       = $navbarLogoHref ?? 'opt.php';
$navbarShowOptButtons = $navbarShowOptButtons ?? false;

// ✅ Botón "Generar Cotización"
$navbarShowCotizarBtn = $navbarShowCotizarBtn ?? false;
$navbarCotizarHref    = $navbarCotizarHref ?? 'cotizar_mis_pedidos.php';

// ===============================
// Usuario
// ===============================
$repoPersona = $entityManager->getRepository(Personas::class);
$persona     = $repoPersona->findOneBy(['email' => $_SESSION['usuario']]);

$role = $persona ? (int)$persona->getRol() : 0;

// ===============================
// Empresa activa (por sesión)
// ===============================
$empresaEntity = null;
$empresaNombre = '';
$codCondIVA    = '';

$empresaIdSesion = (int)($_SESSION['empresa_id'] ?? 0);
if ($empresaIdSesion > 0) {
    $empresaEntity = $entityManager->getRepository(Empresas::class)->find($empresaIdSesion);
}

// Fallback: empresa principal
if (!$empresaEntity && $persona && method_exists($persona, 'getEmpresa')) {
    $empresaEntity = $persona->getEmpresa();
    if ($empresaEntity instanceof Empresas) {
        $_SESSION['empresa_id'] = (int)$empresaEntity->getId();
    }
}

if ($empresaEntity instanceof Empresas) {
    $empresaNombre = $empresaEntity->getRazonSocial() ?: '';
    $codCondIVA    = $empresaEntity->getCodCondIVA() ?: '';
}

$isConsumidorFinal = ($codCondIVA === 'CF');

// ===============================
// Avatar y nombre
// ===============================
$g = $persona ? $persona->getGenero() : 'M';
$avatar = ($g === 'F')
    ? 'https://img.icons8.com/bubbles/150/000000/user-female-circle.png'
    : 'https://img.icons8.com/bubbles/150/000000/user-male-circle.png';

$nombreCompleto = $persona
    ? trim(($persona->getNombre() ?? '') . ' ' . ($persona->getApellido() ?? ''))
    : '';

$integrationApiUrl = getenv('INTEGRATION_API_URL') ?: 'https://pencil-shine-postage.ngrok-free.dev/api/v1/cotizaciones/importar';
$integrationToken = getenv('INTEGRATION_TOKEN') ?: 'e40bee85d1d3c3de02b085f7a93210115778f7c14757b674dfa26c62ad1bb704';
?>

<header class="header <?= $isConsumidorFinal ? 'cf' : '' ?> <?= ($navbarContext ?? '') === 'opt' ? 'navbar-opt' : '' ?>">

  <!-- Col 1: Logo + Botón -->
    <div class="logo-container">
      <a href="<?= htmlspecialchars($navbarLogoHref, ENT_QUOTES, 'UTF-8') ?>" class="logo-link">
        <img src="../logo.png" alt="Malkoni Hnos">
      </a>
    
      <?php if ($navbarShowCotizarBtn): ?>
        <button id="btnNavbarCotizar" type="button" class="btn-navbar-cotizar">
          GENERAR COTIZACIÓN
        </button>
      <?php endif; ?>
    </div>
    
    <!-- Col 2: Centro (solo título) -->
    <div class="center-area">
      <div class="main-title"><?= htmlspecialchars($navbarTitle, ENT_QUOTES, 'UTF-8') ?></div>
    </div>


  <!-- Derecha: Botón + Usuario + Menú -->
  <div class="right-area">

    <div class="user-container">
      <button class="avatar-btn" onclick="location.href='perfil.php'">
        <img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="avatar-img">
      </button>

      <div class="user-meta">
        <?php if ($nombreCompleto): ?>
          <div class="meta-line">
            <span class="meta-k">Usuario:</span>
            <span class="meta-v"><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>

        <?php if (!$isConsumidorFinal && !empty($empresaNombre)): ?>
          <div class="meta-line">
            <span class="meta-k">Empresa:</span>
            <span class="meta-v"><?= htmlspecialchars($empresaNombre, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="menu-area">
      <span class="burger-label"><strong>Menú</strong></span>
      <div class="burger" id="burger">&#9776;</div>
    </div>
  </div>
</header>

<nav id="sideMenu" class="side-menu">
  <div class="close-btn" id="closeBtn">&times;</div>

  <div class="menu-logo">
    <img src="../logo.png" alt="Malkoni Hnos">
  </div>

  <div class="menu-items">
    <button id="profileBtn">Perfil</button>

    <?php if ($role === 1): ?>
      <button id="addUserBtn">Usuarios</button>
    <?php endif; ?>

    <?php if ($role === 2 && !$isConsumidorFinal): ?>
      <button id="cambiarEmpresa">Asociarse a otra empresa</button>
      <button id="empresasAsociadas">Empresas asociadas</button>
    <?php endif; ?>

    <?php if ($navbarShowOptButtons): ?>
      <button id="siteBtn">Sitio Web</button>
      <button id="supportBtn">Soporte</button>
    <?php endif; ?>

    <button
      type="button"
      id="goCotizacionesOnlineBtn"
      class="nav-link btn btn-outline-primary btn-sm text-white px-3 d-inline-flex align-items-center justify-content-center gap-2 mt-2"
    >
      <i class="bi bi-grid-1x2-fill"></i>
      <span>Ir a Cotizaciones Online</span>
    </button>

    <button id="changePassBtn">Cambiar Contraseña</button>
    <button id="logoutBtn">Cerrar Sesión</button>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const INTEGRATION_API_URL = <?= json_encode($integrationApiUrl) ?>;
  const INTEGRATION_TOKEN = <?= json_encode($integrationToken) ?>;
  const SESSION_USER_ID = <?= json_encode((int) ($_SESSION['id'] ?? 0)) ?>;
  const SESSION_EMPRESA_ID = <?= json_encode((int) ($_SESSION['empresa_id'] ?? 0)) ?>;
  const TOKEN_OPT = <?= json_encode($persona ? (string) ($persona->getTokenOpt() ?? '') : '') ?>;
  const PERSONA_NOMBRE = <?= json_encode($persona ? (string) ($persona->getNombre() ?? '') : '') ?>;
  const PERSONA_APELLIDO = <?= json_encode($persona ? (string) ($persona->getApellido() ?? '') : '') ?>;
  const PERSONA_EMAIL = <?= json_encode($persona ? (string) ($persona->getEmail() ?? '') : '') ?>;
  const PERSONA_DNI = <?= json_encode($persona ? (string) ($persona->getDni() ?? '') : '') ?>;
  const PERSONA_GENERO = <?= json_encode($persona ? (string) ($persona->getGenero() ?? '') : '') ?>;
  const PERSONA_TEL = <?= json_encode($persona ? (string) ($persona->getNumTel() ?? '') : '') ?>;
  const EMPRESA_RAZON_SOCIAL = <?= json_encode($empresaEntity ? (string) ($empresaEntity->getRazonSocial() ?? '') : '') ?>;
  const EMPRESA_CUIT = <?= json_encode($empresaEntity ? (string) ($empresaEntity->getCuit() ?? '') : '') ?>;
  const EMPRESA_IVA = <?= json_encode($empresaEntity ? (string) ($empresaEntity->getCodCondIVA() ?? '') : '') ?>;

  const sideMenu = document.getElementById('sideMenu');
  const burger = document.getElementById('burger');
  const closeBtn = document.getElementById('closeBtn');

  if (burger) burger.addEventListener('click', () => sideMenu.classList.add('active'));
  if (closeBtn) closeBtn.addEventListener('click', () => sideMenu.classList.remove('active'));

  const profileBtn = document.getElementById('profileBtn');
  if (profileBtn) profileBtn.addEventListener('click', () => location.href = 'perfil.php');

  const addUserBtn = document.getElementById('addUserBtn');
  if (addUserBtn) addUserBtn.addEventListener('click', () => location.href = 'usuarios.php');

  const cambiarEmpresaBtn = document.getElementById('cambiarEmpresa');
  if (cambiarEmpresaBtn) cambiarEmpresaBtn.addEventListener('click', () => location.href = 'cambiar_empresa.php');

  const empresasAsociadasBtn = document.getElementById('empresasAsociadas');
  if (empresasAsociadasBtn) empresasAsociadasBtn.addEventListener('click', () => location.href = 'empresas_asociadas.php');

  const changePassBtn = document.getElementById('changePassBtn');
  if (changePassBtn) changePassBtn.addEventListener('click', () => window.location.href = '../../recuperar_contraseña.php');

  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = '../logout.php';
    });
  }

  const goCotizacionesOnlineBtn = document.getElementById('goCotizacionesOnlineBtn');
  if (goCotizacionesOnlineBtn) {
    goCotizacionesOnlineBtn.addEventListener('click', async function() {
      const payload = {
        persona_external_id: Number.parseInt(SESSION_USER_ID, 10),
        empresa_activa_external_id: Number.parseInt(SESSION_EMPRESA_ID, 10),
        token_opt: TOKEN_OPT,
        pedido_id: null,
        pdf_url: null,
        persona_nombre: PERSONA_NOMBRE,
        persona_apellido: PERSONA_APELLIDO,
        persona_email: PERSONA_EMAIL,
        persona_dni: PERSONA_DNI,
        persona_genero: PERSONA_GENERO,
        persona_tel: PERSONA_TEL,
        empresa_razon_social: EMPRESA_RAZON_SOCIAL,
        empresa_cuit: EMPRESA_CUIT,
        empresa_iva: EMPRESA_IVA
      };

      try {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Sincronizando acceso...',
            text: 'Preparando tu sesión en Cotizaciones Online.',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
          });
        }

        const response = await fetch(INTEGRATION_API_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Integration-Token': INTEGRATION_TOKEN
          },
          body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || !data?.redirect_url) {
          throw new Error(data?.message || `HTTP ${response.status}`);
        }

        window.location.href = data.redirect_url;
      } catch (error) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'No se pudo abrir Cotizaciones Online',
            text: error.message || 'Error inesperado al sincronizar el acceso.'
          });
        }
      }
    });
  }

  // SweetAlert del botón Generar Cotización
  const btnCot = document.getElementById('btnNavbarCotizar');
  if (btnCot) {
    btnCot.addEventListener('click', () => {
      if (typeof Swal === 'undefined') {
        window.location.href = <?= json_encode($navbarCotizarHref) ?>;
        return;
      }

      Swal.fire({
        icon: 'info',
        title: 'GENERAR COTIZACIÓN',
        html: `
          <div class="swal-body">
            <p class="swal-p">
              Podés generar una cotización a partir de pedidos que ya creaste en el Optimizador de Cortes.
            </p>
            <p class="swal-p">
              Presioná <b>Continuar</b> para seleccionar un pedido existente, o <b>Volver</b> para seguir creando una nueva optimización.
            </p>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Volver',
        buttonsStyling: false,
        customClass: {
          popup: 'malkoni-swal',
          title: 'malkoni-swal-title',
          htmlContainer: 'malkoni-swal-html',
          confirmButton: 'malkoni-swal-confirm',
          cancelButton: 'malkoni-swal-cancel'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = <?= json_encode($navbarCotizarHref) ?>;
        }
      });
    });
  }
});
</script>
