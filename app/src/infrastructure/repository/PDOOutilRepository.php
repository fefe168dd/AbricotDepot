<?php 
namespace abricotdepot\infra\repository;
use Ramsey\Uuid\Uuid;

use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;
use abricotdepot\core\domain\entities\Outil\Outil;

class PDOOutilRepository implements OutilRepository 
{
    private \PDO $pdo;
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function listerOutils(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM outils");
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Outil::class);
    }

    public function OutilParId(string $id): ?Outil
    {
        $stmt = $this->pdo->prepare("SELECT * FROM outils WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $outil = $stmt->fetchObject(Outil::class);
        return $outil ?: null;
    }

    public function OutilParCategorie(string $categoriename): array
    {
        $stmt = $this->pdo->prepare("SELECT o.* FROM outils o JOIN categories c ON o.categorie_id = c.id WHERE c.nom = :categoriename");
        $stmt->bindParam(':categoriename', $categoriename);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Outil::class);
    }

    }
