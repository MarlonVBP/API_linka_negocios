<?php
// request_password_reset.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
include '../../cors.php';
include '../../conn.php';

// Decodifica os dados JSON de entrada
$data = json_decode(file_get_contents('php://input'));
$email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validação do e-mail
    if (empty($email)) {
        echo json_encode(["error" => "O campo de e-mail é obrigatório."]);
        exit();
    }

    // Verifica se o e-mail é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "E-mail inválido."]);
        exit();
    }

    // Verificar se o email existe no banco de dados
    $query = "SELECT id FROM admin WHERE email = :email";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) { // Verificando se o email foi encontrado
        // Gera um token de redefinição seguro
        $token = bin2hex(random_bytes(50));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // O token expira em 1 hora

        // Armazenar o token e o prazo de validade no banco de dados
        $updateQuery = "UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $updateStmt = $connection->prepare($updateQuery);
        $updateStmt->execute([$token, $expiry, $email]);

        // Envia o link de redefinição por e-mail usando PHPMailer
        $reset_link = "https://linkaNegocios.digital/resetar-senha/" . $token;
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'seu_email@gmail.com'; // Substitua pelo seu e-mail
            $mail->Password = 'sua_senha_aqui'; // Substitua pela sua senha (ou idealmente use uma senha de aplicativo)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configurações SSL - Temporário para resolver problemas de conexão
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Configurações do e-mail
            $mail->setFrom('no-reply@seuapp.com', 'Seu App');
            $mail->addAddress($email); // Destinatário

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
} else {
    echo json_encode(["error" => "Método de requisição inválido."]);
}
