<?php
require 'base64url_encode.php';

function jwt_eh_valido($token) {
    $parts = explode('.', $token);
    
    // Se o token não conter três partes, ele é inválido
    if (count($parts) !== 3) {
        return false;
    }

    $secret = '#Frngoclimao20';
    $header = $parts[0];
    $payload = $parts[1];
    $signature_provided = $parts[2];

    // Gerar a assinatura novamente
    $signature_generated = hash_hmac('sha256', $header . '.' . $payload, $secret);

    // Verificar se a assinatura fornecida corresponde à assinatura gerada
    if ($signature_provided !== $signature_generated) {
        return false;
    }

    // Decodificar o payload e extrair as informações do token
    $infos_token = json_decode(base64_decode($payload));

    // Verificar se o token ainda é válido com base no tempo de expiração
    return time() < (int)$infos_token->exp;
}
?>
