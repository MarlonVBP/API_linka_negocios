<?php
// request_password_reset.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
include '../../cors.php';
include '../../conn.php';

$data = json_decode(file_get_contents('php://input'));
$email = $data->email;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validação do e-mail
    if (!isset($email) || empty($email)) {
        echo json_encode(["error" => "O campo de e-mail é obrigatório."]);
        exit();
    }


    // Verificar se o email existe no banco de dados
    $query = "SELECT id FROM admin WHERE email = :email";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':email', $email); // Correção na ligação dos parâmetros
    $stmt->execute();

    if ($stmt->rowCount() > 0) { // Verificando se o email foi encontrado
        // Gerar um token de redefinição
        $token = bin2hex(random_bytes(50));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // O token expira em 1 hora

        // Armazenar o token e o prazo de validade no banco de dados
        $updateQuery = "UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $updateStmt = $connection->prepare($updateQuery);
        $updateStmt->bindValue(1, $token);
        $updateStmt->bindValue(2, $expiry);
        $updateStmt->bindValue(3, $email);
        $updateStmt->execute();

        // Enviar o link de redefinição por e-mail usando PHPMailer
        $reset_link = "https://linkaNegocios.digital/resetar-senha/" . $token;

        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // ou outro servidor
            $mail->SMTPAuth = true;
            $mail->Username = 'gestaodeferramentaspi@gmail.com'; // seu e-mail
            $mail->Password = 'gestaodeferramentas'; // sua senha
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configurações do e-mail
            $mail->setFrom('no-reply@seuapp.com', 'Seu App');
            $mail->addAddress($email); // Adicionar o destinatário

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Redefinir sua senha';
            $mail->Body = "Clique no link para redefinir sua senha: <a href='$reset_link'>$reset_link</a>";
            $mail->AltBody = "Clique no link para redefinir sua senha: $reset_link";

            $mail->send();
            echo json_encode([
                "message" => "Email de redefinição enviado!",
                "token" => $token
            ]);
        } catch (Exception $e) {
            echo json_encode(["error" => "Erro ao enviar o e-mail: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(["error" => "Usuário não encontrado!"]);
    }
}
