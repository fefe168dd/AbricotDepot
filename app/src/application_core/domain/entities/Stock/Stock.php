<?php

namespace abricotdepot\core\domain\entities\Stock;

class Stock
{
    private string $id;
    private string $outilId;
    private int $quantity;
    private int $quantityReserved;
    private int $available;

    public function __construct(
        string $id, 
        string $outilId, 
        int $quantity,
        int $quantityReserved = 0,
        int $available = 0
    )
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

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setQuantityReserved(int $quantityReserved): void
    {
        $this->quantityReserved = $quantityReserved;
    }

    public function setAvailable(int $available): void
    {
        $this->available = $available;
    }

    public function hasEnoughStock(int $requestedQuantity): bool
    {
        return $this->available >= $requestedQuantity;
    }
}