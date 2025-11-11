├── controllers/
│   ├── authController.php
│   └── produtoController.php
```php
<?php
require_once '../config/db.php';
require_once '../models/Produto.php';

$produto = new Produto($conn);

$acao = $_GET['action'] ?? 'listar';

if ($acao === 'listar') {
    $produtos = $produto->listar();
    echo json_encode($produtos);
}

if ($acao === 'buscar' && isset($_GET['id'])) {
    $p = $produto->buscarPorId($_GET['id']);
    echo json_encode($p);