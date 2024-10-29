<?php
include '../../cors.php';  
include '../../conn.php';  

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas POST e permitido.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'JSON inválido.',
    ]);
    exit;
}

$titulo_breve = htmlspecialchars(trim($data->titulo_breve ?? ''));
$detalhes_problema_beneficios = htmlspecialchars(trim($data->detalhes_problema_beneficios ?? ''));
$destaque_problemas = htmlspecialchars(trim($data->destaque_problemas ?? ''));
$destaque_beneficio1 = htmlspecialchars(trim($data->destaque_beneficio1 ?? ''));
$destaque_beneficio2 = htmlspecialchars(trim($data->destaque_beneficio2 ?? ''));
$destaque_beneficio3 = htmlspecialchars(trim($data->destaque_beneficio3 ?? ''));
$cta = htmlspecialchars(trim($data->cta ?? ''));
$imagem_placeholder = htmlspecialchars(trim($data->imagem_placeholder ?? ''));
$beneficio1 = htmlspecialchars(trim($data->beneficio1 ?? ''));
$problema_beneficio1 = htmlspecialchars(trim($data->problema_beneficio1 ?? ''));
$beneficio2 = htmlspecialchars(trim($data->beneficio2 ?? ''));
$problema_beneficio2 = htmlspecialchars(trim($data->problema_beneficio2 ?? ''));
$beneficio3 = htmlspecialchars(trim($data->beneficio3 ?? ''));
$problema_beneficio3 = htmlspecialchars(trim($data->problema_beneficio3 ?? ''));
$porque_clicar = htmlspecialchars(trim($data->porque_clicar ?? ''));

$pergunta1 = htmlspecialchars(trim($data->pergunta1 ?? ''));
$resposta1 = htmlspecialchars(trim($data->resposta1 ?? ''));
$pergunta2 = htmlspecialchars(trim($data->pergunta2 ?? ''));
$resposta2 = htmlspecialchars(trim($data->resposta2 ?? ''));
$pergunta3 = htmlspecialchars(trim($data->pergunta3 ?? ''));
$resposta3 = htmlspecialchars(trim($data->resposta3 ?? ''));
$pergunta4 = htmlspecialchars(trim($data->pergunta4 ?? ''));
$resposta4 = htmlspecialchars(trim($data->resposta4 ?? ''));
$pergunta5 = htmlspecialchars(trim($data->pergunta5 ?? ''));
$resposta5 = htmlspecialchars(trim($data->resposta5 ?? ''));

$query = "INSERT INTO `ProdutoDivulgacao` (
    titulo_breve,
    detalhes_problema_beneficios,
    destaque_problemas,
    destaque_beneficio1,
    destaque_beneficio2,
    destaque_beneficio3,
    cta,
    imagem_placeholder,
    beneficio1,
    problema_beneficio1,
    beneficio2,
    problema_beneficio2,
    beneficio3,
    problema_beneficio3,
    porque_clicar,
    pergunta1,
    resposta1,
    pergunta2,
    resposta2,
    pergunta3,
    resposta3,
    pergunta4,
    resposta4,
    pergunta5,
    resposta5
) VALUES (
    :titulo_breve,
    :detalhes_problema_beneficios,
    :destaque_problemas,
    :destaque_beneficio1,
    :destaque_beneficio2,
    :destaque_beneficio3,
    :cta,
    :imagem_placeholder,
    :beneficio1,
    :problema_beneficio1,
    :beneficio2,
    :problema_beneficio2,
    :beneficio3,
    :problema_beneficio3,
    :porque_clicar,
    :pergunta1,
    :resposta1,
    :pergunta2,
    :resposta2,
    :pergunta3,
    :resposta3,
    :pergunta4,
    :resposta4,
    :pergunta5,
    :resposta5
)";

try {
    $stmt = $connection->prepare($query);

    $stmt->bindValue(':titulo_breve', $titulo_breve, PDO::PARAM_STR);
    $stmt->bindValue(':detalhes_problema_beneficios', $detalhes_problema_beneficios, PDO::PARAM_STR);
    $stmt->bindValue(':destaque_problemas', $destaque_problemas, PDO::PARAM_STR);
    $stmt->bindValue(':destaque_beneficio1', $destaque_beneficio1, PDO::PARAM_STR);
    $stmt->bindValue(':destaque_beneficio2', $destaque_beneficio2, PDO::PARAM_STR);
    $stmt->bindValue(':destaque_beneficio3', $destaque_beneficio3, PDO::PARAM_STR);
    $stmt->bindValue(':cta', $cta, PDO::PARAM_STR);
    $stmt->bindValue(':imagem_placeholder', $imagem_placeholder, PDO::PARAM_STR);
    $stmt->bindValue(':beneficio1', $beneficio1, PDO::PARAM_STR);
    $stmt->bindValue(':problema_beneficio1', $problema_beneficio1, PDO::PARAM_STR);
    $stmt->bindValue(':beneficio2', $beneficio2, PDO::PARAM_STR);
    $stmt->bindValue(':problema_beneficio2', $problema_beneficio2, PDO::PARAM_STR);
    $stmt->bindValue(':beneficio3', $beneficio3, PDO::PARAM_STR);
    $stmt->bindValue(':problema_beneficio3', $problema_beneficio3, PDO::PARAM_STR);
    $stmt->bindValue(':porque_clicar', $porque_clicar, PDO::PARAM_STR);
    $stmt->bindValue(':pergunta1', $pergunta1, PDO::PARAM_STR);
    $stmt->bindValue(':resposta1', $resposta1, PDO::PARAM_STR);
    $stmt->bindValue(':pergunta2', $pergunta2, PDO::PARAM_STR);
    $stmt->bindValue(':resposta2', $resposta2, PDO::PARAM_STR);
    $stmt->bindValue(':pergunta3', $pergunta3, PDO::PARAM_STR);
    $stmt->bindValue(':resposta3', $resposta3, PDO::PARAM_STR);
    $stmt->bindValue(':pergunta4', $pergunta4, PDO::PARAM_STR);
    $stmt->bindValue(':resposta4', $resposta4, PDO::PARAM_STR);
    $stmt->bindValue(':pergunta5', $pergunta5, PDO::PARAM_STR);
    $stmt->bindValue(':resposta5', $resposta5, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
