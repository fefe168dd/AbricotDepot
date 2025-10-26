<?php 
namespace abricotdepot\core\application\ports\spi\repositoryInterface;

use  abricotdepot\core\domain\entities\Reservations\Reservation;

interface ReservationRepository 
{
    public function listerReservations(): array;
    public function ReservationParId(string $id): ?Reservation;
    public function ReservationParOutilIdEtDate(string $id, \DateTime $dateDebut, \DateTime $dateFin): ?Reservation;
    public function ReservationParUserId(string $userId): array;
    public function sauvegarderReservation(Reservation $reservation): void;
    public function createReservation(string $userId, string $outilId, string $datedebut, string $datefin, int $quantity): void;
}
