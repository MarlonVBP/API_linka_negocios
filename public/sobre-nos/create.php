<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

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

$data = json_decode(file_get_contents("php://input"));

try {
    $nome = htmlspecialchars(trim($data->nome));
    $email = htmlspecialchars(trim($data->email));
    $telefone = htmlspecialchars(trim($data->telefone));
    $empresa = htmlspecialchars(trim($data->empresa));
    $area_atuacao = htmlspecialchars(trim($data->area_atuacao));
    $mensagem = htmlspecialchars(trim($data->mensagem));

    $query = "INSERT INTO `contato` (
            nome,
            email,
            telefone,
            empresa,
            area_atuacao,
            mensagem
            ) 
            VALUES (
            :nome,
            :email,
            :telefone,
            :empresa,
            :area_atuacao,
            :mensagem
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
    $stmt->bindValue(':area_atuacao', $area_atuacao, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201); 
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
    ]);
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
