<?php 
namespace App\core\application\ports\api\dto;

use App\core\domain\entities\Reservations\Reservation;
class ReservationDTO 
{
    public string $id;
    public string $outilId;
    public string $userId;
    public string $startDate;
    public string $endDate;

    public function __construct(Reservation $reservation)
    {
        $this->id = $reservation->getId();
        $this->outilId = $reservation->getOutilId();
        $this->userId = $reservation->getUserId();
        $this->startDate = $reservation->getDatedebut()->format('Y-m-d H:i:s');
        $this->endDate = $reservation->getDatefin()->format('Y-m-d H:i:s');
    }
}