<?php

include '../../cors.php';
include '../../conn.php';

require_once '../../admin/login/jwtEhValido.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$token = $_COOKIE['auth_token'] ?? null;

if (!$token || !jwt_eh_valido($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Sessão expirada ou inválida.']);
    exit;
}

$tokenParts = explode('.', $token);
$payload = json_decode(base64url_decode($tokenParts[1]));
$usuario_id = $payload->ID_USER ?? null;

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

if (!$usuario_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token inválido: Usuário não identificado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (isset($_POST['title']) && isset($_POST['content']) && isset($_POST['category'])) {

    $title = trim($_POST['title']);
    $contentRaw = trim($_POST['content']);
    $content = processarImagensDoConteudo($contentRaw);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category_id = intval($_POST['category']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileNameOriginal = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];

        $fileNameCmps = explode(".", $fileNameOriginal);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileMimeType = $finfo->file($fileTmpPath);
        $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');

        if (in_array($fileExtension, $allowedfileExtensions) && in_array($fileMimeType, $allowedMimeTypes)) {

            $newFileName = md5(time() . $fileNameOriginal) . '.' . $fileExtension;
            $uploadFileDir = 'imagens/';
            $dest_path = $uploadFileDir . $newFileName;

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            if (move_uploaded_file($fileTmpPath, $dest_path)) {

                try {
                    $query = "INSERT INTO postagens (usuario_id, categoria_id, titulo, conteudo, descricao, url_imagem) VALUES (:usuario_id, :categoria_id, :titulo, :conteudo, :descricao, :url_imagem)";
                    $stmt = $connection->prepare($query);

                    $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
                    $stmt->bindValue(':categoria_id', $category_id, PDO::PARAM_INT);
                    $stmt->bindValue(':titulo', $title, PDO::PARAM_STR);
                    $stmt->bindValue(':conteudo', $content, PDO::PARAM_STR);
                    $stmt->bindValue(':descricao', $description, PDO::PARAM_STR);
                    $stmt->bindValue(':url_imagem', $dest_path, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Postagem criada com sucesso',
                            'data' => [
                                'id' => $connection->lastInsertId(),
                                'title' => $title,
                                'image' => $dest_path
                            ]
                        ]);
                    } else {
                        throw new Exception("Erro ao inserir no banco.");
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao mover o arquivo para o diretório de imagens.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipo de arquivo inválido. Apenas imagens (JPG, PNG, WEBP) são permitidas.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Imagem obrigatória ou erro no envio.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos (Título, Conteúdo e Categoria são obrigatórios).']);
}
