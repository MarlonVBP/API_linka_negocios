<?php
declare(strict_types=1);

// Lista de origens permitidas (ajuste conforme necessário)
$allowedOrigins = [
    'https://linkanegocios.com.br',
    'http://localhost:4200'  // remover depois
];

// Obtém a origem da solicitação
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Verifica se a origem da solicitação está na lista de permitidas
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: none");
}

header("Access-Control-Allow-Credentials: true");

// Permite que os métodos HTTP GET, POST, PUT e DELETE sejam usados a partir de origens diferentes
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Define os cabeçalhos permitidos para serem incluídos na solicitação
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se a requisição for do tipo OPTIONS, retorne um status 200 e termine a execução do script
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');
?>
