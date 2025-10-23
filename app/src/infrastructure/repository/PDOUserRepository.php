<?php

namespace abricotdepot\infra\repository;

use  abricotdepot\core\application\ports\spi\repositoryInterface\UserRepositoryInterface;

/**
 * Implémentation PDO du repository utilisateur
 */
class PDOUserRepository implements UserRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ?: null;
    }

    /**
     * Vérifie si un utilisateur existe par email
     */
    public function existsByEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Trouve un utilisateur par son pseudo
     */
    public function findByPseudo(string $pseudo): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE username = :pseudo');
        $stmt->execute(['pseudo' => $pseudo]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ?: null;
    }
}