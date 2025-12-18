<?php
include '../../cors.php';
include '../../conn.php';
require_once '../../admin/login/jwtEhValido.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    header('Content-Type: application/json');

    $token = $_COOKIE['auth_token'] ?? null;

    if (!$token || !jwt_eh_valido($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Acesso negado. Sessão expirada ou inválida.']);
        exit;
    }

    $tokenParts = explode('.', $token);
    $payload = json_decode(base64url_decode($tokenParts[1]));
    $usuario_id = $payload->ID_USER ?? null; // Certifique-se que no seu criarJwt.php a chave é 'ID_USER' mesmo

    if (!$usuario_id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token inválido: Usuário não identificado.']);
        exit;
    }

    if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['content']) && isset($_POST['category_id'])) {

        $post_id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $category_id = intval($_POST['category_id']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

        $image_updated = false;
        $target_file = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {

            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileNameOriginal = $_FILES['image']['name'];

            $fileNameCmps = explode(".", $fileNameOriginal);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileMimeType = $finfo->file($fileTmpPath);
            $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');

            if (in_array($fileExtension, $allowedfileExtensions) && in_array($fileMimeType, $allowedMimeTypes)) {

                $newFileName = md5(time() . $fileNameOriginal) . '.' . $fileExtension;
                $uploadFileDir = 'imagens/';
                $target_file = $uploadFileDir . $newFileName;

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                if (move_uploaded_file($fileTmpPath, $target_file)) {
                    $image_updated = true;
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erro ao salvar a imagem no servidor.']);
                    exit;
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Arquivo de imagem inválido (Formato ou extensão não permitidos).']);
                exit;
            }
        }
        $query = "UPDATE postagens SET categoria_id = :categoria_id, titulo = :titulo, conteudo = :conteudo, descricao = :descricao";
        if ($image_updated) {
            $query .= ", url_imagem = :url_imagem";
        }
        $query .= " WHERE id = :post_id AND usuario_id = :usuario_id";

        try {
            $stmt = $connection->prepare($query);
            $stmt->bindValue(':categoria_id', $category_id, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $title, PDO::PARAM_STR);
            $stmt->bindValue(':conteudo', $content, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $description, PDO::PARAM_STR);
            $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);

            if ($image_updated) {
                $stmt->bindValue(':url_imagem', $target_file, PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $response = [
                        'success' => true,
                        'message' => 'Postagem atualizada com sucesso',
                        'data' => [
                            'id' => $post_id,
                            'title' => $title,
                            'image' => $image_updated ? $target_file : null
                        ]
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Nenhuma alteração feita ou você não tem permissão para editar este post.'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Falha na execução do banco de dados'
                ];
            }
        } catch (Exception $e) {
            http_response_code(500);
            $response = [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
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
        'message' => 'Método de requisição inválido'
    ];
}

echo json_encode($response);
