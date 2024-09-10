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
    $select = "SELECT * FROM casos_sucesso";
    $stmt = $connection->prepare($select);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $casos_sucesso = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => 1,
            'casos_sucesso' => $casos_sucesso,
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado.',
            'casos_sucesso' => [],
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
    exit;
}
?>
