<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas GET e aceito.',
    ]);
    exit;
}

$postagem_id = isset($_GET['postagem_id']) ? htmlspecialchars(trim($_GET['postagem_id'])) : null;

if (!$postagem_id) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Parâmetro postagem_id é obrigatório.'
    ]);
    exit;
}

try {
    $query = "
        SELECT t.id, t.nome, t.criado_em 
        FROM tags t
        JOIN postagem_tags pt ON t.id = pt.tag_id
        WHERE pt.postagem_id = :postagem_id
    ";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':postagem_id', $postagem_id, PDO::PARAM_INT);
    $stmt->execute();

    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($tags) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'response' => $tags
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhuma tag encontrada para o postagem_id fornecido'
        ]);
    }
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
