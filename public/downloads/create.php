<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Permitir apenas requisições POST
if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas POST é permitido.',
    ]);
    exit;
}

// Obter e processar os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Sanitizar os dados recebidos
    $titulo_breve = htmlspecialchars(trim($data->titulo_breve));
    $detalhes_problema_beneficios = htmlspecialchars(trim($data->detalhes_problema_beneficios));
    $destaque_problemas = htmlspecialchars(trim($data->destaque_problemas));
    $destaque_beneficio1 = htmlspecialchars(trim($data->destaque_beneficio1));
    $destaque_beneficio2 = htmlspecialchars(trim($data->destaque_beneficio2));
    $destaque_beneficio3 = htmlspecialchars(trim($data->destaque_beneficio3));
    $cta = htmlspecialchars(trim($data->cta));
    $imagem_placeholder = htmlspecialchars(trim($data->imagem_placeholder));
    $beneficio1 = htmlspecialchars(trim($data->beneficio1));
    $problema_beneficio1 = htmlspecialchars(trim($data->problema_beneficio1));
    $beneficio2 = htmlspecialchars(trim($data->beneficio2));
    $problema_beneficio2 = htmlspecialchars(trim($data->problema_beneficio2));
    $beneficio3 = htmlspecialchars(trim($data->beneficio3));
    $problema_beneficio3 = htmlspecialchars(trim($data->problema_beneficio3));
    $porque_clicar = htmlspecialchars(trim($data->porque_clicar));

    // Preparar a consulta SQL para inserção
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
            porque_clicar
            ) 
            VALUES (
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
            :porque_clicar
            )";

    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
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

    // Executar a consulta e verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        http_response_code(201); // Criado
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    // Se a inserção falhar, retornar uma mensagem de erro
    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
    ]);
    exit;

} catch (PDOException $e) {
    // Definir código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
