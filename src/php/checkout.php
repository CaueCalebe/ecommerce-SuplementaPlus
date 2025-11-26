<?php
session_start();
require_once '../../backend/config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Gerar token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar se o carrinho tem itens
if (empty($_SESSION['carrinho'])) {
    $_SESSION['mensagem'] = 'Carrinho vazio!';
    header('Location: carrinho.php');
    exit();
}

$pdo = conectarBD();
$total = 0;
$produtos = [];
$erros = [];

// Buscar dados dos produtos no carrinho
try {
    foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
        $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            $produto['quantidade'] = $quantidade;
            $produto['subtotal'] = $produto['preco'] * $quantidade;
            $total += $produto['subtotal'];
            $produtos[] = $produto;
        }
    }
} catch (PDOException $e) {
    $erros[] = 'Erro ao carregar produtos.';
}

// Processar compra se for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $erros[] = 'Token de segurança inválido.';
    }
    
    // Validar campos obrigatórios
    $nome_completo = trim($_POST['nome_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $metodo_pagamento = trim($_POST['metodo_pagamento'] ?? '');
    
    // Validações
    if (strlen($nome_completo) < 3) {
        $erros[] = 'Nome completo deve ter pelo menos 3 caracteres.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido.';
    }
    
    if (strlen($telefone) < 11) {
        $erros[] = 'Telefone deve ter pelo menos 11 dígitos.';
    }
    
    if (strlen($cep) < 8) {
        $erros[] = 'CEP inválido.';
    }
    
    if (strlen($endereco) < 5) {
        $erros[] = 'Endereço inválido.';
    }
    
    if (strlen($cidade) < 3) {
        $erros[] = 'Cidade deve ter pelo menos 3 caracteres.';
    }
    
    if (strlen($estado) !== 2) {
        $erros[] = 'Estado deve ter 2 caracteres.';
    }
    
    if (!in_array($metodo_pagamento, ['credito', 'debito', 'pix', 'boleto'])) {
        $erros[] = 'Método de pagamento inválido.';
    }
    
    // Se não houver erros, criar pedido
    if (empty($erros)) {
        try {
            // Iniciar transação
            $pdo->beginTransaction();
            
            // Inserir pedido
            $stmt = $pdo->prepare('
                INSERT INTO pedidos (usuario_id, total, status, data_pedido, nome_completo, email, telefone, cep, endereco, numero, complemento, cidade, estado, metodo_pagamento)
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            
            $stmt->execute([
                $_SESSION['usuario_id'],
                $total,
                'pendente',
                $nome_completo,
                $email,
                $telefone,
                $cep,
                $endereco,
                $numero,
                $complemento,
                $cidade,
                $estado,
                $metodo_pagamento
            ]);
            
            $pedido_id = $pdo->lastInsertId();
            
            // Inserir itens do pedido
            foreach ($produtos as $p) {
                $stmt = $pdo->prepare('
                    INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco)
                    VALUES (?, ?, ?, ?)
                ');
                
                $stmt->execute([
                    $pedido_id,
                    $p['id'],
                    $p['quantidade'],
                    $p['preco']
                ]);
            }
            
            // Confirmar transação
            $pdo->commit();
            
            // Limpar carrinho
            $_SESSION['carrinho'] = [];
            
            // Redirecionar para página de confirmação
            $_SESSION['mensagem_sucesso'] = 'Pedido criado com sucesso! Número do pedido: ' . $pedido_id;
            header('Location: meus-pedidos.php');
            exit();
            
        } catch (PDOException $e) {
            // Reverter transação
            $pdo->rollBack();
            $erros[] = 'Erro ao processar pedido. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SuplementaPlus</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section class="checkout">
            <h2>Finalizar Compra</h2>
            
            <?php if (!empty($erros)): ?>
                <div class="mensagem-erro">
                    <ul>
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="checkout-container">
                <div class="checkout-form">
                    <h3>Dados de Entrega</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-grupo">
                            <label for="nome_completo">Nome Completo:</label>
                            <input type="text" id="nome_completo" name="nome_completo" required value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>">
                        </div>
                        
                        <div class="form-grupo">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        
                        <div class="form-grupo">
                            <label for="telefone">Telefone:</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 99999-9999" required value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-grupo">
                            <label for="cep">CEP:</label>
                            <input type="text" id="cep" name="cep" placeholder="00000-000" required value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>">
                        </div>
                        
                        <div class="form-grupo">
                            <label for="endereco">Endereço:</label>
                            <input type="text" id="endereco" name="endereco" required value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-grupo">
                                <label for="numero">Número:</label>
                                <input type="text" id="numero" name="numero" required value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>">
                            </div>
                            
                            <div class="form-grupo">
                                <label for="complemento">Complemento (opcional):</label>
                                <input type="text" id="complemento" name="complemento" value="<?= htmlspecialchars($_POST['complemento'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-grupo">
                                <label for="cidade">Cidade:</label>
                                <input type="text" id="cidade" name="cidade" required value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>">
                            </div>
                            
                            <div class="form-grupo">
                                <label for="estado">Estado (UF):</label>
                                <input type="text" id="estado" name="estado" placeholder="SP" maxlength="2" required value="<?= htmlspecialchars($_POST['estado'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <h3 style="margin-top: 30px;">Método de Pagamento</h3>
                        
                        <div class="form-grupo">
                            <label>
                                <input type="radio" name="metodo_pagamento" value="credito" required <?= (isset($_POST['metodo_pagamento']) && $_POST['metodo_pagamento'] === 'credito') ? 'checked' : '' ?>>
                                Cartão de Crédito
                            </label>
                        </div>
                        
                        <div class="form-grupo">
                            <label>
                                <input type="radio" name="metodo_pagamento" value="debito" <?= (isset($_POST['metodo_pagamento']) && $_POST['metodo_pagamento'] === 'debito') ? 'checked' : '' ?>>
                                Cartão de Débito
                            </label>
                        </div>
                        
                        <div class="form-grupo">
                            <label>
                                <input type="radio" name="metodo_pagamento" value="pix" <?= (isset($_POST['metodo_pagamento']) && $_POST['metodo_pagamento'] === 'pix') ? 'checked' : '' ?>>
                                PIX
                            </label>
                        </div>
                        
                        <div class="form-grupo">
                            <label>
                                <input type="radio" name="metodo_pagamento" value="boleto" <?= (isset($_POST['metodo_pagamento']) && $_POST['metodo_pagamento'] === 'boleto') ? 'checked' : '' ?>>
                                Boleto Bancário
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-finalizar">Finalizar Compra</button>
                        <a href="carrinho.php" class="btn-voltar-checkout">Voltar para Carrinho</a>
                    </form>
                </div>
                
                <div class="checkout-resumo">
                    <h3>Resumo do Pedido</h3>
                    
                    <div class="resumo-itens">
                        <?php foreach ($produtos as $p): ?>
                            <div class="resumo-item">
                                <div class="item-info">
                                    <img src="../../assets/<?= htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                                    <div>
                                        <p class="item-nome"><?= htmlspecialchars($p['nome']) ?></p>
                                        <p class="item-qtd">Qtd: <?= $p['quantidade'] ?></p>
                                    </div>
                                </div>
                                <p class="item-preco">R$ <?= number_format($p['subtotal'], 2, ',', '.') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="resumo-divisor"></div>
                    
                    <div class="resumo-linha">
                        <span>Subtotal:</span>
                        <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                    
                    <div class="resumo-linha">
                        <span>Frete:</span>
                        <span>A calcular</span>
                    </div>
                    
                    <div class="resumo-total-checkout">
                        <span>Total:</span>
                        <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
