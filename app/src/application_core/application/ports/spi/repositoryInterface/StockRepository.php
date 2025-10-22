<?php
namespace abricotdepot\core\application\ports\spi\repositoryInterface;

use abricotdepot\core\domain\entities\Stock\Stock;

interface StockRepository 
{
    public function listerStocks(): array;
    public function StockParId(string $id): ?Stock;
    public function StockParOutilId(string $outilId): ?Stock;
    public function updateStock(Stock $stock): void;
    public function createStockReservation(
        string $stockId, 
        string $reservationId, 
        int $quantity, 
        \DateTime $dateDebut, 
        \DateTime $dateFin
    ): string;
    public function cancelStockReservation(string $reservationId): void;
    public function completeStockReservation(string $reservationId): void;
}