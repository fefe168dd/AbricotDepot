<?php
namespace abricotdepot\infra\repository;
use abricotdepot\core\application\ports\spi\repositoryInterface\StockRepository;
use abricotdepot\core\domain\entities\Stock\Stock;
use PDO;

class PDOStockRepository implements StockRepository 
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function listerStocks(): array
    {
        $stmt = $this->connection->query('SELECT * FROM stock');
        $stocksData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stocks = [];
        foreach ($stocksData as $data) {
            $stocks[] = new Stock(
                $data['id'], 
                $data['outil_id'], 
                (int)$data['quantity'],
                (int)($data['quantity_reserved'] ?? 0),
                (int)($data['available'] ?? 0)
            );
        }
        return $stocks;
    }

    public function StockParId(string $id): ?Stock
    {
        $stmt = $this->connection->prepare('SELECT * FROM stock WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            return new Stock(
                $data['id'], 
                $data['outil_id'], 
                (int)$data['quantity'],
                (int)($data['quantity_reserved'] ?? 0),
                (int)($data['available'] ?? 0)
            );
        }
        return null;
    }

    public function StockParOutilId(string $outilId): ?Stock
    {
        $stmt = $this->connection->prepare('SELECT * FROM stock WHERE outil_id = :outilId');
        $stmt->execute(['outilId' => $outilId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            return new Stock(
                $data['id'], 
                $data['outil_id'], 
                (int)$data['quantity'],
                (int)($data['quantity_reserved'] ?? 0),
                (int)($data['available'] ?? 0)
            );
        }
        return null;
    }

    public function updateStock(Stock $stock): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE stock 
             SET quantity = :quantity, 
                 quantity_reserved = :quantity_reserved 
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $stock->getId(),
            'quantity' => $stock->getQuantity(),
            'quantity_reserved' => $stock->getQuantityReserved()
        ]);
    }

    public function createStockReservation(
        string $stockId, 
        string $reservationId, 
        int $quantity, 
        \DateTime $dateDebut, 
        \DateTime $dateFin
    ): string {
        // Démarrer une transaction pour garantir la cohérence
        $this->connection->beginTransaction();
        
        try {
            // Vérifier que le stock est suffisant avec un verrou
            $stmt = $this->connection->prepare(
                'SELECT id, quantity, quantity_reserved, available 
                 FROM stock 
                 WHERE id = :stock_id 
                 FOR UPDATE'
            );
            $stmt->execute(['stock_id' => $stockId]);
            $stock = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$stock) {
                throw new \Exception("Stock non trouvé");
            }
            
            if ($stock['available'] < $quantity) {
                throw new \Exception(
                    "Stock insuffisant. Disponible: {$stock['available']}, Demandé: {$quantity}"
                );
            }
            
            // Mettre à jour les quantités du stock
            $newQuantityReserved = $stock['quantity_reserved'] + $quantity;
            $newAvailable = $stock['quantity'] - $newQuantityReserved;
            
            $updateStmt = $this->connection->prepare(
                'UPDATE stock 
                 SET quantity_reserved = :quantity_reserved,
                     available = :available
                 WHERE id = :stock_id'
            );
            $updateStmt->execute([
                'quantity_reserved' => $newQuantityReserved,
                'available' => $newAvailable,
                'stock_id' => $stockId
            ]);
            
            // Créer l'entrée dans stock_reservations
            $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $stmt = $this->connection->prepare(
                'INSERT INTO stock_reservations 
                 (id, stock_id, order_id, quantity, datedebut, datefin) 
                 VALUES (:id, :stock_id, :order_id, :quantity, :datedebut, :datefin)'
            );
            $stmt->execute([
                'id' => $id,
                'stock_id' => $stockId,
                'order_id' => $reservationId,
                'quantity' => $quantity,
                'datedebut' => $dateDebut->format('Y-m-d H:i:s'),
                'datefin' => $dateFin->format('Y-m-d H:i:s')
            ]);
            
            $this->connection->commit();
            return $id;
            
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function cancelStockReservation(string $reservationId): void
    {
        $this->connection->beginTransaction();
        
        try {
            // Récupérer les informations de la réservation
            $stmt = $this->connection->prepare(
                'SELECT sr.stock_id, sr.quantity 
                 FROM stock_reservations sr
                 WHERE sr.order_id = :reservation_id'
            );
            $stmt->execute(['reservation_id' => $reservationId]);
            $reservation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($reservation) {
                // Libérer le stock
                $stmt = $this->connection->prepare(
                    'UPDATE stock 
                     SET quantity_reserved = quantity_reserved - :quantity,
                         available = available + :quantity
                     WHERE id = :stock_id'
                );
                $stmt->execute([
                    'quantity' => $reservation['quantity'],
                    'stock_id' => $reservation['stock_id']
                ]);
                
                // Supprimer ou marquer la réservation
                $stmt = $this->connection->prepare(
                    'DELETE FROM stock_reservations 
                     WHERE order_id = :reservation_id'
                );
                $stmt->execute(['reservation_id' => $reservationId]);
            }
            
            $this->connection->commit();
            
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function completeStockReservation(string $reservationId): void
    {
        $this->connection->beginTransaction();
        
        try {
            // Récupérer les informations de la réservation
            $stmt = $this->connection->prepare(
                'SELECT sr.stock_id, sr.quantity 
                 FROM stock_reservations sr
                 WHERE sr.order_id = :reservation_id'
            );
            $stmt->execute(['reservation_id' => $reservationId]);
            $reservation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($reservation) {
                // Libérer le stock (la réservation est terminée)
                $stmt = $this->connection->prepare(
                    'UPDATE stock 
                     SET quantity_reserved = quantity_reserved - :quantity,
                         available = available + :quantity
                     WHERE id = :stock_id'
                );
                $stmt->execute([
                    'quantity' => $reservation['quantity'],
                    'stock_id' => $reservation['stock_id']
                ]);
                
                // Supprimer la réservation
                $stmt = $this->connection->prepare(
                    'DELETE FROM stock_reservations 
                     WHERE order_id = :reservation_id'
                );
                $stmt->execute(['reservation_id' => $reservationId]);
            }
            
            $this->connection->commit();
            
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}