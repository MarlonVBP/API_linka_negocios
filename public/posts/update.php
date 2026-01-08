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

    function processarImagensDoConteudo($htmlContent)
    {
        if (empty($htmlContent)) return $htmlContent;

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        $alterou = false;

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
                $data = substr($src, strpos($src, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                    continue;
                }

                $data = base64_decode($data);
                if ($data === false) {
                    continue;
                }

                $fileName = uniqid() . '_' . time() . '.' . $type;
                $diretorioDestino = 'imagens/';

                if (!is_dir($diretorioDestino)) {
                    mkdir($diretorioDestino, 0755, true);
                }

                file_put_contents($diretorioDestino . $fileName, $data);

                $webUrl = 'https://linkanegocios.com.br/api/public/posts/imagens/' . $fileName;

                $img->setAttribute('src', $webUrl);
                $img->setAttribute('class', 'img-fluid post-image');
                $alterou = true;
            }
        }

        if ($alterou) {
            return $dom->saveHTML();
        }

        return $htmlContent;
    }

    $tokenParts = explode('.', $token);
    $payload = json_decode(base64url_decode($tokenParts[1]));
    $usuario_id = $payload->ID_USER ?? null;

    if (!$usuario_id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token inválido: Usuário não identificado.']);
        exit;
    }

    if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['content']) && isset($_POST['category'])) {

        $post_id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $contentRaw = trim($_POST['content']);
        $content = processarImagensDoConteudo($contentRaw);
        $category_id = intval($_POST['category']);
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
                echo json_encode(['success' => false, 'message' => 'Arquivo de imagem inválido.']);
                exit;
            }
        }

        $query = "UPDATE postagens SET categoria_id = :categoria_id, titulo = :titulo, conteudo = :conteudo, descricao = :descricao";
        if ($image_updated) {
            $query .= ", url_imagem = :url_imagem";
        }
        $query .= " WHERE id = :post_id";

        try {
            $stmt = $connection->prepare($query);
            $stmt->bindValue(':categoria_id', $category_id, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $title, PDO::PARAM_STR);
            $stmt->bindValue(':conteudo', $content, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $description, PDO::PARAM_STR);
            $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);

            if ($image_updated) {
                $stmt->bindValue(':url_imagem', $target_file, PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
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
