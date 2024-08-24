<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas PUT é aceito.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? intval($data->id) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID do registro não fornecido.'
    ]);
    exit;
}

try {
    $select_query = "SELECT * FROM ProdutoDivulgacao WHERE id = :id";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {
        $titulo_breve = isset($data->titulo_breve) ? htmlspecialchars(trim($data->titulo_breve)) : null;
        $detalhes_problema_beneficios = isset($data->detalhes_problema_beneficios) ? htmlspecialchars(trim($data->detalhes_problema_beneficios)) : null;
        $destaque_problemas = isset($data->destaque_problemas) ? htmlspecialchars(trim($data->destaque_problemas)) : null;
        $destaque_beneficio1 = isset($data->destaque_beneficio1) ? htmlspecialchars(trim($data->destaque_beneficio1)) : null;
        $destaque_beneficio2 = isset($data->destaque_beneficio2) ? htmlspecialchars(trim($data->destaque_beneficio2)) : null;
        $destaque_beneficio3 = isset($data->destaque_beneficio3) ? htmlspecialchars(trim($data->destaque_beneficio3)) : null;
        $cta = isset($data->cta) ? htmlspecialchars(trim($data->cta)) : null;
        $imagem_placeholder = isset($data->imagem_placeholder) ? htmlspecialchars(trim($data->imagem_placeholder)) : null;
        $beneficio1 = isset($data->beneficio1) ? htmlspecialchars(trim($data->beneficio1)) : null;
        $problema_beneficio1 = isset($data->problema_beneficio1) ? htmlspecialchars(trim($data->problema_beneficio1)) : null;
        $beneficio2 = isset($data->beneficio2) ? htmlspecialchars(trim($data->beneficio2)) : null;
        $problema_beneficio2 = isset($data->problema_beneficio2) ? htmlspecialchars(trim($data->problema_beneficio2)) : null;
        $beneficio3 = isset($data->beneficio3) ? htmlspecialchars(trim($data->beneficio3)) : null;
        $problema_beneficio3 = isset($data->problema_beneficio3) ? htmlspecialchars(trim($data->problema_beneficio3)) : null;
        $porque_clicar = isset($data->porque_clicar) ? htmlspecialchars(trim($data->porque_clicar)) : null;
        $pergunta1 = isset($data->pergunta1) ? htmlspecialchars(trim($data->pergunta1)) : null;
        $resposta1 = isset($data->resposta1) ? htmlspecialchars(trim($data->resposta1)) : null;
        $pergunta2 = isset($data->pergunta2) ? htmlspecialchars(trim($data->pergunta2)) : null;
        $resposta2 = isset($data->resposta2) ? htmlspecialchars(trim($data->resposta2)) : null;
        $pergunta3 = isset($data->pergunta3) ? htmlspecialchars(trim($data->pergunta3)) : null;
        $resposta3 = isset($data->resposta3) ? htmlspecialchars(trim($data->resposta3)) : null;
        $pergunta4 = isset($data->pergunta4) ? htmlspecialchars(trim($data->pergunta4)) : null;
        $resposta4 = isset($data->resposta4) ? htmlspecialchars(trim($data->resposta4)) : null;
        $pergunta5 = isset($data->pergunta5) ? htmlspecialchars(trim($data->pergunta5)) : null;
        $resposta5 = isset($data->resposta5) ? htmlspecialchars(trim($data->resposta5)) : null;

        $update_query = "UPDATE ProdutoDivulgacao SET 
                            titulo_breve = :titulo_breve, 
                            detalhes_problema_beneficios = :detalhes_problema_beneficios, 
                            destaque_problemas = :destaque_problemas,
                            destaque_beneficio1 = :destaque_beneficio1,
                            destaque_beneficio2 = :destaque_beneficio2,
                            destaque_beneficio3 = :destaque_beneficio3,
                            cta = :cta,
                            imagem_placeholder = :imagem_placeholder,
                            beneficio1 = :beneficio1,
                            problema_beneficio1 = :problema_beneficio1,
                            beneficio2 = :beneficio2,
                            problema_beneficio2 = :problema_beneficio2,
                            beneficio3 = :beneficio3,
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
                            resposta5 = :resposta5
                        WHERE id = :id";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':titulo_breve', $titulo_breve, PDO::PARAM_STR);
        $update_stmt->bindValue(':detalhes_problema_beneficios', $detalhes_problema_beneficios, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_problemas', $destaque_problemas, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_beneficio1', $destaque_beneficio1, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_beneficio2', $destaque_beneficio2, PDO::PARAM_STR);
        $update_stmt->bindValue(':destaque_beneficio3', $destaque_beneficio3, PDO::PARAM_STR);
        $update_stmt->bindValue(':cta', $cta, PDO::PARAM_STR);
        $update_stmt->bindValue(':imagem_placeholder', $imagem_placeholder, PDO::PARAM_STR);
        $update_stmt->bindValue(':beneficio1', $beneficio1, PDO::PARAM_STR);
        $update_stmt->bindValue(':problema_beneficio1', $problema_beneficio1, PDO::PARAM_STR);
        $update_stmt->bindValue(':beneficio2', $beneficio2, PDO::PARAM_STR);
        $update_stmt->bindValue(':problema_beneficio2', $problema_beneficio2, PDO::PARAM_STR);
        $update_stmt->bindValue(':beneficio3', $beneficio3, PDO::PARAM_STR);
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
}
?>
