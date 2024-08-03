<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas GET é permitido.',
    ]);
    exit;
}

try {
    $query = "SELECT * FROM ProdutoDivulgacao";

    $stmt = $connection->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        http_response_code(200); 
        echo json_encode([
            'success' => 1,
            'data' => $result
        ]);
    } else {
        http_response_code(404); 
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum produto encontrado.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
?>
