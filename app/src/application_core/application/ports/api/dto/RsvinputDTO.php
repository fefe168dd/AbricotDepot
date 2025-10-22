<?php 

namespace abricotdepot\core\application\ports\api\dto;

class RsvinputDTO
{
    public string $outilId;

    public string $userId;
    public int $quantity;
    public \DateTime $dateDebut;
    public \DateTime $dateFin;

    public function __construct(string $outilId, string $userId, int $quantity, \DateTime $dateDebut, \DateTime $dateFin)
    {
        $this->outilId = $outilId;
        $this->userId = $userId;
        $this->quantity = $quantity;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }
}
