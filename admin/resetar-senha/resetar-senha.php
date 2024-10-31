<?php

include '../../cors.php';
include '../../conn.php';

$data = json_decode(file_get_contents('php://input'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $data->token;
    $new_password = password_hash($data->new_password, PASSWORD_BCRYPT);

    $query = "SELECT id, reset_expires FROM admin WHERE reset_token = ? AND reset_expires > NOW()";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(1, $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Redefinir a senha
        $query = "UPDATE admin SET senha = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(1, $new_password);
        $stmt->bindValue(2, $token);
        $stmt->execute();

        echo json_encode(["message" => "Senha redefinida com sucesso!"]);
    } else {
        echo json_encode(["error" => "Token inválido ou expirado!"]);
    }
}
