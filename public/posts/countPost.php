
<?php
include '../../cors.php';
include '../../conn.php';

try {
    $query = "SELECT COUNT(*) FROM postagens";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $number = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($number) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'response' => $number
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhuma categoria encontrada'
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
