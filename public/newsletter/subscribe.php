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
    
    $checkQuery = "SELECT id FROM newsletter WHERE email = :email";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        
        echo json_encode([
            'success' => 1, 
            'message' => 'Este e-mail já está inscrito em nossa lista!'
        ]);
        exit;
    }

    $query = "INSERT INTO newsletter (email) VALUES (:email)";
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

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>