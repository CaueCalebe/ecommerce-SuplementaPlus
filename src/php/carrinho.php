<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../backend/config/db.php';
require_once '../../backend/models/Carrinho.php';
require_once '../../backend/models/Produto.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'checkout') {
        // Processar checkout
        $user_id = $_POST['user_id'] ?? null;
        $items_json = $_POST['items'] ?? '[]';
        
        // Decodificar items
        $cart_items = json_decode($items_json, true);

        if (!$user_id || empty($cart_items)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos para checkout']);
            exit;
        }
        
        // Move this method outside the try block and class Produto if necessary

        // Validar cada item
        $produto = new Produto($conn);
        $total = 0;
        
        foreach ($cart_items as $item) {
            if (!isset($item['id']) || !isset($item['price']) || !isset($item['quantidade'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de item inválido']);
                exit;
            }
            
            // Verificar se produto existe
            $produtoData = $produto->buscarPorId($item['id']);
            if (!$produtoData) {
                echo json_encode(['success' => false, 'message' => 'Produto ID ' . $item['id'] . ' não encontrado']);
                exit;
            }
            
            // Verificar estoque
            if (!$produto->verificarEstoque($item['id'], $item['quantidade'])) {
                echo json_encode(['success' => false, 'message' => 'Estoque insuficiente para ' . $produtoData['nome']]);
                exit;
            }
            
            $total += $item['price'] * $item['quantidade'];
        }
        
        // Adicionar frete
        $total += 10.00;

        // Criar pedido
        $carrinho = new Carrinho($conn);
        $resultado = $carrinho->criarPedido($user_id, $cart_items, $total);
        
        // Se sucesso, atualizar estoque
        if ($resultado['success']) {
            foreach ($cart_items as $item) {
                $produto->atualizarEstoque($item['id'], $item['quantidade']);
            }
        }
        
        echo json_encode($resultado);
        
    } elseif ($action === 'get') {
        // Retornar carrinho (gerenciado pelo frontend)
        echo json_encode(['success' => true, 'message' => 'Carrinho gerenciado pelo localStorage']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}