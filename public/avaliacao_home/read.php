<?php
include '../../cors.php';  
include '../../conn.php';  

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas GET e permitido.',
    ]);
    exit;
}

try {
    $query = "
        SELECT id, nome, avaliacao, mensagem, foto_perfil, criado_em
        FROM avaliacao_empresa
        ORDER BY avaliacao DESC
        LIMIT 3
    ";

    $stmt = $connection->prepare($query);
    $stmt->execute();

    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success' => 1,
        'data' => $avaliacoes
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
