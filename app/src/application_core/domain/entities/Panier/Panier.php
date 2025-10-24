<?php
namespace abricotdepot\core\domain\entities\Panier;

class Panier
{
    private string $id;
    private string $userId;
    private string $outilId;
    private int $quantity;
    private \DateTime $dateDebut;
    private \DateTime $dateFin;

    public function __construct(
        string $id,
        string $userId,
        string $outilId,
        int $quantity,
        \DateTime $dateDebut,
        \DateTime $dateFin
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->outilId = $outilId;
        $this->quantity = $quantity;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function getPanierId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getOutilId(): string
    {
        return $this->outilId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getDateDebut(): \DateTime
    {
        return $this->dateDebut;
    }

    public function getDateFin(): \DateTime
    {
        return $this->dateFin;
    }
}
