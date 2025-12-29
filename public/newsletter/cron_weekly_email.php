<?php

set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../cors.php';
include '../../conn.php';
require '../../vendor/autoload.php';

try {

    $queryPosts = "SELECT id, titulo, descricao, url_imagem, criado_em 
                   FROM postagens 
                   WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                   ORDER BY criado_em DESC";
    $stmtPosts = $connection->prepare($queryPosts);
    $stmtPosts->execute();
    $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);


    if (count($posts) === 0) {
        echo "Nenhum post novo nesta semana.";
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao buscar posts: " . $e->getMessage());
}

$emailContent = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #b22828; text-align: center;'>Novidades da Semana - Linka Negócios</h2>
        <p>Olá! Confira o que publicamos recentemente:</p>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
";

foreach ($posts as $post) {

    $linkPost = "https://www.linkanegocios.com.br/read-more/" . $post['id'];

    $imgSrc = "https://linkanegocios.com.br/api/public/posts/" . $post['url_imagem'];

    $emailContent .= "
        <div style='margin-bottom: 30px;'>
            <a href='$linkPost' style='text-decoration: none; color: inherit;'>
                <img src='$imgSrc' alt='{$post['titulo']}' style='width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px;'>
                <h3 style='color: #333; margin-top: 10px;'>{$post['titulo']}</h3>
            </a>
            <p style='color: #666; font-size: 14px;'>" . substr($post['descricao'], 0, 150) . "...</p>
            <a href='$linkPost' style='background-color: #b22828; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-size: 14px;'>Ler Mais</a>
        </div>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
    ";
}

$emailContent .= "
        <p style='text-align: center; font-size: 12px; color: #999;'>
            Se você não deseja mais receber estes e-mails, entre em contato.
        </p>
    </div>
";

try {
    $querySubs = "SELECT email FROM newsletter WHERE ativo = 1";
    $stmtSubs = $connection->prepare($querySubs);
    $stmtSubs->execute();
    $subscribers = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar assinantes: " . $e->getMessage());
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7efcfa001@smtp-brevo.com';
    $mail->Password   = 'pzqFRB725kavVNSm';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('marlonvicctor13@gmail.com', 'Linka Negócios Newsletter');
    $mail->isHTML(true);
    $mail->Subject = 'Confira as novidades da semana na Linka Negócios!';
    $mail->Body    = $emailContent;
    $mail->AltBody = 'Temos novos posts no site! Acesse www.linkanegocios.com.br para conferir.';

    $enviados = 0;
    foreach ($subscribers as $sub) {
        try {
            $mail->addAddress($sub['email']);

            $mail->send();

            $enviados++;

            $mail->clearAddresses();
        } catch (Exception $e) {
            echo "Erro ao enviar para " . $sub['email'] . ": {$mail->ErrorInfo}<br>";
            $mail->clearAddresses();
            continue;
        }
    }

    echo "Newsletter enviada com sucesso para $enviados assinantes.";
} catch (Exception $e) {
    echo "Erro geral no envio: {$mail->ErrorInfo}";
}
