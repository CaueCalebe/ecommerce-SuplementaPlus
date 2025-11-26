<?php

require_once __DIR__ . '/../config/db.php';

class PedidoController {
    
    private $pdo;
    
    public function __construct() {
        $this->pdo = conectarBD();
    }
    
    /**
     * Criar um novo pedido
     */
    public function criar($usuario_id, $total, $nome_completo, $email, $telefone, $cep, $endereco, $numero, $complemento, $cidade, $estado, $metodo_pagamento, $itens) {
        try {
            // Iniciar transação
            $this->pdo->beginTransaction();
            
            // Inserir pedido
            $stmt = $this->pdo->prepare('
                INSERT INTO pedidos (usuario_id, total, status, data_pedido, nome_completo, email, telefone, cep, endereco, numero, complemento, cidade, estado, metodo_pagamento)
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            
            $stmt->execute([
                $usuario_id,
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
            
            $pedido_id = $this->pdo->lastInsertId();
            
            // Inserir itens do pedido
            foreach ($itens as $item) {
                $stmt = $this->pdo->prepare('
                    INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco)
                    VALUES (?, ?, ?, ?)
                ');
                
                $stmt->execute([
                    $pedido_id,
                    $item['produto_id'],
                    $item['quantidade'],
                    $item['preco']
                ]);
            }
            
            // Confirmar transação
            $this->pdo->commit();
            
            return $pedido_id;
            
        } catch (PDOException $e) {
            // Reverter transação
            $this->pdo->rollBack();
            throw new Exception('Erro ao criar pedido: ' . $e->getMessage());
        }
    }
    
    /**
     * Listar pedidos por usuário
     */
    public function listarPorUsuario($usuario_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT * FROM pedidos 
                WHERE usuario_id = ? 
                ORDER BY data_pedido DESC
            ');
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao listar pedidos: ' . $e->getMessage());
        }
    }
    
    /**
     * Obter detalhes de um pedido
     */
    public function obter($pedido_id, $usuario_id = null) {
        try {
            $sql = 'SELECT * FROM pedidos WHERE id = ?';
            $params = [$pedido_id];
            
            if ($usuario_id !== null) {
                $sql .= ' AND usuario_id = ?';
                $params[] = $usuario_id;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao obter pedido: ' . $e->getMessage());
        }
    }
    
    /**
     * Obter itens de um pedido
     */
    public function obterItens($pedido_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT ip.*, p.nome, p.imagem
                FROM itens_pedido ip
                JOIN produtos p ON ip.produto_id = p.id
                WHERE ip.pedido_id = ?
                ORDER BY ip.id
            ');
            $stmt->execute([$pedido_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao obter itens do pedido: ' . $e->getMessage());
        }
    }
    
    /**
     * Atualizar status do pedido
     */
    public function atualizarStatus($pedido_id, $novo_status, $usuario_id = null) {
        // Validar status
        $status_validos = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
        
        if (!in_array($novo_status, $status_validos)) {
            throw new Exception('Status inválido.');
        }
        
        try {
            $sql = 'UPDATE pedidos SET status = ?, data_atualizacao = NOW() WHERE id = ?';
            $params = [$novo_status, $pedido_id];
            
            if ($usuario_id !== null) {
                $sql .= ' AND usuario_id = ?';
                $params[] = $usuario_id;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception('Erro ao atualizar status: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancelar pedido (apenas se estiver pendente)
     */
    public function cancelar($pedido_id, $usuario_id) {
        try {
            // Verificar se o pedido pertence ao usuário e está pendente
            $pedido = $this->obter($pedido_id, $usuario_id);
            
            if (!$pedido) {
                throw new Exception('Pedido não encontrado.');
            }
            
            if ($pedido['status'] !== 'pendente') {
                throw new Exception('Apenas pedidos pendentes podem ser cancelados.');
            }
            
            // Atualizar status para cancelado
            return $this->atualizarStatus($pedido_id, 'cancelado', $usuario_id);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Listar todos os pedidos (apenas para administrador)
     */
    public function listarTodos($limite = 50, $pagina = 1) {
        try {
            $offset = ($pagina - 1) * $limite;
            
            $stmt = $this->pdo->prepare('
                SELECT p.*, u.nome as usuario_nome, u.email as usuario_email
                FROM pedidos p
                JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.data_pedido DESC
                LIMIT ? OFFSET ?
            ');
            $stmt->execute([$limite, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao listar pedidos: ' . $e->getMessage());
        }
    }
    
    /**
     * Contar total de pedidos (para paginação)
     */
    public function contar() {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM pedidos');
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            throw new Exception('Erro ao contar pedidos: ' . $e->getMessage());
        }
    }
    
    /**
     * Obter estatísticas de pedidos
     */
    public function obterEstatisticas() {
        try {
            $stmt = $this->pdo->query('
                SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN status = "pendente" THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = "processando" THEN 1 ELSE 0 END) as processando,
                    SUM(CASE WHEN status = "enviado" THEN 1 ELSE 0 END) as enviados,
                    SUM(CASE WHEN status = "entregue" THEN 1 ELSE 0 END) as entregues,
                    SUM(CASE WHEN status = "cancelado" THEN 1 ELSE 0 END) as cancelados,
                    SUM(total) as receita_total
                FROM pedidos
            ');
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao obter estatísticas: ' . $e->getMessage());
        }
    }
}
