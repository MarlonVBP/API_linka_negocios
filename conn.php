<?php
date_default_timezone_set('America/Sao_Paulo');

// $host = "br440";
// $database = "linkan76_linka_negocios";
// $usuario = "linkan76_linka_negocios";
// $senha = 'L!nK@H0sT_N$g-';

$host = "localhost";
$database = "linknegocios";
$usuario = "root";
$senha = '';

try {
    $dsn = 'mysql:host=' . $host . ';dbname=' . $database;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $connection = new PDO($dsn, $usuario, $senha, $options);
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    exit;
}
?>
