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
        $this->pdoOutil = $pdoOutil;
        $this->pdoPanier->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdoOutil->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdoPanier->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdoOutil->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }


    public function savePanier(Panier $panier): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO panier (id, user_id, outil_id, quantity, datedebut, datefin)
            VALUES (:id, :user_id, :outil_id, :quantity, :datedebut, :datefin)
            ON CONFLICT (id) DO NOTHING

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

    public function addItem(string $panierId, string $outilId, int $quantity, ?string $datedebut = null, ?string $datefin = null): void
    {
        $datedebut = $datedebut ?? date('Y-m-d 00:00:00');
        $datefin = $datefin ?? date('Y-m-d 23:59:59');

        $select = $this->pdoPanier->prepare('SELECT id, quantity FROM panier WHERE user_id = :user_id AND outil_id = :outil_id AND datedebut = :datedebut AND datefin = :datefin LIMIT 1');
        $select->execute([
            ':user_id' => $panierId,
            ':outil_id' => $outilId,
            ':datedebut' => $datedebut,
            ':datefin' => $datefin,
        ]);
        $row = $select->fetch();

        if ($row) {
            $newQty = (int) $row['quantity'] + $quantity;
            $upd = $this->pdoPanier->prepare('UPDATE panier SET quantity = :quantity WHERE id = :id');
            $upd->execute([':quantity' => $newQty, ':id' => $row['id']]);
            return;
        }

        $id = Uuid::uuid4()->toString();
        $ins = $this->pdoPanier->prepare('INSERT INTO panier (id, user_id, outil_id, quantity, datedebut, datefin) VALUES (:id, :user_id, :outil_id, :quantity, :datedebut, :datefin)');
        $ins->execute([
            ':id' => $id,
            ':user_id' => $panierId,
            ':outil_id' => $outilId,
            ':quantity' => $quantity,
            ':datedebut' => $datedebut,
            ':datefin' => $datefin,
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
                (int) $row['quantity'],
                new \DateTime($row['datedebut']),
                new \DateTime($row['datefin'])
            );
        }

        return null;
    }

    public function getPanierItemsByUserId(string $panierId): array
    {
        // read lines from panier DB
        $stmt = $this->pdoPanier->prepare('SELECT id, outil_id, quantity, datedebut, datefin FROM panier WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $panierId]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            return ['panier_id' => $panierId, 'items' => []];
        }

        // collect unique outil ids
        $outilIds = array_values(array_unique(array_map(fn($r) => $r['outil_id'], $rows)));
        if (empty($outilIds)) {
            return ['panier_id' => $panierId, 'items' => []];
        }

        // prepare IN placeholders and params
        $placeholders = [];
        $params = [];
        foreach ($outilIds as $i => $id) {
            $ph = ":id{$i}";
            $placeholders[] = $ph;
            $params[$ph] = $id;
        }

        // query the other DB (use quoted table name if your schema uses quotes)
        $sql = 'SELECT id, name, description, prix, image_url FROM "outil" WHERE id IN (' . implode(',', $placeholders) . ')';
        $stmt2 = $this->pdoOutil->prepare($sql);
        $stmt2->execute($params);
        $outils = $stmt2->fetchAll();

        // index outils by id
        $outilsById = [];
        foreach ($outils as $o) {
            $outilsById[$o['id']] = $o;
        }

        // combine
        $items = [];
        foreach ($rows as $r) {
            $outil = $outilsById[$r['outil_id']] ?? null;
            $items[] = [
                'item_id' => $r['id'],
                'outil_id' => $r['outil_id'],
                'name' => $outil['name'] ?? 'Outil inconnu',
                'description' => $outil['description'] ?? '',
                'prix' => isset($outil['prix']) ? (float) $outil['prix'] : 0.0,
                'image_url' => $outil['image_url'] ?? '',
                'quantity' => (int) $r['quantity'],
                'datedebut' => $r['datedebut'],
                'datefin' => $r['datefin'],
            ];
        }

        return ['panier_id' => $panierId, 'items' => $items];
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
                (int) $row['quantity'],
                new \DateTime($row['datedebut']),
                new \DateTime($row['datefin'])
            );
        }

        return $paniers;
    }
}