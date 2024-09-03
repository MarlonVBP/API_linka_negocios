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
    $postagem_id = htmlspecialchars(trim($data->postagem_id));
    $tag_ids = $data->tag_id; // Assume que $tag_ids é um array

    if (!is_array($tag_ids)) {
        throw new Exception('O parâmetro tag_id deve ser um array.');
    }

    $connection->beginTransaction(); // Iniciar transação para garantir integridade dos dados

    // Excluir todas as tags atuais para o postagem_id
    $deleteQuery = "DELETE FROM `postagem_tags` WHERE postagem_id = :postagem_id";
    $deleteStmt = $connection->prepare($deleteQuery);
    $deleteStmt->bindValue(':postagem_id', $postagem_id, PDO::PARAM_INT);

    if (!$deleteStmt->execute()) {
        $connection->rollBack(); // Desfazer alterações em caso de falha
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => 'Falha ao remover tags existentes'
        ]);
        exit;
    }

    // Inserir as novas tags
    $insertQuery = "INSERT INTO `postagem_tags` (postagem_id, tag_id) VALUES (:postagem_id, :tag_id)";
    $insertStmt = $connection->prepare($insertQuery);

    foreach ($tag_ids as $tag_id) {
        $tag_id = htmlspecialchars(trim($tag_id));
        $insertStmt->bindValue(':postagem_id', $postagem_id, PDO::PARAM_INT);
        $insertStmt->bindValue(':tag_id', $tag_id, PDO::PARAM_INT);

        if (!$insertStmt->execute()) {
            $connection->rollBack(); // Desfazer alterações em caso de falha
            http_response_code(500);
            echo json_encode([
                'success' => 0,
                'message' => 'Falha ao adicionar novas tags'
            ]);
            exit;
        }
    }

    $connection->commit(); // Confirmar transação se tudo correr bem

    http_response_code(201);
    echo json_encode([
        'success' => 1,
        'message' => 'Tags atualizadas com sucesso'
    ]);
    exit;
} catch (Exception $e) {
    $connection->rollBack(); // Garantir que a transação seja desfeita em caso de erro
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
