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
    $query = "SELECT id, titulo, descricao, imagem, conteudo1, conteudo2, conteudo3 FROM servicos";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($servicos as &$servico) {
        // Decodificar as entidades HTML
        $servico['titulo'] = html_entity_decode($servico['titulo'], ENT_QUOTES, 'UTF-8');
        $servico['descricao'] = html_entity_decode($servico['descricao'], ENT_QUOTES, 'UTF-8');
        $servico['conteudo1'] = html_entity_decode($servico['conteudo1'], ENT_QUOTES, 'UTF-8');
        $servico['conteudo2'] = html_entity_decode($servico['conteudo2'], ENT_QUOTES, 'UTF-8');
        $servico['conteudo3'] = html_entity_decode($servico['conteudo3'], ENT_QUOTES, 'UTF-8');
        
    }

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
            'message' => 'Nenhum servico encontrada'
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
