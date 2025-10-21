<?php 
namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\core\application\ports\api\dto\ReservationDTO;
use abricotdepot\core\domain\entities\Reservations\Reservation;

class ServiceReservation 
{
    private ReservationRepository $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function listerReservations(): array
    {
        $reservations = $this->reservationRepository->listerReservations();
        return array_map(fn($reservation) => new ReservationDTO($reservation), $reservations);
    }

    public function obtenirReservationParId(string $id): ?ReservationDTO
    {
        $reservation = $this->reservationRepository->ReservationParId($id);
        return $reservation ? new ReservationDTO($reservation) : null;
    }

    public function sauvegarderReservation(ReservationDTO $reservationDTO): void
    {
        $reservation = new Reservation(
            $reservationDTO->id,
            $reservationDTO->outilId,
            $reservationDTO->userId,
            new \DateTime($reservationDTO->startDate),
            new \DateTime($reservationDTO->endDate)
        );
        $this->reservationRepository->sauvegarderReservation($reservation);
    }
}