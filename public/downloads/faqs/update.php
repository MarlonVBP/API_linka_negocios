<?php
include '../../../cors.php';
include '../../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Permitir apenas requisições PUT
if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas PUT é aceito.',
    ]);
    exit;
}

// Obter e processar os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? intval($data->id) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID do registro não fornecido.'
    ]);
    exit;
}

try {
    // Verificar se o registro existe
    $select_query = "SELECT * FROM `exemplo` WHERE id_exemplo = :id_exemplo";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':id_exemplo', $id, PDO::PARAM_INT);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {
        $dado_de_exemplo1 = htmlspecialchars(trim($data->dado1));
        $dado_de_exemplo2 = htmlspecialchars(trim($data->dado2));
        $dado_de_exemplo3 = htmlspecialchars(trim($data->dado3));
        // $exemplo_senha = htmlspecialchars(trim($data->senha)); // Descomente se precisar atualizar a senha

        // Preparar a consulta SQL para atualização
        $update_query = "UPDATE `exemplo` SET 
                            dado_de_exemplo1 = :dado_de_exemplo1, 
                            dado_de_exemplo2 = :dado_de_exemplo2, 
                            dado_de_exemplo3 = :dado_de_exemplo3 
                        WHERE id_exemplo = :id_exemplo";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':dado_de_exemplo1', $dado_de_exemplo1, PDO::PARAM_STR);
        $update_stmt->bindValue(':dado_de_exemplo2', $dado_de_exemplo2, PDO::PARAM_STR);
        $update_stmt->bindValue(':dado_de_exemplo3', $dado_de_exemplo3, PDO::PARAM_STR);
        // $update_stmt->bindValue(':exemplo_senha', $exemplo_senha, PDO::PARAM_STR); // Descomente se precisar atualizar a senha

        $update_stmt->bindValue(':id_exemplo', $id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            http_response_code(200); // Código HTTP 200 para sucesso na atualização
            echo json_encode([
                'success' => 1,
                'message' => 'Dados atualizados com sucesso.'
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Falha na atualização dos dados.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Registro não encontrado para o ID fornecido.'
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
