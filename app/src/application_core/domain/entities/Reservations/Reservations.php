<?php
namespace abricotdepot\core\domain\entities\Reservations;
use DateTime;

class Reservation
{
    private string $id;
    private string $userId;
    private string $outilId;
    private DateTime $datedebut;
    private DateTime $datefin;

    public function __construct(string $id, string $userId, string $outilId, DateTime $datedebut, DateTime $datefin)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->outilId = $outilId;
        $this->datedebut = $datedebut;
        $this->datefin = $datefin;
    }

    public function getId(): string
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

    public function getDatedebut(): DateTime
    {
        return $this->datedebut;
    }
    public function getDatefin(): DateTime
    {
        return $this->datefin;
    }
}
