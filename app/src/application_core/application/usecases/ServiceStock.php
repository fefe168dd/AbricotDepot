<?php
namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\StockRepository;
use abricotdepot\core\application\ports\api\dto\StockDTO;

class ServiceStock 
{
    private StockRepository $stockRepository;

    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    public function listerStocks(): array
    {
        $stocks = $this->stockRepository->listerStocks();
        return array_map(fn($stock) => new StockDTO($stock), $stocks);
    }

    public function obtenirStockParId(string $id): ?StockDTO
    {
        $stock = $this->stockRepository->StockParId($id);
        return $stock ? new StockDTO($stock) : null;
    }

    public function obtenirStockParOutilId(string $outilId): ?StockDTO
    {
        $stock = $this->stockRepository->StockParOutilId($outilId);
        return $stock ? new StockDTO($stock) : null;
    }
}