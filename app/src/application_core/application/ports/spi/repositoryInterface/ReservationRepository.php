<?php 
namespace abricotdepot\core\application\ports\spi\repositoryInterface;

use  abricotdepot\core\domain\entities\Reservations\Reservation;

interface ReservationRepository 
{
    public function listerReservations(): array;
    public function ReservationParId(string $id): ?Reservation;
    public function sauvegarderReservation(Reservation $reservation): void;
}
