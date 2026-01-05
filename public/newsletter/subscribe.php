<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => 0, 'message' => 'Método não permitido.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || empty($data->email)) {
    http_response_code(400);
    echo json_encode(['success' => 0, 'message' => 'E-mail é obrigatório.']);
    exit;
}

$email = filter_var(trim($data->email), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => 0, 'message' => 'Formato de e-mail inválido.']);
    exit;
}

try {
    
    $checkQuery = "SELECT id, ativo FROM newsletter WHERE email = :email";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $checkStmt->execute();
    
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        
        if ($existingUser['ativo'] == 1) {
            echo json_encode([
                'success' => 1, 
                'message' => 'Este e-mail já está inscrito e ativo em nossa lista!'
            ]);
        } else {
            $reactivateQuery = "UPDATE newsletter SET ativo = 1 WHERE id = :id";
            $reactivateStmt = $connection->prepare($reactivateQuery);
            $reactivateStmt->bindValue(':id', $existingUser['id'], PDO::PARAM_INT);
            
            if ($reactivateStmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'success' => 1,
                    'message' => 'Bem-vindo de volta! Sua inscrição foi reativada com sucesso.'
                ]);
            } else {
                throw new Exception("Erro ao reativar inscrição.");
            }
        }
        exit;
    }

    $query = "INSERT INTO newsletter (email, ativo) VALUES (:email, 1)";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Inscrição realizada com sucesso! Obrigado.'
        ]);
    } else {
        throw new Exception("Erro ao salvar no banco.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => 0, 'message' => 'Erro interno ao processar inscrição.']);
}
?>