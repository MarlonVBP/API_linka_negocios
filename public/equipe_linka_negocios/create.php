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
        'message' => 'Metodo nao permitido. Apenas POST e permitido.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

try {
    $nome = htmlspecialchars(trim($data->nome));
    $descricao = htmlspecialchars(trim($data->descricao));
    $imagem = htmlspecialchars(trim($data->imagem));

    $query = "INSERT INTO `equipe_linka_negocios` (
            nome,
            descricao,
            imagem
            ) 
            VALUES (
            :nome,
            :descricao,
            :imagem
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
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
