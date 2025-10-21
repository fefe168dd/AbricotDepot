<?php 
namespace abricotdepot\core\application\ports\api\dto;

use abricotdepot\core\domain\entities\Stock\Stock;
class StockDTO 
{
    public string $id;
    public string $outilId;
    public int $quantity;

    public function __construct(Stock $stock)
    {
        $this->id = $stock->getId();
        $this->outilId = $stock->getOutilId();
        $this->quantity = $stock->getQuantity();
    }
}