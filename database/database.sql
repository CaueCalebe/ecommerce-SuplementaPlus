-- Script para criar as tabelas do banco de dados suplementa_db
-- Execute este script no seu PostgreSQL

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    imagem VARCHAR(255),
    estoque INT DEFAULT 100,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'pendente',
    total DECIMAL(10, 2) DEFAULT 0
);

-- Tabela de Itens do Pedido
CREATE TABLE IF NOT EXISTS itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    produto_id INT NOT NULL REFERENCES produtos(id),
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL
);

-- Tabela de Carrinho (opcional, se usar sessão no servidor)
CREATE TABLE IF NOT EXISTS carrinho (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    produto_id INT NOT NULL REFERENCES produtos(id),
    quantidade INT NOT NULL,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir produtos de exemplo COM ESTOQUE
INSERT INTO produtos (nome, descricao, preco, imagem, estoque) VALUES
('Whey Protein Isolate 100%', 'Proteína isolada de alta qualidade', 129.90, 'whey-100-isolado.jpg', 50),
('BCAA G-Force', 'BCAA para recuperação muscular', 99.90, 'bcaa-g-force.jpg', 75),
('Multi-Vitamin With Iron', 'Multivitamínico com ferro', 89.90, 'multi-vitamin-with-iron.jpg', 100),
('Pasta de Amendoim Choco Mix com Whey Protein 250g', 'Combinação de proteína e amendoim', 79.90, 'pre-treino.jpg', 40),
('Creatine Drive Nutrex Research 300g', 'Creatina pura para ganho de força', 120.00, 'creatina-monohydratada.jpg', 60),
('Whey Protein Pró Max Titanium Sabor Baunilha 1kg', 'Whey protein premium com sabor baunilha', 100.00, 'whey-protein-pro-max-titanium-po-baunilha.jpg', 35),
('Creatina Max Titanium 300g', 'Creatina monohidratada pura', 85.00, 'creatina-po-max-titanium.jpg', 80),
('Pré-Treino S.A.W 120 Cápsulas', 'Pré-treino em cápsula para energia', 80.25, 's-a-w-capsules-glowne-YI.jpg', 45),
('Multivitamínico Owl Vita 60 Cápsulas', 'Multivitamínico completo', 110.00, 'owl-vita-multivitaminico.jpg', 90),
('BCAA 3000 Body Nutry 120 Cápsulas', 'BCAA em formato de cápsula', 92.15, 'bcaa-3000.jpg', 65);
