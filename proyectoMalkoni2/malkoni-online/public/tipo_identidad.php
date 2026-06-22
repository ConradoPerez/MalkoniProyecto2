<?php
    header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccione su tipo de identidad</title>
    <link rel="stylesheet" href="styles/tipo_identidadStyles.css?v=<?= time() ?>">
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-container">
    <!-- Panel izquierdo: solo logo centrado -->
    <div class="left-panel">
        <div class="logo">
            <img src="logo.png" alt="Malkoni Hnos" class="logo-img">
        </div>
    </div>

    <!-- Panel derecho: texto de tipo de cuenta y selecci¨®n -->
    <div class="right-panel">
        <div class="form-container">
            <h2>TIPO DE CUENTA</h2>
           

            <div class="d-grid">
                <a href="validar_usuario_cf.php" class="btn btn-teal">Quiero ser usuario independiente</a>
                <a href="validar_usuario.php" class="btn btn-gray">Quiero ser usuario de una Empresa existente</a>
                <a href="registro_cuit.php" class="btn btn-gray">Crear Nueva empresa y usuario</a>
            </div>

            
        </div>
    </div>
</div>

</body>
</html>
