<?php
namespace abricotdepot\core\domain\entities\Reservations;
use DateTime;

class Reservation
{
    private ?string $id;
    private string $outilId;
    private int $quantity;
    private DateTime $datedebut;
    private DateTime $datefin;

    public function __construct(?string $id, string $outilId, int $quantity, DateTime $datedebut, DateTime $datefin)
    {
        $this->id = $id;
        $this->outilId = $outilId;
        $this->quantity = $quantity;
        $this->datedebut = $datedebut;
        $this->datefin = $datefin;
    }

    public function getId(): ?string
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

    public function getDatedebut(): DateTime
    {
        return $this->datedebut;
    }
    public function getDatefin(): DateTime
    {
        return $this->datefin;
    }
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
