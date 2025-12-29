<?php
include '../../cors.php';
include '../../conn.php';
require_once '../../admin/login/jwtEhValido.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido'
];

$token = $_COOKIE['auth_token'] ?? null;

if (!$token || !jwt_eh_valido($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['id']) && is_numeric($data['id'])) {
        $post_id = intval($data['id']);

        try {
            if ($connection) {
                $query = "DELETE FROM postagens WHERE id = :post_id";
                $stmt = $connection->prepare($query);

                $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $response = [
                        'success' => true,
                        'message' => 'Postagem excluída com sucesso'
                    ];
                } else {
                    $response['message'] = 'Falha na exclusão da postagem';
                }
            } else {
                $response['message'] = 'Falha na conexão com o banco de dados';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Erro de banco de dados: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'ID da postagem não fornecido ou inválido';
    }
}

echo json_encode($response);
