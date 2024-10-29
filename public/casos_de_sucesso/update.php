<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas PUT e aceito.',
    ]);
    exit;
}

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
    $select_query = "SELECT * FROM casos_sucesso WHERE id = :id";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {
        $titulo = htmlspecialchars(trim($data->titulo));
        $mensagem = htmlspecialchars(trim($data->mensagem));
        $imagem = htmlspecialchars(trim($data->imagem));

        $update_query = "UPDATE casos_sucesso SET 
                            titulo = :titulo, 
                            mensagem = :mensagem, 
                            imagem = :imagem 
                        WHERE id = :id";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $update_stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
        $update_stmt->bindValue(':imagem', $imagem, PDO::PARAM_STR);
        $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            http_response_code(200); 
            echo json_encode([
                'success' => 1,
                'message' => 'Caso de sucesso atualizado com sucesso.'
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Falha na atualização do caso de sucesso.'
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
