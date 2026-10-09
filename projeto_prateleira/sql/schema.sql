-- Prateleira — esquema do banco (MySQL 5.7+/MariaDB 10.3+). Valores em centavos.
SET NAMES utf8mb4;

CREATE TABLE planos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL,
  preco_centavos INT UNSIGNED NOT NULL,
  limite_produtos INT UNSIGNED NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE lojas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  slug VARCHAR(60) NOT NULL UNIQUE,
  whatsapp VARCHAR(20) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  loja_id INT UNSIGNED NOT NULL,
  nome VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  papel ENUM('dono','equipe') NOT NULL DEFAULT 'dono',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assinaturas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  loja_id INT UNSIGNED NOT NULL,
  plano_id INT UNSIGNED NOT NULL,
  status ENUM('pendente','ativa','atrasada','cancelada') NOT NULL DEFAULT 'pendente',
  gateway_ref VARCHAR(80) NULL,
  vence_em DATE NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE,
  FOREIGN KEY (plano_id) REFERENCES planos(id),
  INDEX (loja_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pagamentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assinatura_id INT UNSIGNED NOT NULL,
  valor_centavos INT UNSIGNED NOT NULL,
  status ENUM('pendente','pago','falhou') NOT NULL DEFAULT 'pendente',
  gateway_ref VARCHAR(80) NULL UNIQUE,
  pago_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produtos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  loja_id INT UNSIGNED NOT NULL,
  nome VARCHAR(120) NOT NULL,
  categoria VARCHAR(60) NOT NULL,
  preco_centavos INT UNSIGNED NOT NULL,
  estoque INT NOT NULL DEFAULT 0,
  emoji VARCHAR(8) NULL,
  imagem VARCHAR(255) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE,
  INDEX (loja_id, ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedidos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  loja_id INT UNSIGNED NOT NULL,
  cliente VARCHAR(80) NOT NULL,
  telefone VARCHAR(20) NOT NULL,
  recebimento ENUM('retirada','entrega') NOT NULL DEFAULT 'retirada',
  endereco VARCHAR(200) NULL,
  total_centavos INT UNSIGNED NOT NULL,
  origem ENUM('online','balcao') NOT NULL DEFAULT 'online',
  status ENUM('pendente','pago','cancelado') NOT NULL DEFAULT 'pendente',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE,
  INDEX (loja_id, status, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedido_itens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL,
  produto_id INT UNSIGNED NULL,
  nome VARCHAR(120) NOT NULL,
  preco_centavos INT UNSIGNED NOT NULL,
  qtd INT UNSIGNED NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Planos de exemplo (ajuste nomes, preços e limites)
INSERT INTO planos (nome, preco_centavos, limite_produtos) VALUES
 ('Básico', 2990, 50),
 ('Completo', 5990, 500);
