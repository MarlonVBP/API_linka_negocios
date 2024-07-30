<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT id FROM admin WHERE email=:email";
    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario_id = $user['id'];

        if (isset($_POST['title']) && isset($_POST['content']) && isset($_FILES['image']) && isset($_POST['category_id'])) {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $description = trim($_POST['description']);
            $category_id = intval($_POST['category_id']);

            if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $image = $_FILES['image']['name'];
                $target_dir = "imagens/";
                $target_file = $target_dir . basename($image);

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $query = "INSERT INTO postagens (usuario_id, categoria_id, titulo, conteudo, descricao, url_imagem) VALUES (:usuario_id, :categoria_id, :titulo, :conteudo, :descricao, :url_imagem)";
                    $stmt = $connection->prepare($query);

                    $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
                    $stmt->bindValue(':categoria_id', $category_id, PDO::PARAM_INT);
                    $stmt->bindValue(':titulo', $title, PDO::PARAM_STR);
                    $stmt->bindValue(':conteudo', $content, PDO::PARAM_STR);
                    $stmt->bindValue(':descricao', $content, PDO::PARAM_STR);
                    $stmt->bindValue(':url_imagem', $target_file, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Postagem criada com sucesso',
                            'data' => [
                                'id' => $connection->lastInsertId(),
                                'title' => $title,
                                'content' => $content,
                                'image' => $target_file
                            ]
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Falha na criação da postagem'
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Erro ao fazer o upload da imagem'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Arquivo de imagem não fornecido ou erro no upload'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Campos obrigatórios não fornecidos'
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
        'message' => 'Método de requisição inválido'
    ];
}
header('Content-Type: application/json');
echo json_encode($response);
