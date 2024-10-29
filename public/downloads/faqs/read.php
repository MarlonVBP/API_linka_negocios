<?php
include '../../../cors.php';
include '../../../conn.php';

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
    // Obter o parâmetro resposta
    $resposta = isset($_GET['resposta']) ? filter_var($_GET['resposta'], FILTER_VALIDATE_BOOLEAN) : null;

    // Preparar a consulta SQL base
    $select = "SELECT id, pergunta, resposta FROM faq";

    // Adicionar condição para excluir perguntas com respostas, se necessário
    if ($resposta === true) {
        $select .= " WHERE resposta IS NULL OR resposta = ''";
    }

    // Preparar e executar a consulta SQL
    $stmt = $connection->prepare($select);
    $stmt->execute();

    // Verificar se há registros
    if ($stmt->rowCount() > 0) {
        $vetor_faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Adicionar o campo 'active' com valor false a cada registro
        foreach ($vetor_faqs as &$faq) {
            $faq['active'] = false;
            // Adicionar mensagem se o campo 'resposta' estiver vazio
            if (empty($faq['resposta'])) {
                $faq['resposta'] = 'Esta pergunta será respondida em breve';
            }
        }

        echo json_encode([
            'success' => 1,
            'response' => $vetor_faqs,
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado.',
            'response' => [],
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
