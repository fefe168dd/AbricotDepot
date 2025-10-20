<?php

namespace pp\core\domain\entities\Stock;

class StockDisponible
{
    private string $id;
    private string $outilId;
    private int $quantity;
    private int $quantityReserved;
    private int $available;

    public function __construct(string $id, string $outilId, int $quantity, int $quantityReserved, int $available  )
    {
        $this->id = $id;
        $this->outilId = $outilId;
        $this->quantity = $quantity;
        $this->quantityReserved = $quantityReserved;
        $this->available = $available;
    }
    public function getId(): string
    {
        return $this->id;
    }

    public function getOutilId(): string
    {
        return $this->outilId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
    public function getQuantityReserved(): int
    {
        return $this->quantityReserved;
    }
    public function getAvailable(): int
    {
        return $this->available;
    }
}
