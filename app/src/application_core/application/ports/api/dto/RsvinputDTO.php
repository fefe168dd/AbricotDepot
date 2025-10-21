<?php 

namespace abricotdepot\core\application\ports\api\dto;

class RsvinputDTO
{
    public string $outilId;
    public int $quantity;
    public \DateTime $dateDebut;
    public \DateTime $dateFin;

    public function __construct(string $outilId, int $quantity, \DateTime $dateDebut, \DateTime $dateFin)
    {
        $this->outilId = $outilId;
        $this->quantity = $quantity;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }
}
