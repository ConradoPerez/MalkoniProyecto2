<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/api.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $token = sga_obtener_token();

    echo "TOKEN OK:\n";
    echo $token . "\n";

} catch (Throwable $e) {
    echo "ERROR al obtener token:\n";
    echo $e->getMessage() . "\n";
}
