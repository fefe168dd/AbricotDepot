<?php

namespace App\core\domain\entities\Stock;

class Stock
{
    private string $id;
    private string $outilId;
    private int $quantity;

    public function __construct(string $id, string $outilId, int $quantity)
    {
        $this->id = $id;
        $this->outilId = $outilId;
        $this->quantity = $quantity;
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
}