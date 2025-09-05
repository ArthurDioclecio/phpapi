<?php

require_once 'Database.php';
require_once 'Pessoa.php';

class ModelPessoa {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    
    public function create(Pessoa $p): int {
        $sql = 'INSERT INTO pessoa (nome, cpf, telefone) VALUES (:nome, :cpf, :telefone)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $p->getNome());
        $stmt->bindValue(':cpf', $p->getCpf());
        $stmt->bindValue(':telefone', $p->getTelefone());

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    
    public function read(?int $id = null): array {
        if ($id !== null) {
            $stmt = $this->db->prepare('SELECT * FROM pessoa WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ? [$row] : [];
        }
        $stmt = $this->db->query('SELECT * FROM pessoa ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    
    public function update(Pessoa $p): bool {
        if ($p->getId() === null) {
            throw new InvalidArgumentException('ID é obrigatório para atualização.');
        }
        $sql = 'UPDATE pessoa SET nome = :nome, cpf = :cpf, telefone = :telefone WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $p->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $p->getNome());
        $stmt->bindValue(':cpf', $p->getCpf());
        $stmt->bindValue(':telefone', $p->getTelefone());

        return $stmt->execute();
    }

    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM pessoa WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}