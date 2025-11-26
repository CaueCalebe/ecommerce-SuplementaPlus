<?php
class Carrinho {
    private $conn;
    private $table_pedidos = 'pedidos';
    private $table_itens = 'itens_pedido';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function criarPedido($usuario_id, $itens, $total) {
        try {
            // Inicia transação
            $this->conn->beginTransaction();

            // Criar pedido
            $sql = "INSERT INTO {$this->table_pedidos} (usuario_id, data_pedido, status, total) 
                    VALUES (:usuario_id, NOW(), 'pendente', :total) RETURNING id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':total', $total);
            $stmt->execute();
            
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            $pedido_id = $pedido['id'];

            // Adicionar itens do pedido
            $sql_item = "INSERT INTO {$this->table_itens} (pedido_id, produto_id, quantidade, preco_unitario) 
                         VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario)";
            $stmt_item = $this->conn->prepare($sql_item);

            foreach ($itens as $item) {
                $stmt_item->bindValue(':pedido_id', $pedido_id, PDO::PARAM_INT);
                $stmt_item->bindValue(':produto_id', $item['id'], PDO::PARAM_INT);
                $stmt_item->bindValue(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                $stmt_item->bindValue(':preco_unitario', $item['price']);
                $stmt_item->execute();
            }

            // Commit da transação
            $this->conn->commit();

            return [
                'success' => true, 
                'message' => 'Pedido realizado com sucesso!',
                'pedido_id' => $pedido_id
            ];
        } catch (PDOException $e) {
            // Rollback em caso de erro
            $this->conn->rollBack();
            return [
                'success' => false, 
                'message' => 'Erro ao criar pedido: ' . $e->getMessage()
            ];
        }
    }

    public function listarPedidosUsuario($usuario_id) {
        try {
            $sql = "SELECT p.id, p.data_pedido, p.status, p.total,
                    COUNT(ip.id) as total_itens
                    FROM {$this->table_pedidos} p
                    LEFT JOIN {$this->table_itens} ip ON p.id = ip.pedido_id
                    WHERE p.usuario_id = :usuario_id
                    GROUP BY p.id, p.data_pedido, p.status, p.total
                    ORDER BY p.data_pedido DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function buscarPedido($pedido_id) {
        try {
            $sql = "SELECT p.*, 
                    json_agg(json_build_object(
                        'produto_id', ip.produto_id,
                        'quantidade', ip.quantidade,
                        'preco_unitario', ip.preco_unitario
                    )) as itens
                    FROM {$this->table_pedidos} p
                    LEFT JOIN {$this->table_itens} ip ON p.id = ip.pedido_id
                    WHERE p.id = :pedido_id
                    GROUP BY p.id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function atualizarStatus($pedido_id, $status) {
        try {
            $sql = "UPDATE {$this->table_pedidos} SET status = :status WHERE id = :pedido_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
