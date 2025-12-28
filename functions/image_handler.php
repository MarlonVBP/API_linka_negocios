function processarImagensDoConteudo($htmlContent) {
    if (empty($htmlContent)) return $htmlContent;

    $dom = new DOMDocument();
    // Suprime erros de HTML malformado e configura UTF-8
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');
    $alterou = false;

    foreach ($images as $img) {
        $src = $img->getAttribute('src');

        // Verifica se é uma imagem em Base64
        if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
            $data = substr($src, strpos($src, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif, webp

            // Valida extensão
            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png', 'webp' ])) {
                continue;
            }

            $data = base64_decode($data);

            if ($data === false) {
                continue;
            }

            // Gera nome único e define caminho
            $fileName = uniqid() . '_' . time() . '.' . $type;
            
            // CAMINHO ONDE A IMAGEM SERÁ SALVA (Ajuste conforme sua estrutura de pastas)
            // Considerando que create.php está em public/posts/
            $diretorioDestino = 'imagens/'; 
            
            if (!is_dir($diretorioDestino)) {
                mkdir($diretorioDestino, 0755, true);
            }

            file_put_contents($diretorioDestino . $fileName, $data);

            // URL PÚBLICA PARA ACESSAR A IMAGEM
            // Ajuste para a URL real da sua API
            $webUrl = 'https://sua-api.com.br/public/posts/imagens/' . $fileName; 
            
            // Substitui o src base64 pela URL do arquivo
            $img->setAttribute('src', $webUrl);
            $img->setAttribute('class', 'img-fluid post-image'); // Adiciona classe CSS para responsividade
            $alterou = true;
        }
    }

    if ($alterou) {
        return $dom->saveHTML();
    }

    return $htmlContent;
}