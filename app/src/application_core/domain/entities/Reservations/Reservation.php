<?php
namespace abricotdepot\core\domain\entities\Reservations;
use DateTime;

class Reservation
{
    private ?string $id;
    private string $outilId;
    private string $userId;
    private int $quantity;
    private DateTime $datedebut;
    private DateTime $datefin;
    private int $status;
    public const STATUS_PENDING = 0;
    public const STATUS_CONFIRMED = 1;
    public const STATUS_CANCELED = 2;
    public function __construct(?string $id, string $outilId, string $userId, int $quantity, DateTime $datedebut, DateTime $datefin, int $status)
    {
        $this->id = $id;
        $this->outilId = $outilId;
        $this->userId = $userId;
        $this->quantity = $quantity;
        $this->datedebut = $datedebut;
        $this->datefin = $datefin;
        $this->status = $status;
        $this->status = $status;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOutilId(): string
    {
        return $this->outilId;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getDateDebut(): DateTime
    {
        return $this->datedebut;
    }
    
    public function getDateFin(): DateTime
    {
        return $this->datefin;
    }
    public function getStatus(): int
    {
        return $this->status;
    }
        public function setId(string $id): void
    {
        $this->id = $id;
    }
}


