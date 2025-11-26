<?php
// Configurar output antes de qualquer coisa
error_reporting(0);
ini_set('display_errors', 0);

// Define headers antes de qualquer output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Caminho absoluto para evitar erros
$base_dir = dirname(dirname(dirname(__FILE__)));
require_once $base_dir . '/backend/config/db.php';
require_once $base_dir . '/backend/models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validações básicas
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios']);
    exit;
}

// Verificar conexão com BD
if ($conn === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
    exit;
}

try {
    $usuario = new Usuario($conn);
    $resultado = $usuario->login($email, $password);
    
    echo json_encode($resultado);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

exit;