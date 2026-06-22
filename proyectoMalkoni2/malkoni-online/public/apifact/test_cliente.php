<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/api.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $token = sga_obtener_token();
    echo "token:\n$token\n\n";

    // ===== EMPRESA (pfj=1) - sin dni - sin apellido/nombre/genero =====
    $clienteData = [
    'id'         => 12344333,
    'codcli'     => "",

    'pfj'        => 1, // EMPRESA

    'rsocial'    => "EMPRESA PRUEBA DOMICILIO VACIO SA",
    'cuit'       => "20411962483",
    'codcondiva' => "MT", // RI / MT / EX según corresponda

    // domicilio vacío a propósito (caso de prueba)
    'domicilio'  => "hola123",

    'telefono'   => "3511299999",
    'celular'    => "",
    'barrio'     => "",
    'mail'       => "empresa.prueba@malkoni.com.ar",

    'codloc'     => 10,
    'codpcia'    => 1,
    'cp'         => "5000",
];


$payloadDirecto = [
    'cred' => [
        'terminal' => SGA_TERMINAL,
        'token'    => $token,
    ],
    'data' => $clienteData,
];


    echo "PAYLOAD:\n";
    echo json_encode($payloadDirecto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";

    $respDirecto = sga_http_post('/clientes', $payloadDirecto);

    echo "HTTP CODE (directo): {$respDirecto['code']}\n\n";
    echo "Respuesta RAW (directo):\n";
    echo $respDirecto['raw'] . "\n\n";

    echo "Respuesta PARSEADA (directo):\n";
    var_export($respDirecto['body']);
    echo "\n\n";

    if (isset($respDirecto['body']['data']['detalles'][0]['codcli'])) {
        echo "codcli devuelto (directo, detalles[0]): " .
            $respDirecto['body']['data']['detalles'][0]['codcli'] . "\n\n";
    } elseif (!empty($respDirecto['body']['data']['codcli'])) {
        echo "codcli devuelto (directo, data): " .
            $respDirecto['body']['data']['codcli'] . "\n\n";
    }

    echo "===== PRUEBA syncClienteFacturacion() =====\n";
    $codcli = syncClienteFacturacion($clienteData);
    echo "syncClienteFacturacion() devolvió codcli:\n";
    var_export($codcli);
    echo "\n\n";

} catch (Throwable $e) {
    echo "ERROR en test_cliente:\n";
    echo $e->getMessage() . "\n";
}
