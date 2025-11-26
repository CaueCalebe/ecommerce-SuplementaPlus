<?php
/**
 * Configuração do Banco de Dados
 * PostgreSQL Connection
 */

// Supprimir erros de output direto
error_reporting(0);
ini_set('display_errors', 0);

// Configurações do banco
$host = 'localhost';
$port = '5432';
$dbname = 'suplementa_db';
$user = 'postgres';
$password = 'postgres';

// Variável global de conexão
$conn = null;
$db_error = null;

try {
    // Criar conexão PDO
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Definir charset UTF-8
    $conn->exec("SET NAMES 'UTF8'");
    
} catch (PDOException $e) {
    // Armazenar erro para uso posterior
    $db_error = $e->getMessage();
    error_log("Erro de conexão com BD: " . $db_error);
}

