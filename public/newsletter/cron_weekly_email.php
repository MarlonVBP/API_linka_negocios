<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acesso Negado: Este script so pode ser executado via Cron Job (Terminal).");
}

set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../cors.php';
include '../../conn.php';
require '../../vendor/autoload.php';

try {
    $queryPosts = "SELECT id, titulo, descricao, url_imagem, views, criado_em 
                   FROM postagens 
                   WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   ORDER BY views DESC, criado_em DESC
                   LIMIT 5";

    $stmtPosts = $connection->prepare($queryPosts);
    $stmtPosts->execute();
    $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

    if (count($posts) === 0) {
        echo "Nenhum post encontrado nos últimos 30 dias.";
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao buscar posts: " . $e->getMessage());
}

$tituloTop1 = (strlen($posts[0]['titulo']) > 40) ? substr($posts[0]['titulo'], 0, 40) . '...' : $posts[0]['titulo'];
$mesAtual = date('m/Y');
$assuntoEmail = "Destaques de $mesAtual: $tituloTop1 e muito mais";
$anoAtual = date('Y');
$fontFamily = "'Helvetica Neue', Helvetica, Arial, sans-serif";
$corPrimaria = "#b22828";
$corFundo = "#f4f4f4";
$corCard = "#ffffff";
$corTexto = "#212121";
$corCinza = "#555555";
$corFooter = "#333333";
$emailTemplate = "
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$assuntoEmail</title>
    <style>
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content-padding { padding: 20px !important; }
        }
    </style>
</head>
<body style='margin: 0; padding: 0; background-color: $corFundo; font-family: $fontFamily;'>

    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: $corFundo;'>
        <tr>
            <td align='center' style='padding: 40px 0;'>
                
                <table border='0' cellpadding='0' cellspacing='0' width='600' class='container' style='background-color: $corCard; width: 600px; max-width: 100%; margin: 0 auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1);'>
                    
                    <tr>
                        <td align='center' style='background-color: $corPrimaria; padding: 30px 20px;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 1px; text-transform: uppercase; font-weight: 700;'>
                                Linka Negócios
                            </h1>
                            <p style='color: #ffffff; margin: 5px 0 0 0; font-size: 13px; opacity: 0.9; text-transform: uppercase;'>
                                Edição Mensal • $mesAtual
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td class='content-padding' style='padding: 40px 40px 20px 40px; text-align: left;'>
                            <h2 style='color: $corTexto; margin: 0 0 15px 0; font-size: 20px; font-weight: bold;'>
                                Olá! 👋
                            </h2>
                            <p style='color: $corCinza; margin: 0; font-size: 16px; line-height: 1.6;'>
                                Aqui estão os assuntos que movimentaram nossa comunidade este mês. Selecionamos os <strong>5 artigos mais lidos</strong> para você.
                            </p>
                        </td>
                    </tr>
";

$rank = 1;
foreach ($posts as $post) {
    $linkPost = "https://www.linkanegocios.com.br/read-more/" . $post['id'];
    $imgSrc = $post['url_imagem'];
    if (strpos($imgSrc, 'http') === false) {
        $imgSrc = "https://linkanegocios.com.br/api/public/posts/" . $imgSrc;
    }

    $emailTemplate .= "
                    <tr>
                        <td class='content-padding' style='padding: 20px 40px 40px 40px;'>
                            <a href='$linkPost' style='text-decoration: none; display: block;'>
                                <img src='$imgSrc' alt='{$post['titulo']}' style='width: 100%; max-width: 600px; height: auto; display: block; border: 1px solid #eeeeee;'>
                            </a>
                            
                            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                <tr>
                                    <td style='padding-top: 20px; text-align: left;'>
                                        <p style='color: $corPrimaria; font-size: 12px; font-weight: bold; text-transform: uppercase; margin: 0 0 5px 0;'>
                                            TOP #$rank
                                        </p>
                                        <h3 style='margin: 0 0 10px 0; font-size: 20px; line-height: 1.3; color: $corTexto; font-weight: bold;'>
                                            <a href='$linkPost' style='color: $corTexto; text-decoration: none;'>{$post['titulo']}</a>
                                        </h3>
                                        <p style='color: $corCinza; font-size: 15px; line-height: 1.6; margin: 0 0 15px 0;'>
                                            " . substr(strip_tags($post['descricao']), 0, 130) . "...
                                        </p>
                                        <table border='0' cellpadding='0' cellspacing='0'>
                                            <tr>
                                                <td align='left' style='padding-top: 5px;'>
                                                    <a href='$linkPost' style='color: $corPrimaria; font-size: 15px; font-weight: bold; text-decoration: none; border-bottom: 2px solid $corPrimaria; padding-bottom: 2px;'>
                                                        Ler matéria completa &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <div style='border-bottom: 1px solid #eeeeee; margin-top: 30px;'></div>
                        </td>
                    </tr>
    ";
    $rank++;
}

$emailTemplate .= "
                    <tr>
                        <td align='center' style='background-color: $corFooter; padding: 40px 20px;'>
                            <p style='color: #ffffff; font-size: 16px; font-weight: bold; margin: 0 0 10px 0;'>
                                Linka Negócios
                            </p>
                            <p style='color: #cccccc; font-size: 12px; line-height: 1.5; margin: 0 0 20px 0;'>
                                Enviado para nossa lista de assinantes VIP.<br>
                                © $anoAtual Todos os direitos reservados.
                            </p>
                            <p style='margin: 0; font-size: 12px;'>
                                <a href='https://www.linkanegocios.com.br' style='color: #ffffff; text-decoration: none; font-weight: bold;'>Acessar Site</a>
                                <span style='color: #666666; margin: 0 10px;'>|</span>
                                <a href='{{LINK_DESCADASTRO}}' style='color: #999999; text-decoration: underline;'>Cancelar subscrição</a>
                            </p>
                        </td>
                    </tr>

                </table>
                <div style='height: 40px;'>&nbsp;</div>
            </td>
        </tr>
    </table>

</body>
</html>
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
$mail->SMTPDebug = 0;

try {
    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->Host       = 'smtp.titan.email';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contato@linkanegocios.com.br';
    $mail->Password   = '*********8'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->setFrom('contato@linkanegocios.com.br', 'Linka Negócios');
    $mail->isHTML(true);
    $mail->Subject = $assuntoEmail;
    $mail->AltBody = "Destaques do mês na Linka Negócios. Top 1: $tituloTop1. Acesse o site para ler mais.";

    $enviados = 0;
    if (count($subscribers) > 0) {
        foreach ($subscribers as $sub) {
            try {
                
                $linkUnsub = "https://linkanegocios.com.br/api/public/newsletter/unsubscribe.php?e=" . base64_encode($sub['email']);
                $corpoFinal = str_replace('{{LINK_DESCADASTRO}}', $linkUnsub, $emailTemplate);
                $mail->Body = $corpoFinal;
                $mail->addAddress($sub['email']);
                $mail->send();
                $enviados++;
                $mail->clearAddresses();
                 
            } catch (Exception $e) {
                $mail->clearAddresses();
                continue;
            }
        }
        echo "Newsletter MENSAL enviada com sucesso para $enviados assinantes.";
    } else {
        echo "Nenhum assinante ativo.";
    }
} catch (Exception $e) {
    echo "Erro geral: {$mail->ErrorInfo}";
}
