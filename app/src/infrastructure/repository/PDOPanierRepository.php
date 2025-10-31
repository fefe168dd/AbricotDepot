<?php

namespace abricotdepot\infra\repository;

use Ramsey\Uuid\Uuid;
use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\core\domain\entities\Panier\Panier;

class PDOPanierRepository implements PanierRepository
{
    private \PDO $pdoPanier;
    private \PDO $pdoOutil;

    public function __construct(\PDO $pdoPanier, \PDO $pdoOutil)
    {
        $this->pdoPanier = $pdoPanier;
        $this->pdoOutil  = $pdoOutil;

        // Configuration de PDO
        foreach ([$this->pdoPanier, $this->pdoOutil] as $pdo) {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
    }

    public function getAllPaniers(): array
    {
        $stmt = $this->pdoPanier->query('SELECT * FROM panier');
        $rows = $stmt->fetchAll();

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

    /**
     * Sauvegarde un panier complet (utilisé rarement, par ex. migration)
     */
    public function savePanier(Panier $panier): void
    {
        $stmt = $this->pdoPanier->prepare('
            INSERT INTO panier (id, user_id, outil_id, quantity, datedebut, datefin)
            VALUES (:id, :user_id, :outil_id, :quantity, :datedebut, :datefin)
            ON CONFLICT (id) DO NOTHING
        ');

        $stmt->execute([
            ':id'         => $panier->getId(),
            ':user_id'    => $panier->getUserId(),
            ':outil_id'   => $panier->getOutilId(),
            ':quantity'   => $panier->getQuantity(),
            ':datedebut'  => $panier->getDateDebut()->format('Y-m-d H:i:s'),
            ':datefin'    => $panier->getDateFin()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ajoute un article dans le panier d’un utilisateur (ou augmente la quantité)
     */
    public function addItem(string $userId, string $outilId, int $quantity, ?string $datedebut = null, ?string $datefin = null): void
{
    // Si aucune date fournie, on met des valeurs par défaut
    $datedebut = $datedebut ?? date('Y-m-d 00:00:00');
    $datefin   = $datefin   ?? date('Y-m-d 23:59:59');

    // Vérifie si l’article existe déjà avec ces mêmes dates
    $select = $this->pdoPanier->prepare('
        SELECT id, quantity 
        FROM panier 
        WHERE user_id = :user_id 
          AND outil_id = :outil_id 
          AND datedebut = :datedebut 
          AND datefin = :datefin 
        LIMIT 1
    ');

    $select->execute([
        ':user_id'   => $userId,
        ':outil_id'  => $outilId,
        ':datedebut' => $datedebut,
        ':datefin'   => $datefin,
    ]);

    $row = $select->fetch();

    if ($row) {
        // Met à jour la quantité existante
        $newQty = (int)$row['quantity'] + $quantity;
        $upd = $this->pdoPanier->prepare('UPDATE panier SET quantity = :quantity WHERE id = :id');
        $upd->execute([':quantity' => $newQty, ':id' => $row['id']]);
    } else {
        // Ajoute une nouvelle ligne
        $id = Uuid::uuid4()->toString();
        $ins = $this->pdoPanier->prepare('
            INSERT INTO panier (id, user_id, outil_id, quantity, datedebut, datefin)
            VALUES (:id, :user_id, :outil_id, :quantity, :datedebut, :datefin)
        ');
        $ins->execute([
            ':id'         => $id,
            ':user_id'    => $userId,
            ':outil_id'   => $outilId,
            ':quantity'   => $quantity,
            ':datedebut'  => $datedebut,
            ':datefin'    => $datefin,
        ]);
    }
}


    /**
     * Retire un article (ou diminue la quantité) du panier
     */
    public function removeItem(string $userId, string $outilId, string $datedebut, string $datefin): void
        {
            $select = $this->pdoPanier->prepare('
                SELECT id, quantity 
                FROM panier 
                WHERE user_id = :user_id 
                AND outil_id = :outil_id
                AND datedebut = :datedebut
                AND datefin = :datefin
                LIMIT 1
            ');
            $select->execute([
                ':user_id'   => $userId,
                ':outil_id'  => $outilId,
                ':datedebut' => $datedebut,
                ':datefin'   => $datefin
            ]);

            $row = $select->fetch();

            if (!$row) return;

            if ($row['quantity'] > 1) {
                $upd = $this->pdoPanier->prepare('UPDATE panier SET quantity = quantity - 1 WHERE id = :id');
                $upd->execute([':id' => $row['id']]);
            } else {
                $del = $this->pdoPanier->prepare('DELETE FROM panier WHERE id = :id');
                $del->execute([':id' => $row['id']]);
            }
        }



    /**
     * Récupère un panier complet par son ID (rarement utilisé directement)
     */
    public function findById(string $id): ?Panier
    {
        $stmt = $this->pdoPanier->prepare('SELECT * FROM panier WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

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

    /**
     * Récupère tous les articles du panier d’un utilisateur
     */
    public function getPanierItemsByUserId(string $userId): array
    {
        $stmt = $this->pdoPanier->prepare('
            SELECT id, outil_id, quantity, datedebut, datefin 
            FROM panier 
            WHERE user_id = :user_id
        ');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            return ['panier_id' => $userId, 'items' => []];
        }

        // Récupère les outils liés
        $outilIds = array_column($rows, 'outil_id');
        $outilIds = array_unique($outilIds);

        $placeholders = implode(',', array_map(fn($i) => ":id{$i}", array_keys($outilIds)));
        $params = [];
        foreach ($outilIds as $i => $id) {
            $params[":id{$i}"] = $id;
        }

        $sql = 'SELECT id, name, description, prix, image_url FROM "outil" WHERE id IN (' . $placeholders . ')';
        $stmt2 = $this->pdoOutil->prepare($sql);
        $stmt2->execute($params);
        $outils = $stmt2->fetchAll();

        $outilsById = [];
        foreach ($outils as $o) {
            $outilsById[$o['id']] = $o;
        }

        // Fusionne les infos
        $items = [];
        foreach ($rows as $r) {
            $outil = $outilsById[$r['outil_id']] ?? null;
            $items[] = [
                'item_id'    => $r['id'],
                'outil_id'   => $r['outil_id'],
                'name'       => $outil['name'] ?? 'Outil inconnu',
                'description'=> $outil['description'] ?? '',
                'prix'       => isset($outil['prix']) ? (float)$outil['prix'] : 0.0,
                'image_url'  => $outil['image_url'] ?? '',
                'quantity'   => (int)$r['quantity'],
                'datedebut'  => $r['datedebut'],
                'datefin'    => $r['datefin'],
            ];
        }

        return ['panier_id' => $userId, 'items' => $items];
    }

    /**
     * Récupère les articles du panier pour un user + un outil spécifique
     */
    public function getPanierItemsByUserIdAndOutilId(string $userId, string $outilId): array
    {
        $stmt = $this->pdoPanier->prepare('
            SELECT * FROM panier 
            WHERE user_id = :user_id AND outil_id = :outil_id
        ');
        $stmt->execute([
            ':user_id'  => $userId,
            ':outil_id' => $outilId
        ]);

        $rows = $stmt->fetchAll();

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
    // Dans PDOPanierRepository
public function clearPanier(string $userId): void
{
    $stmt = $this->pdoPanier->prepare('DELETE FROM panier WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
}

}
