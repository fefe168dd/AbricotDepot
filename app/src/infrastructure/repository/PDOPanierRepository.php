<?php

namespace abricotdepot\infra\repository;

use Ramsey\Uuid\Uuid;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\core\domain\entities\Panier\Panier;


class PDOPanierRepository implements PanierRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function savePanier(Panier $panier): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO panier (id, user_id, outil_id, quantity, datedebut, datefin)
            VALUES (:id, :user_id, :outil_id, :quantity, :datedebut, :datefin)
        ');

        $stmt->execute([
            ':id' => $panier->getId(),
            ':user_id' => $panier->getUserId(),
            ':outil_id' => $panier->getOutilId(),
            ':quantity' => $panier->getQuantity(),
            ':datedebut' => $panier->getDateDebut()->format('Y-m-d H:i:s'),
            ':datefin' => $panier->getDateFin()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?Panier
    {
        $stmt = $this->pdo->prepare('SELECT * FROM panier WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            return new Panier(
                $row['id'],
                $row['user_id'],
                $row['outil_id'],
                (int)$row['quantity'],
                new \DateTime($row['datedebut']),
                new \DateTime($row['datefin'])
            );
        }

        return null;
    }

    public function getPanierItemsByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM panier WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $paniers = [];
        foreach ($rows as $row) {
            $paniers[] = new Panier(
                $row['id'],
                $row['user_id'],
                $row['outil_id'],
                (int)$row['quantity'],
                new \DateTime($row['datedebut']),
                new \DateTime($row['datefin'])
            );
        }

        return $paniers;
    }

    public function getPanierItemsByUserIdAndOutilId(string $userId, string $outilId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM panier 
            WHERE user_id = :user_id AND outil_id = :outil_id
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':outil_id' => $outilId
        ]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $paniers = [];
        foreach ($rows as $row) {
            $paniers[] = new Panier(
                $row['id'],
                $row['user_id'],
                $row['outil_id'],
                (int)$row['quantity'],
                new \DateTime($row['datedebut']),
                new \DateTime($row['datefin'])
            );
        }

        return $paniers;
    }
}
