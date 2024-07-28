<?php
include '../../cors.php';
include '../../conn.php';

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido'
];

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['email'])) {
        $email = $data['email'];

        $sql = "SELECT id FROM admin WHERE email=:email";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $usuario_id = $user['id'];

            if (isset($data['id'])) {
                $post_id = intval($data['id']);

                $query = "DELETE FROM postagens WHERE id = :post_id AND usuario_id = :usuario_id";
                $stmt = $connection->prepare($query);

                $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
                $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $response = [
                        'success' => true,
                        'message' => 'Postagem excluída com sucesso'
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Falha na exclusão da postagem'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'ID da postagem não fornecido'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Usuário não encontrado'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Email não fornecido'
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
