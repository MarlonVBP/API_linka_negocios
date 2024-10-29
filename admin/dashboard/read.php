<?php
include '../../cors.php';
include '../../conn.php';

// Verificar se o método de requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas GET e aceito.',
    ]);
    exit;
}

// Obter os parâmetros da consulta
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : null;
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : null;

try {
    // Preparar e executar a consulta SQL para filtro por mês
    if ($mes !== null) {
        $select = "
            SELECT DAY(criado_em) AS dia, SUM(dados) AS total_dados
            FROM dashboard
            WHERE MONTH(criado_em) = :mes AND YEAR(criado_em) = YEAR(CURDATE())
            GROUP BY dia
        ";
        $stmt = $connection->prepare($select);
        $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
    }
    // Preparar e executar a consulta SQL para filtro por ano
    elseif ($ano !== null) {
        $select = "
            SELECT mes, SUM(dados) AS total_dados
            FROM dashboard
            WHERE YEAR(criado_em) = :ano
            GROUP BY mes
        ";
        $stmt = $connection->prepare($select);
        $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
    } else {
        // Se nenhum filtro for aplicado, retorne todos os dados
        $select = "SELECT mes, dados FROM dashboard ORDER BY id";
        $stmt = $connection->prepare($select);
    }

    $stmt->execute();

    // Verificar se há registros
    if ($stmt->rowCount() > 0) {
        $vetor_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => 1,
            'response' => $vetor_dados
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado.',
            'response' => [],
        ]);
    }
} catch (PDOException $e) {
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
    exit;
}
