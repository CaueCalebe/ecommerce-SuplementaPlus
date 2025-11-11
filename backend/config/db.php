</db>php
<?php
$host = 'localhost';
$port = '5432';
$dbname = 'suplementa_db';
$user = 'postgres';
$password = 'sua_senha_aqui';

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

