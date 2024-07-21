<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Permitir apenas requisições POST
if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas POST é permitido.',
    ]);
    exit;
}

// Obter e processar os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Sanitizar os dados recebidos
    $dado_de_exemplo1 = htmlspecialchars(trim($data->dado1));
    $dado_de_exemplo2 = htmlspecialchars(trim($data->dado2));

    // Preparar a consulta SQL para inserção
    $query = "INSERT INTO `cliente` (
            dado_de_exemplo1,
            dado_de_exemplo2
            ) 
            VALUES (
            :dado_de_exemplo1,
            :dado_de_exemplo2
            )";

    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
    $stmt->bindValue(':dado_de_exemplo1', $dado_de_exemplo1, PDO::PARAM_STR);
    $stmt->bindValue(':dado_de_exemplo2', $dado_de_exemplo2, PDO::PARAM_STR);

    // Executar a consulta e verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        http_response_code(201); // Criado
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    // Se a inserção falhar, retornar uma mensagem de erro
    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
    ]);
    exit;

} catch (PDOException $e) {
    // Definir código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
