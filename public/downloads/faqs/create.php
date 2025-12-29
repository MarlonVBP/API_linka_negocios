<?php
include '../../../cors.php';
include '../../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas POST e permitido.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

try {
    
    if (isset($data->pergunta) && !isset($data->id)) {
        
        $pergunta = htmlspecialchars(trim($data->pergunta));

        $query = "INSERT INTO `faq` (pergunta) VALUES (:pergunta)";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(':pergunta', $pergunta, PDO::PARAM_STR);

        if ($stmt->execute()) {
            
            $id_pergunta = $connection->lastInsertId();

            http_response_code(201); 
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
    } elseif (isset($data->resposta) && isset($data->id)) {
        
        $resposta = htmlspecialchars(trim($data->resposta));
        $id_pergunta = intval($data->id);

        $query = "UPDATE `faq` SET resposta = :resposta WHERE id = :id_pergunta";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(':resposta', $resposta, PDO::PARAM_STR);
        $stmt->bindValue(':id_pergunta', $id_pergunta, PDO::PARAM_INT);

        if ($stmt->execute()) {
            http_response_code(200); 
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
        
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Dados inválidos'
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