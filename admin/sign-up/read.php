<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas GET é aceito.',
    ]);
    exit;
}

$email = isset($_GET['email']) ? htmlspecialchars(trim($_GET['email'])) : null;

if (!$email) {
    echo json_encode([
        'success' => 0,
        'message' => 'E-mail não fornecido.'
    ]);
    exit;
}

try {
    $select = "SELECT * FROM admin WHERE email = :email";
    $stmt = $connection->prepare($select);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => 1,
            'data' => $admin
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado para o e-mail fornecido.',
            'data' => null
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
