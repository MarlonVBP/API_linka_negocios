<?php


include '../../cors.php';
include '../../conn.php';


$data = json_decode(file_get_contents('php://input'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($data->token) || !isset($data->new_password)) {
        echo json_encode(["error" => "Token e nova senha são obrigatórios."]);
        exit();
    }

    $token = $data->token;
    $new_password = password_hash($data->new_password, PASSWORD_BCRYPT);


    $query = "SELECT id FROM admin WHERE reset_token = ? AND reset_expires > NOW()";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(1, $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        $query = "UPDATE admin SET senha = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(1, $new_password);
        $stmt->bindValue(2, $token);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Senha redefinida com sucesso!"]);
        } else {
            echo json_encode(["error" => "Erro ao atualizar a senha no banco de dados."]);
        }
    } else {
        echo json_encode(["error" => "Token inválido ou expirado! Solicite uma nova recuperação de senha."]);
    }
} else {
    echo json_encode(["error" => "Método de requisição inválido."]);
}
