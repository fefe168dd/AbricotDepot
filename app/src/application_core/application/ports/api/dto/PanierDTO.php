<?php 
namespace abricotdepot\core\application\ports\api\dto;

use abricotdepot\core\domain\entities\Panier\Panier;

class PanierDTO 
{
    public string $id;
    public string $userId;
    public string $outilId;
    public int $quantity;
    public \DateTime $dateDebut;
    public \DateTime $dateFin;

    public function __construct(Panier $Panier)
    {
        $this->id = $Panier->getId();
        $this->userId = $Panier->getUserId();
        $this->outilId = $Panier->getOutilId();
        $this->quantity = $Panier->getQuantity();
        $this->dateDebut = $Panier->getDateDebut();
        $this->dateFin = $Panier->getDateFin();
    }
}