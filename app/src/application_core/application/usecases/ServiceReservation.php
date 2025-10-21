<?php 
namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\core\application\ports\api\dto\ReservationDTO;
use abricotdepot\core\application\ports\api\dto\RsvinputDTO;
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

    public function sauvegarderReservation(RsvinputDTO $reservationDTO): Reservation
    {
        $dateDebut = $reservationDTO->dateDebut instanceof \DateTime ? $reservationDTO->dateDebut : new \DateTime((string)$reservationDTO->dateDebut);
        $dateFin = $reservationDTO->dateFin instanceof \DateTime ? $reservationDTO->dateFin : new \DateTime((string)$reservationDTO->dateFin);

        $reservation = new Reservation(
            null,
            $reservationDTO->outilId,
            $reservationDTO->quantity,
            $dateDebut,
            $dateFin
        );
        $this->reservationRepository->sauvegarderReservation($reservation);
        return $reservation;
    }
}