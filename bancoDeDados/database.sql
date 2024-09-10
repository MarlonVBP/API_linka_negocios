CREATE DATABASE IF NOT EXISTS `LinkNegocios` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `LinkNegocios`;

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_admin VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255),
    cargo VARCHAR(100),
    ultimo_login TIMESTAMP,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS postagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    descricao TEXT NOT NULL,
    url_imagem VARCHAR(255),
    views INT NOT NULL,
    comentarios INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES admin(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE
    SET
        NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS paginas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ProdutoDivulgacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo_breve VARCHAR(255) NOT NULL,
    detalhes_problema_beneficios TEXT,
    destaque_problemas TEXT,
    destaque_beneficio1 TEXT,
    destaque_beneficio2 TEXT,
    destaque_beneficio3 TEXT,
    cta VARCHAR(255),
    imagem_placeholder VARCHAR(255),
    beneficio1 TEXT,
    problema_beneficio1 TEXT,
    beneficio2 TEXT,
    problema_beneficio2 TEXT,
    beneficio3 TEXT,
    problema_beneficio3 TEXT,
    porque_clicar TEXT,
    pergunta1 TEXT,
    resposta1 TEXT,
    pergunta2 TEXT,
    resposta2 TEXT,
    pergunta3 TEXT,
    resposta3 TEXT,
    pergunta4 TEXT,
    resposta4 TEXT,
    pergunta5 TEXT,
    resposta5 TEXT
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `comentarios_produtos`;

CREATE TABLE IF NOT EXISTS comentarios_produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    user_name VARCHAR(50) NOT NULL,
    profissao VARCHAR(50),
    empresa VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    avaliacao INT CHECK (
        avaliacao >= 1
        AND avaliacao <= 5
    ),
    visualizado BOOLEAN DEFAULT true,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES ProdutoDivulgacao(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `comentarios_postagens`;

CREATE TABLE IF NOT EXISTS comentarios_postagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    postagem_id INT NOT NULL,
    user_name VARCHAR(50) NOT NULL,
    profissao VARCHAR(50),
    empresa VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    avaliacao INT CHECK (
        avaliacao >= 1
        AND avaliacao <= 5
    ),
    visualizado BOOLEAN DEFAULT true,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (postagem_id) REFERENCES postagens(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `comentarios_paginas`;

CREATE TABLE IF NOT EXISTS comentarios_paginas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pagina_id INT NOT NULL,
    user_name VARCHAR(50) NOT NULL,
    profissao VARCHAR(50),
    empresa VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    avaliacao INT CHECK (
        avaliacao >= 1
        AND avaliacao <= 5
    ),
    visualizado BOOLEAN DEFAULT true,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pagina_id) REFERENCES paginas(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS avaliacao_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    avaliacao INT NOT NULL CHECK (
        avaliacao >= 1
        AND avaliacao <= 5
    ),
    mensagem VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS postagem_tags (
    postagem_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (postagem_id, tag_id),
    FOREIGN KEY (postagem_id) REFERENCES postagens(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(15),
    empresa VARCHAR(100),
    area_atuacao VARCHAR(100),
    mensagem TEXT,
    visualizado BOOLEAN DEFAULT true,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,
    resposta TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    conteudo1 TEXT NOT NULL,
    conteudo2 TEXT NOT NULL,
    conteudo3 TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dashboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes VARCHAR(20) NOT NULL,
    dados INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS motivos_escolher_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imagem VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS motivos_escolher_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casos_sucesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT,
    imagem VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
