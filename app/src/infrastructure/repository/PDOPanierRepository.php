<?php
namespace abricotdepot\infra\repository;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\core\domain\entities\Panier\Panier;


class PDOPanierRepository implements PanierRepository 
{
    private \PDO $pdoPanier;
private \PDO $pdoOutil;

public function __construct(\PDO $pdoPanier, \PDO $pdoOutil)
{
    $this->pdoPanier = $pdoPanier;
    $this->pdoOutil = $pdoOutil;

    $this->pdoPanier->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->pdoOutil->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
}

    public function save(Panier $panier): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO panier (id, user_id) VALUES (:id, :user_id)');
        $stmt->execute([
            ':id' => $panier->getIdPanier(),
            ':user_id' => $panier->getUserId()
        ]);
    }

    public function findById(string $id): ?Panier
    {
        $stmt = $this->pdo->prepare('SELECT * FROM panier WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            return new Panier($row['id'], $row['user_id']);
        }

        return null;
    }

    public function getPanierWithItemsByPanierId(string $panierId): array
{
    // 1️⃣ Récupérer les items dans la base panier
    $items = $this->pdoPanier->query("SELECT * FROM panier_item WHERE panier_id = '$panierId'")->fetchAll();

    // 2️⃣ Pour chaque item, récupérer les infos de l'outil depuis la base outil
    foreach ($items as &$item) {
        $outilStmt = $this->pdoOutil->prepare("SELECT * FROM outil WHERE id = :id");
        $outilStmt->execute([':id' => $item['outil_id']]);
        $outil = $outilStmt->fetch();

        if (!$outil) continue;

        $item['name'] = $outil['name'];
        $item['description'] = $outil['description'];
        $item['prix'] = (float)$outil['prix'];
        $item['image_url'] = $outil['image_url'];
    }

    return [
        'panier_id' => $panierId,
        'items' => $items
    ];
}

    public function addItem(string $panierId, int $outilId, int $quantity = 1): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO panier_item (panier_id, outil_id, quantity)
            VALUES (:panier_id, :outil_id, :quantity)
            ON DUPLICATE KEY UPDATE quantity = quantity + :quantity
        ");
        $stmt->execute([
            ':panier_id' => $panierId,
            ':outil_id' => $outilId,
            ':quantity' => $quantity
        ]);
    }
}
