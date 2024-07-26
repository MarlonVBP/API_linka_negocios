<?php
include '../../../cors.php';
include '../../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Permitir apenas requisições POST
if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas POST é permitido.',
    ]);
    exit;
}

// Obter e processar os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Verificar se estamos inserindo uma pergunta ou uma resposta
    if (isset($data->pergunta) && !isset($data->id_resposta)) {
        // Inserir uma nova pergunta
        $pergunta = htmlspecialchars(trim($data->pergunta));

        // Preparar a consulta SQL para inserção da pergunta
        $query = "INSERT INTO `faq` (pergunta) VALUES (:pergunta)";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(':pergunta', $pergunta, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Obter o ID da nova pergunta inserida
            $id_pergunta = $connection->lastInsertId();

            http_response_code(201); // Criado
            echo json_encode([
                'success' => 1,
                'message' => 'Pergunta inserida com sucesso',
                'id_pergunta' => $id_pergunta
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Falha na inserção da pergunta'
            ]);
        }
    } elseif (isset($data->resposta) && isset($data->id_pergunta)) {
        // Inserir uma resposta associada a uma pergunta existente
        $resposta = htmlspecialchars(trim($data->resposta));
        $id_pergunta = intval($data->id_pergunta);

        // Preparar a consulta SQL para inserção da resposta
        $query = "UPDATE `faq` SET resposta = :resposta WHERE id = :id_pergunta";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(':resposta', $resposta, PDO::PARAM_STR);
        $stmt->bindValue(':id_pergunta', $id_pergunta, PDO::PARAM_INT);

        if ($stmt->execute()) {
            http_response_code(200); // OK
            echo json_encode([
                'success' => 1,
                'message' => 'Resposta inserida com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Falha na inserção da resposta'
            ]);
        }
    } else {
        // Dados inválidos
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Dados inválidos'
        ]);
    }
} catch (PDOException $e) {
    // Definir código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
