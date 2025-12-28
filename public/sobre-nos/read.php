<?php
include '../../cors.php';
include '../../conn.php';

// Verificar se o método de requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas GET e aceito.',
    ]);
    exit;
}

try {
    // Preparar e executar a consulta SQL
    $select = "SELECT * FROM contato WHERE visualizado = true;";
    $stmt = $connection->prepare($select);
    $stmt->execute();    

    // Verificar se há registros
    if ($stmt->rowCount() > 0) {
        $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => 1,
            'contatos' => $contatos,
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado.',
            'contatos' => [],
        ]);
    }
} catch (PDOException $e) {
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
    exit;
}
?>