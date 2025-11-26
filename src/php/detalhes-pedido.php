<?php
session_start();
require_once '../../backend/config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$pedido_id = (int)($_GET['id'] ?? 0);

if ($pedido_id === 0) {
    header('Location: meus-pedidos.php');
    exit();
}

$pdo = conectarBD();
$pedido = [];
$itens_pedido = [];
$mensagem_erro = '';

// Buscar dados do pedido
try {
    $stmt = $pdo->prepare('
        SELECT * FROM pedidos 
        WHERE id = ? AND usuario_id = ?
    ');
    $stmt->execute([$pedido_id, $_SESSION['usuario_id']]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        header('Location: meus-pedidos.php');
        exit();
    }
    
    // Buscar itens do pedido
    $stmt = $pdo->prepare('
        SELECT ip.*, p.nome, p.imagem
        FROM itens_pedido ip
        JOIN produtos p ON ip.produto_id = p.id
        WHERE ip.pedido_id = ?
        ORDER BY ip.id
    ');
    $stmt->execute([$pedido_id]);
    $itens_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $mensagem_erro = 'Erro ao carregar detalhes do pedido.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido - SuplementaPlus</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section class="detalhes-pedido">
            <h2>Detalhes do Pedido #<?= $pedido['id'] ?></h2>
            
            <?php if (!empty($mensagem_erro)): ?>
                <div class="mensagem-erro">
                    <?= htmlspecialchars($mensagem_erro) ?>
                </div>
            <?php endif; ?>
            
            <div class="pedido-conteudo">
                <div class="secao-pedido">
                    <h3>Status do Pedido</h3>
                    <div class="status-box">
                        <span class="status status-<?= strtolower($pedido['status']) ?>">
                            <?php
                            $status_labels = [
                                'pendente' => 'Pendente',
                                'processando' => 'Processando',
                                'enviado' => 'Enviado',
                                'entregue' => 'Entregue',
                                'cancelado' => 'Cancelado'
                            ];
                            echo $status_labels[$pedido['status']] ?? $pedido['status'];
                            ?>
                        </span>
                        <p class="data-pedido">Data: <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></p>
                    </div>
                </div>
                
                <div class="secao-pedido">
                    <h3>Informações de Entrega</h3>
                    <div class="info-box">
                        <p><strong>Nome Completo:</strong> <?= htmlspecialchars($pedido['nome_completo']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></p>
                        <p><strong>Telefone:</strong> <?= htmlspecialchars($pedido['telefone']) ?></p>
                        <p><strong>CEP:</strong> <?= htmlspecialchars($pedido['cep']) ?></p>
                        <p><strong>Endereço:</strong> <?= htmlspecialchars($pedido['endereco']) ?>, <?= htmlspecialchars($pedido['numero']) ?></p>
                        <?php if (!empty($pedido['complemento'])): ?>
                            <p><strong>Complemento:</strong> <?= htmlspecialchars($pedido['complemento']) ?></p>
                        <?php endif; ?>
                        <p><strong>Cidade:</strong> <?= htmlspecialchars($pedido['cidade']) ?>, <?= htmlspecialchars($pedido['estado']) ?></p>
                    </div>
                </div>
                
                <div class="secao-pedido">
                    <h3>Método de Pagamento</h3>
                    <div class="info-box">
                        <p>
                            <?php
                            $metodos = [
                                'credito' => 'Cartão de Crédito',
                                'debito' => 'Cartão de Débito',
                                'pix' => 'PIX',
                                'boleto' => 'Boleto Bancário'
                            ];
                            echo $metodos[$pedido['metodo_pagamento']] ?? $pedido['metodo_pagamento'];
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="secao-pedido">
                    <h3>Produtos</h3>
                    <table class="tabela-itens">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_pedido as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-produto">
                                            <img src="../../assets/<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                                            <span><?= htmlspecialchars($item['nome']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= $item['quantidade'] ?></td>
                                    <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item['quantidade'] * $item['preco'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="secao-pedido total-secao">
                    <div class="linha-total">
                        <span>Total do Pedido:</span>
                        <span class="valor-grande">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="acoes-pedido">
                <a href="meus-pedidos.php" class="btn-voltar">Voltar</a>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
