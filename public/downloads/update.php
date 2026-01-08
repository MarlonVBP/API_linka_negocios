<?php
include '../../cors.php';
include '../../conn.php';
require_once '../../admin/login/jwtEhValido.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Para upload de arquivos, utilize POST.',
    ]);
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
if (!isset($payload->ID_USER)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token inválido.']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID do registro não fornecido.'
    ]);
    exit;
}

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags($data ?? ''));
}

try {
    $select_query = "SELECT * FROM ProdutoDivulgacao WHERE id = :id";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {

        $titulo_breve = sanitize_input($_POST['titulo_breve'] ?? null);
        $detalhes_problema_beneficios = sanitize_input($_POST['detalhes_problema_beneficios'] ?? null);
        $destaque_problemas = sanitize_input($_POST['destaque_problemas'] ?? null);
        $destaque_beneficio1 = sanitize_input($_POST['destaque_beneficio1'] ?? null);
        $destaque_beneficio2 = sanitize_input($_POST['destaque_beneficio2'] ?? null);
        $destaque_beneficio3 = sanitize_input($_POST['destaque_beneficio3'] ?? null);
        $cta = sanitize_input($_POST['cta'] ?? null);
        $imagem_placeholder = sanitize_input($_POST['imagem_placeholder'] ?? null);
        $problema_beneficio1 = sanitize_input($_POST['problema_beneficio1'] ?? null);
        $problema_beneficio2 = sanitize_input($_POST['problema_beneficio2'] ?? null);
        $problema_beneficio3 = sanitize_input($_POST['problema_beneficio3'] ?? null);
        $porque_clicar = sanitize_input($_POST['porque_clicar'] ?? null);
        $pergunta1 = sanitize_input($_POST['pergunta1'] ?? null);
        $resposta1 = sanitize_input($_POST['resposta1'] ?? null);
        $pergunta2 = sanitize_input($_POST['pergunta2'] ?? null);
        $resposta2 = sanitize_input($_POST['resposta2'] ?? null);
        $pergunta3 = sanitize_input($_POST['pergunta3'] ?? null);
        $resposta3 = sanitize_input($_POST['resposta3'] ?? null);
        $pergunta4 = sanitize_input($_POST['pergunta4'] ?? null);
        $resposta4 = sanitize_input($_POST['resposta4'] ?? null);
        $pergunta5 = sanitize_input($_POST['pergunta5'] ?? null);
        $resposta5 = sanitize_input($_POST['resposta5'] ?? null);

        $arquivo_zip_updated = false;
        $arquivo_zip_path = null;

        if (isset($_FILES['arquivo_zip']) && $_FILES['arquivo_zip']['error'] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['arquivo_zip']['name'];
            $fileTmpPath = $_FILES['arquivo_zip']['tmp_name'];

            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            if ($fileExtension === 'zip') {
                $uploadFileDir = '../../uploads/produtos_zips/';

                if (!is_dir($uploadFileDir)) {
                    if (!mkdir($uploadFileDir, 0777, true)) {
                        throw new Exception("Falha ao criar diretório de upload.");
                    }
                }

                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $arquivo_zip_path = 'uploads/produtos_zips/' . $newFileName;
                    $arquivo_zip_updated = true;
                } else {
                    throw new Exception("Erro ao mover o arquivo para o destino final.");
                }
            } else {
                http_response_code(400);
                echo json_encode(["success" => 0, "message" => "Apenas arquivos .zip são permitidos."]);
                exit;
            }
        }

        $update_query = "UPDATE ProdutoDivulgacao SET 
                            titulo_breve = :titulo_breve, 
                            detalhes_problema_beneficios = :detalhes_problema_beneficios, 
                            destaque_problemas = :destaque_problemas,
                            destaque_beneficio1 = :destaque_beneficio1,
                            destaque_beneficio2 = :destaque_beneficio2,
                            destaque_beneficio3 = :destaque_beneficio3,
                            cta = :cta,
                            imagem_placeholder = :imagem_placeholder,
                            problema_beneficio1 = :problema_beneficio1,
                            problema_beneficio2 = :problema_beneficio2,
                            problema_beneficio3 = :problema_beneficio3,
                            porque_clicar = :porque_clicar,
                            pergunta1 = :pergunta1,
                            resposta1 = :resposta1,
                            pergunta2 = :pergunta2,
                            resposta2 = :resposta2,
                            pergunta3 = :pergunta3,
                            resposta3 = :resposta3,
                            pergunta4 = :pergunta4,
                            resposta4 = :resposta4,
                            pergunta5 = :pergunta5,
                            resposta5 = :resposta5";

        if ($arquivo_zip_updated) {
            $update_query .= ", arquivo_zip = :arquivo_zip";
        }

        $update_query .= " WHERE id = :id";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':titulo_breve', $titulo_breve, PDO::PARAM_STR);
        $update_stmt->bindValue(':detalhes_problema_beneficios', $detalhes_problema_beneficios, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_problemas', $destaque_problemas, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_beneficio1', $destaque_beneficio1, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_beneficio2', $destaque_beneficio2, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_beneficio3', $destaque_beneficio3, PDO::PARAM_STR);
        $update_stmt->bindValue(':cta', $cta, PDO::PARAM_STR);
        $update_stmt->bindValue(':imagem_placeholder', $imagem_placeholder, PDO::PARAM_STR);
        $update_stmt->bindValue(':problema_beneficio1', $problema_beneficio1, PDO::PARAM_STR);
        $update_stmt->bindValue(':problema_beneficio2', $problema_beneficio2, PDO::PARAM_STR);
        $update_stmt->bindValue(':problema_beneficio3', $problema_beneficio3, PDO::PARAM_STR);
        $update_stmt->bindValue(':porque_clicar', $porque_clicar, PDO::PARAM_STR);
        $update_stmt->bindValue(':pergunta1', $pergunta1, PDO::PARAM_STR);
        $update_stmt->bindValue(':resposta1', $resposta1, PDO::PARAM_STR);
        $update_stmt->bindValue(':pergunta2', $pergunta2, PDO::PARAM_STR);
        $update_stmt->bindValue(':resposta2', $resposta2, PDO::PARAM_STR);
        $update_stmt->bindValue(':pergunta3', $pergunta3, PDO::PARAM_STR);
        $update_stmt->bindValue(':resposta3', $resposta3, PDO::PARAM_STR);
        $update_stmt->bindValue(':pergunta4', $pergunta4, PDO::PARAM_STR);
        $update_stmt->bindValue(':resposta4', $resposta4, PDO::PARAM_STR);
        $update_stmt->bindValue(':pergunta5', $pergunta5, PDO::PARAM_STR);
        $update_stmt->bindValue(':resposta5', $resposta5, PDO::PARAM_STR);
        $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($arquivo_zip_updated) {
            $update_stmt->bindValue(':arquivo_zip', $arquivo_zip_path, PDO::PARAM_STR);
        }

        if ($update_stmt->execute()) {
            http_response_code(200);
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
