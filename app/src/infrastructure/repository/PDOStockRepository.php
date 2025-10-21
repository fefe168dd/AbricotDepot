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
            $stocks[] = new Stock($data['id'], $data['outil_id'], (int)$data['quantity']);
        }
        return $stocks;
    }

    public function StockParId(string $id): ?Stock
    {
        $stmt = $this->connection->prepare('SELECT * FROM stock WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            return new Stock($data['id'], $data['outil_id'], (int)$data['quantity']);
        }
        return null;
    }

    public function StockParOutilId(string $outilId): ?Stock
    {
        $stmt = $this->connection->prepare('SELECT * FROM stock WHERE outil_id = :outilId');
        $stmt->execute(['outilId' => $outilId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            return new Stock($data['id'], $data['outil_id'], (int)$data['quantity']);
        }
        return null;
    }
}