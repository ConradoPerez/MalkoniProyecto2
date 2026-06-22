<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

require_once __DIR__ . '/../vendor/autoload.php';

// Ruta donde estar¨¢n tus entidades (clases mapeadas a tablas)
$paths = [__DIR__ . '/../src/Entities'];
$isDevMode = true; // activar cache en producci¨®n

// Configuraci¨®n de conexi¨®n a la base de datos
$dbParams = [
    'driver'   => 'pdo_mysql',
    'user'     => 'malkoni_online',
    'password' => '#$Mcp4n3lI$#',
    'dbname'   => 'malkoni_online',
    'host'     => 'localhost',
    'charset'  => 'utf8mb4'
];

// Configurar el ORM de Doctrine
$config = ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$connection = DriverManager::getConnection($dbParams, $config);
$entityManager = new EntityManager($connection, $config);

// Para incluir en otros archivos
return $entityManager;
