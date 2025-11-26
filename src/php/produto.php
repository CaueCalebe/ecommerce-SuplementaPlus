<?php
/**
 * API de Produtos
 * Endpoints para gerenciar produtos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../backend/config/db.php';
require_once '../../backend/models/Produto.php';

// Instanciar modelo
$produto = new Produto($conn);

// Obter ação da requisição
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // ===== GET - BUSCAR PRODUTOS =====
    if ($method === 'GET') {
        
        // Buscar produto por ID
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $resultado = $produto->buscarPorId($id);
            echo json_encode($resultado);
            exit;
        }
        
        // Buscar por termo de pesquisa
        if (isset($_GET['busca']) && !empty($_GET['busca'])) {
            $termo = trim($_GET['busca']);
            $resultado = $produto->buscarPorNome($termo);
            echo json_encode($resultado);
            exit;
        }
        
        // Listar produtos em destaque
        if ($action === 'destaque') {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
            $resultado = $produto->listarDestaque($limit);
            echo json_encode($resultado);
            exit;
        }
        
        // Obter estatísticas
        if ($action === 'estatisticas') {
            $resultado = $produto->obterEstatisticas();
            echo json_encode($resultado);
            exit;
        }
        
        // Listar todos os produtos (padrão)
        $resultado = $produto->listar();
        echo json_encode($resultado);
        exit;
    }
    
    // ===== POST - CRIAR PRODUTO (ADMIN) =====
    if ($method === 'POST' && $action === 'criar') {
        
        // Validar dados recebidos
        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'preco' => floatval($_POST['preco'] ?? 0),
            'imagem' => trim($_POST['imagem'] ?? 'default.jpg'),
            'estoque' => intval($_POST['estoque'] ?? 0)
        ];
        
        // Validar
        $validacao = $produto->validarDados($dados);
        if (!$validacao['valido']) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados inválidos',
                'erros' => $validacao['erros']
            ]);
            exit;
        }
        
        // Criar produto
        $resultado = $produto->criar($dados);
        echo json_encode($resultado);
        exit;
    }
    
    // ===== PUT/POST - ATUALIZAR PRODUTO (ADMIN) =====
    if (($method === 'PUT' || $method === 'POST') && $action === 'atualizar') {
        
        // Obter ID
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID do produto não fornecido'
            ]);
            exit;
        }
        
        // Para PUT, ler do corpo da requisição
        if ($method === 'PUT') {
            parse_str(file_get_contents("php://input"), $_POST);
        }
        
        // Validar dados
        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'preco' => floatval($_POST['preco'] ?? 0),
            'imagem' => trim($_POST['imagem'] ?? 'default.jpg'),
            'estoque' => intval($_POST['estoque'] ?? 0)
        ];
        
        // Validar
        $validacao = $produto->validarDados($dados);
        if (!$validacao['valido']) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados inválidos',
                'erros' => $validacao['erros']
            ]);
            exit;
        }
        
        // Atualizar produto
        $resultado = $produto->atualizar($id, $dados);
        echo json_encode($resultado);
        exit;
    }
    
    // ===== DELETE - DELETAR PRODUTO (ADMIN) =====
    if ($method === 'DELETE' || ($method === 'POST' && $action === 'deletar')) {
        
        // Obter ID
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID do produto não fornecido'
            ]);
            exit;
        }
        
        // Deletar produto
        $resultado = $produto->deletar($id);
        echo json_encode($resultado);
        exit;
    }
    
    // ===== POST - VERIFICAR ESTOQUE =====
    if ($method === 'POST' && $action === 'verificar_estoque') {
        
        $id = intval($_POST['id'] ?? 0);
        $quantidade = intval($_POST['quantidade'] ?? 0);
        
        if (!$id || !$quantidade) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados insuficientes'
            ]);
            exit;
        }
        
        $temEstoque = $produto->verificarEstoque($id, $quantidade);
        
        echo json_encode([
            'success' => true,
            'tem_estoque' => $temEstoque,
            'mensagem' => $temEstoque ? 'Estoque disponível' : 'Estoque insuficiente'
        ]);
        exit;
    }
    
    // Método não suportado
    echo json_encode([
        'success' => false,
        'message' => 'Método ou ação não suportada'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}