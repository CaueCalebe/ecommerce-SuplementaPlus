<?php
/**
 * Classe Produto
 * Gerencia todas as operações relacionadas aos produtos no banco de dados
 */
class Produto {
    private $conn;
    private $table = 'produtos';

    // Propriedades do produto
    public $id;
    public $nome;
    public $descricao;
    public $preco;
    public $imagem;
    public $estoque;
    public $data_criacao;

    /**
     * Construtor
     * @param PDO $db - Conexão com o banco de dados
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Listar todos os produtos
     * @return array - Array com todos os produtos
     */
    public function listar() {
        try {
            $sql = "SELECT id, nome, descricao, preco, imagem, estoque, data_criacao 
                    FROM {$this->table} 
                    ORDER BY id ASC";
            
            $stmt = $this->conn->query($sql);
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'produtos' => $produtos,
                'total' => count($produtos)
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao listar produtos: ' . $e->getMessage(),
                'produtos' => []
            ];
        }
    }

    /**
     * Buscar produto por ID
     * @param int $id - ID do produto
     * @return array - Dados do produto ou null
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT id, nome, descricao, preco, imagem, estoque, data_criacao 
                    FROM {$this->table} 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($produto) {
                return [
                    'success' => true,
                    'produto' => $produto
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao buscar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar produtos por nome ou descrição
     * @param string $termo - Termo de busca
     * @return array - Array com produtos encontrados
     */
    public function buscarPorNome($termo) {
        try {
            $sql = "SELECT id, nome, descricao, preco, imagem, estoque, data_criacao 
                    FROM {$this->table} 
                    WHERE LOWER(nome) LIKE LOWER(:termo) 
                    OR LOWER(descricao) LIKE LOWER(:termo)
                    ORDER BY id ASC";
            
            $stmt = $this->conn->prepare($sql);
            $searchTerm = '%' . $termo . '%';
            $stmt->bindValue(':termo', $searchTerm);
            $stmt->execute();
            
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'produtos' => $produtos,
                'total' => count($produtos),
                'termo_busca' => $termo
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage(),
                'produtos' => []
            ];
        }
    }

    /**
     * Verificar se há estoque disponível
     * @param int $id - ID do produto
     * @param int $quantidade - Quantidade desejada
     * @return bool - true se há estoque, false caso contrário
     */
    public function verificarEstoque($id, $quantidade) {
        try {
            $sql = "SELECT estoque FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$produto) {
                return false;
            }
            
            return $produto['estoque'] >= $quantidade;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Atualizar estoque (reduzir após compra)
     * @param int $id - ID do produto
     * @param int $quantidade - Quantidade a reduzir
     * @return bool - true se sucesso, false caso contrário
     */
    public function atualizarEstoque($id, $quantidade) {
        try {
            // Primeiro verificar se há estoque suficiente
            if (!$this->verificarEstoque($id, $quantidade)) {
                return false;
            }

            $sql = "UPDATE {$this->table} 
                    SET estoque = estoque - :quantidade 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aumentar estoque (ao cancelar pedido)
     * @param int $id - ID do produto
     * @param int $quantidade - Quantidade a adicionar
     * @return bool - true se sucesso, false caso contrário
     */
    public function aumentarEstoque($id, $quantidade) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET estoque = estoque + :quantidade 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Criar novo produto (Admin)
     * @param array $dados - Dados do produto
     * @return array - Resultado da operação
     */
    public function criar($dados) {
        try {
            $sql = "INSERT INTO {$this->table} (nome, descricao, preco, imagem, estoque, data_criacao) 
                    VALUES (:nome, :descricao, :preco, :imagem, :estoque, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':preco', $dados['preco']);
            $stmt->bindValue(':imagem', $dados['imagem']);
            $stmt->bindValue(':estoque', $dados['estoque'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Produto criado com sucesso',
                    'id' => $this->conn->lastInsertId()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao criar produto'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao criar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Atualizar produto existente (Admin)
     * @param int $id - ID do produto
     * @param array $dados - Novos dados do produto
     * @return array - Resultado da operação
     */
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET nome = :nome, 
                        descricao = :descricao, 
                        preco = :preco, 
                        imagem = :imagem, 
                        estoque = :estoque 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':preco', $dados['preco']);
            $stmt->bindValue(':imagem', $dados['imagem']);
            $stmt->bindValue(':estoque', $dados['estoque'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Produto atualizado com sucesso'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao atualizar produto'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao atualizar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Deletar produto (Admin)
     * @param int $id - ID do produto
     * @return array - Resultado da operação
     */
    public function deletar($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Produto deletado com sucesso'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao deletar produto'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao deletar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Listar produtos em destaque (os mais vendidos ou com promoção)
     * @param int $limit - Quantidade de produtos a retornar
     * @return array - Array com produtos em destaque
     */
    public function listarDestaque($limit = 5) {
        try {
            $sql = "SELECT id, nome, descricao, preco, imagem, estoque 
                    FROM {$this->table} 
                    WHERE estoque > 0 
                    ORDER BY id DESC 
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'produtos' => $produtos
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao listar produtos em destaque: ' . $e->getMessage(),
                'produtos' => []
            ];
        }
    }

    /**
     * Obter estatísticas dos produtos
     * @return array - Estatísticas gerais
     */
    public function obterEstatisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_produtos,
                        SUM(estoque) as total_estoque,
                        AVG(preco) as preco_medio,
                        MIN(preco) as preco_minimo,
                        MAX(preco) as preco_maximo
                    FROM {$this->table}";
            
            $stmt = $this->conn->query($sql);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'estatisticas' => $stats
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar dados do produto
     * @param array $dados - Dados a validar
     * @return array - Resultado da validação
     */
    public function validarDados($dados) {
        $erros = [];

        if (empty($dados['nome']) || strlen($dados['nome']) < 3) {
            $erros[] = 'Nome deve ter no mínimo 3 caracteres';
        }

        if (empty($dados['preco']) || $dados['preco'] <= 0) {
            $erros[] = 'Preço deve ser maior que zero';
        }

        if (isset($dados['estoque']) && $dados['estoque'] < 0) {
            $erros[] = 'Estoque não pode ser negativo';
        }

        if (empty($erros)) {
            return ['valido' => true];
        }

        return [
            'valido' => false,
            'erros' => $erros
        ];
    }
}
