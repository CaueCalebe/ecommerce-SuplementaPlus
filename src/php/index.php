<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../backend/config/db.php';

// Buscar produtos do banco de dados
$produtos = [];
try {
    $stmt = $conn->query("SELECT id, nome, descricao, preco, imagem FROM produtos ORDER BY id ASC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar resposta
    echo json_encode([
        'success' => true,
        'data' => $produtos,
        'total' => count($produtos)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
    ]);
}

exit;