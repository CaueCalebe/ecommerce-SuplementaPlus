<?php
session_start();
require_once '../../backend/config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = conectarBD();
$pedidos = [];
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? '';
unset($_SESSION['mensagem_sucesso']);

// Buscar pedidos do usuário
try {
    $stmt = $pdo->prepare('
        SELECT * FROM pedidos 
        WHERE usuario_id = ? 
        ORDER BY data_pedido DESC
    ');
    $stmt->execute([$_SESSION['usuario_id']]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = 'Erro ao carregar pedidos.';
}

// Processar cancelamento de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_pedido'])) {
    $pedido_id = (int)$_POST['cancelar_pedido'];
    
    try {
        // Verificar se o pedido pertence ao usuário
        $stmt = $pdo->prepare('SELECT usuario_id FROM pedidos WHERE id = ?');
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido && $pedido['usuario_id'] === $_SESSION['usuario_id']) {
            // Apenas permitir cancelamento de pedidos pendentes
            $stmt = $pdo->prepare('SELECT status FROM pedidos WHERE id = ?');
            $stmt->execute([$pedido_id]);
            $status_pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($status_pedido['status'] === 'pendente') {
                $stmt = $pdo->prepare('UPDATE pedidos SET status = ? WHERE id = ?');
                $stmt->execute(['cancelado', $pedido_id]);
                $_SESSION['mensagem_sucesso'] = 'Pedido cancelado com sucesso.';
            }
        }
    } catch (PDOException $e) {
        $mensagem_erro = 'Erro ao cancelar pedido.';
    }
    
    header('Location: meus-pedidos.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - SuplementaPlus</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section class="meus-pedidos">
            <h2>Meus Pedidos</h2>
            
            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="mensagem-sucesso">
                    <?= htmlspecialchars($mensagem_sucesso) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($mensagem_erro)): ?>
                <div class="mensagem-erro">
                    <?= htmlspecialchars($mensagem_erro) ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($pedidos)): ?>
                <div class="sem-pedidos">
                    <p>Você ainda não fez nenhum pedido.</p>
                    <a href="index.php" class="btn-voltar">Voltar para Produtos</a>
                </div>
            <?php else: ?>
                <div class="pedidos-lista">
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="pedido-card">
                            <div class="pedido-header">
                                <div class="pedido-info">
                                    <p class="pedido-numero">Pedido #<?= $pedido['id'] ?></p>
                                    <p class="pedido-data"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></p>
                                </div>
                                <div class="pedido-status">
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
                                </div>
                            </div>
                            
                            <div class="pedido-detalhes">
                                <p><strong>Nome:</strong> <?= htmlspecialchars($pedido['nome_completo']) ?></p>
                                <p><strong>Método de Pagamento:</strong> 
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
                                <p><strong>Endereço:</strong> <?= htmlspecialchars($pedido['endereco']) ?>, <?= htmlspecialchars($pedido['numero']) ?> - <?= htmlspecialchars($pedido['cidade']) ?>, <?= htmlspecialchars($pedido['estado']) ?></p>
                            </div>
                            
                            <div class="pedido-total">
                                <span>Total:</span>
                                <span class="valor-total">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                            </div>
                            
                            <div class="pedido-acoes">
                                <a href="detalhes-pedido.php?id=<?= $pedido['id'] ?>" class="btn-detalhes">Ver Detalhes</a>
                                <?php if ($pedido['status'] === 'pendente'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="cancelar_pedido" value="<?= $pedido['id'] ?>">
                                        <button type="submit" class="btn-cancelar" onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">Cancelar Pedido</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
