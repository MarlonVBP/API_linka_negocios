<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

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

$data = json_decode(file_get_contents("php://input"));

try {
    $titulo = htmlspecialchars(trim($data->titulo));
    $mensagem = htmlspecialchars(trim($data->mensagem));
    $imagem = htmlspecialchars(trim($data->imagem));

    $query = "INSERT INTO `casos_sucesso` (
            titulo,
            mensagem,
            imagem
            ) 
            VALUES (
            :titulo,
            :mensagem,
            :imagem
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
    $stmt->bindValue(':imagem', $imagem, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201); 
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
