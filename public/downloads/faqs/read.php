<?php
include '../../../cors.php';
include '../../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas GET e aceito.',
    ]);
    exit;
}

try {
    $resposta = isset($_GET['resposta']) ? filter_var($_GET['resposta'], FILTER_VALIDATE_BOOLEAN) : null;

    $select = "SELECT id, pergunta, resposta FROM faq";

    if ($resposta === true) {
        $select .= " WHERE resposta IS NULL OR resposta = ''";
    }

    $stmt = $connection->prepare($select);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $vetor_faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach ($vetor_faqs as &$faq) {
            $faq['active'] = false;

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
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
    exit;
}
?>