<?php 
namespace App\core\application\ports\spi\repositoryInterface;

use  App\core\domain\entities\Reservations\Reservation;

interface ReservationRepository 
{
    public function listerReservations(): array;
    public function ReservationParId(string $id): ?Reservation;
    public function sauvegarderReservation(Reservation $reservation): void;
}
