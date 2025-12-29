<?php
include '../../../cors.php';
include '../../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas DELETE e aceito.',
    ]);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'Por favor, forneça o ID do post.'
    ]);
    exit;
}

try {
    $fetch_post = "SELECT * FROM `faq` WHERE id = :id";
    $fetch_stmt = $connection->prepare($fetch_post);
    $fetch_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $fetch_stmt->execute();

    if ($fetch_stmt->rowCount() > 0) {
        $delete_post = "DELETE FROM `faq` WHERE id = :id";
        $delete_stmt = $connection->prepare($delete_post);
        $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => 1,
                'message' => 'Registro excluído com sucesso.'
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Falha ao excluir o registro. Algo deu errado.'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'ID inválido. Nenhum registro encontrado com o ID fornecido.'
        ]);
        exit;
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
