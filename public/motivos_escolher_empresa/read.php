<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas GET é aceito.',
    ]);
    exit;
}

try {
    $query = "SELECT id, titulo, descricao, imagem FROM motivos_escolher_empresa";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($servicos) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'data' => $servicos
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum motivo encontrada'
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
