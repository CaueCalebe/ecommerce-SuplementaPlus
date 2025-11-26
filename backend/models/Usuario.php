<?php
class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrar($email, $senha) {
        try {
            // Verifica se email já existe
            $sql = "SELECT id FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Este email já está registrado'];
            }

            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir usuário
            $sql = "INSERT INTO {$this->table} (email, senha, data_criacao) VALUES (:email, :senha, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha', $senha_hash);
            $stmt->execute();

            return ['success' => true, 'message' => 'Conta criada com sucesso!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()];
        }
    }

    public function login($email, $senha) {
        try {
            $sql = "SELECT id, email, senha FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Email ou senha incorretos'];
            }

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($senha, $usuario['senha'])) {
                return ['success' => false, 'message' => 'Email ou senha incorretos'];
            }

            return [
                'success' => true,
                'message' => 'Login realizado com sucesso!',
                'user' => [
                    'id' => $usuario['id'],
                    'email' => $usuario['email']
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erro ao fazer login: ' . $e->getMessage()];
        }
    }

    public function buscarPorId($id) {
        try {
            $sql = "SELECT id, email, data_criacao FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
