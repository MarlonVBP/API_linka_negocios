<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

include_once '../../conn.php';
require_once '../../admin/login/jwtEhValido.php';

$token = $_COOKIE['auth_token'] ?? null;

if (!$token || !jwt_eh_valido($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Sessão expirada ou inválida.']);
    exit;
}

$tokenParts = explode('.', $token);

$payload = json_decode(base64url_decode($tokenParts[1]));
$usuario_id = $payload->ID_USER ?? null;

if (!$usuario_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token inválido: Usuário não identificado.']);
    exit;
}

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags($data ?? ''));
}

if (empty($_POST['titulo_breve']) || empty($_POST['detalhes_problema_beneficios'])) {
    http_response_code(400);
    echo json_encode(["message" => "Dados incompletos. Título e Detalhes são obrigatórios."]);
    exit;
}

try {

    $titulo_breve = sanitize_input($_POST['titulo_breve']);
    $detalhes_problema_beneficios = sanitize_input($_POST['detalhes_problema_beneficios']);

    $destaque_problemas = sanitize_input($_POST['destaque_problemas'] ?? '');
    $destaque_beneficio1 = sanitize_input($_POST['destaque_beneficio1'] ?? '');
    $destaque_beneficio2 = sanitize_input($_POST['destaque_beneficio2'] ?? '');
    $destaque_beneficio3 = sanitize_input($_POST['destaque_beneficio3'] ?? '');
    $cta = sanitize_input($_POST['cta'] ?? '');
    $imagem_placeholder = sanitize_input($_POST['imagem_placeholder'] ?? '');
    $problema_beneficio1 = sanitize_input($_POST['problema_beneficio1'] ?? '');
    $problema_beneficio2 = sanitize_input($_POST['problema_beneficio2'] ?? '');
    $problema_beneficio3 = sanitize_input($_POST['problema_beneficio3'] ?? '');
    $porque_clicar = sanitize_input($_POST['porque_clicar'] ?? '');

    $pergunta1 = sanitize_input($_POST['pergunta1'] ?? '');
    $resposta1 = sanitize_input($_POST['resposta1'] ?? '');
    $pergunta2 = sanitize_input($_POST['pergunta2'] ?? '');
    $resposta2 = sanitize_input($_POST['resposta2'] ?? '');
    $pergunta3 = sanitize_input($_POST['pergunta3'] ?? '');
    $resposta3 = sanitize_input($_POST['resposta3'] ?? '');
    $pergunta4 = sanitize_input($_POST['pergunta4'] ?? '');
    $resposta4 = sanitize_input($_POST['resposta4'] ?? '');
    $pergunta5 = sanitize_input($_POST['pergunta5'] ?? '');
    $resposta5 = sanitize_input($_POST['resposta5'] ?? '');

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
                    throw new Exception("Falha ao criar diretório de upload. Verifique as permissões.");
                }
            }

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $arquivo_zip_path = 'uploads/produtos_zips/' . $newFileName;
            } else {
                throw new Exception("Erro ao mover o arquivo para o destino final.");
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Apenas arquivos .zip são permitidos."]);
            exit;
        }
    }

    $query = "INSERT INTO ProdutoDivulgacao SET 
        titulo_breve = :titulo_breve,
        detalhes_problema_beneficios = :detalhes_problema_beneficios,
        destaque_problemas = :destaque_problemas,
        destaque_beneficio1 = :destaque_beneficio1,
        destaque_beneficio2 = :destaque_beneficio2,
        destaque_beneficio3 = :destaque_beneficio3,
        cta = :cta,
        imagem_placeholder = :imagem_placeholder,
        arquivo_zip = :arquivo_zip,
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

    $stmt = $connection->prepare($query);

    $stmt->bindParam(':titulo_breve', $titulo_breve);
    $stmt->bindParam(':detalhes_problema_beneficios', $detalhes_problema_beneficios);
    $stmt->bindParam(':destaque_problemas', $destaque_problemas);
    $stmt->bindParam(':destaque_beneficio1', $destaque_beneficio1);
    $stmt->bindParam(':destaque_beneficio2', $destaque_beneficio2);
    $stmt->bindParam(':destaque_beneficio3', $destaque_beneficio3);
    $stmt->bindParam(':cta', $cta);
    $stmt->bindParam(':imagem_placeholder', $imagem_placeholder);
    $stmt->bindParam(':arquivo_zip', $arquivo_zip_path);
    $stmt->bindParam(':problema_beneficio1', $problema_beneficio1);
    $stmt->bindParam(':problema_beneficio2', $problema_beneficio2);
    $stmt->bindParam(':problema_beneficio3', $problema_beneficio3);
    $stmt->bindParam(':porque_clicar', $porque_clicar);
    $stmt->bindParam(':pergunta1', $pergunta1);
    $stmt->bindParam(':resposta1', $resposta1);
    $stmt->bindParam(':pergunta2', $pergunta2);
    $stmt->bindParam(':resposta2', $resposta2);
    $stmt->bindParam(':pergunta3', $pergunta3);
    $stmt->bindParam(':resposta3', $resposta3);
    $stmt->bindParam(':pergunta4', $pergunta4);
    $stmt->bindParam(':resposta4', $resposta4);
    $stmt->bindParam(':pergunta5', $pergunta5);
    $stmt->bindParam(':resposta5', $resposta5);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Produto criado com sucesso.", "id" => $connection->lastInsertId()]);
    } else {
        throw new Exception("Erro ao executar a query: " . implode(" | ", $stmt->errorInfo()));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Erro interno no servidor.",
        "error" => $e->getMessage()
    ]);
}
