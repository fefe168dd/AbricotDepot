<?php 
namespace abricotdepot\infra\repository;
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
        $sql = "SELECT o.id, o.name AS nom, o.description, o.prix, o.image_url, c.idcat, c.name AS categorie_name
                FROM outil o
                JOIN outil_categorie oc ON o.id = oc.outil_id
                JOIN categorie c ON oc.categorie_id = c.idcat";
        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $outils = [];
        foreach ($results as $row) {
            $categorie = new \abricotdepot\core\domain\entities\Outil\Categorie($row['idcat'], $row['categorie_name']);
            $outils[] = new Outil($row['id'], $row['nom'], $row['description'], (float)$row['prix'], $row['image_url'], $categorie);
        }
        return $outils;
    }

    public function OutilParId(string $id): ?Outil
    {
        $sql = "SELECT o.id, o.name AS nom, o.description, o.prix, o.image_url, c.idcat, c.name AS categorie_name
                FROM outil o
                JOIN outil_categorie oc ON o.id = oc.outil_id
                JOIN categorie c ON oc.categorie_id = c.idcat
                WHERE o.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $categorie = new \abricotdepot\core\domain\entities\Outil\Categorie($row['idcat'], $row['categorie_name']);
            return new Outil($row['id'], $row['nom'], $row['description'], (float)$row['prix'], $row['image_url'], $categorie);
        }
        return null;
    }

    public function OutilParCategorie(string $categoriename): array
    {
        $sql = "SELECT o.id, o.name AS nom, o.description, o.prix, o.image_url, c.idcat, c.name AS categorie_name
                FROM outil o
                JOIN outil_categorie oc ON o.id = oc.outil_id
                JOIN categorie c ON oc.categorie_id = c.idcat
                WHERE c.name = :categoriename";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':categoriename', $categoriename);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $outils = [];
        foreach ($results as $row) {
            $categorie = new \abricotdepot\core\domain\entities\Outil\Categorie($row['idcat'], $row['categorie_name']);
            $outils[] = new Outil($row['id'], $row['nom'], $row['description'], (float)$row['prix'], $row['image_url'], $categorie);
        }
        return $outils;
    }
}