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

try {
    $select = "SELECT * FROM equipe_linka_negocios";
    $stmt = $connection->prepare($select);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $equipe_linka_negocios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => 1,
            'equipe_linka_negocios' => $equipe_linka_negocios,
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado.',
            'equipe_linka_negocios' => [],
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
