<?php
namespace abricotdepot\core\application\ports\spi\repositoryInterface;

use abricotdepot\core\domain\entities\Stock\Stock;

interface StockRepository 
{
    public function listerStocks(): array;
    public function StockParId(string $id): ?Stock;
    public function StockParOutilId(string $outilId): ?Stock;
}