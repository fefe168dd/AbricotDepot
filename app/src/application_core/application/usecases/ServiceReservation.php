<?php

namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\StockRepository;
use abricotdepot\core\application\ports\api\dto\ReservationDTO;
use abricotdepot\core\application\ports\api\dto\RsvinputDTO;
use abricotdepot\core\domain\entities\Reservations\Reservation;

class ServiceReservation
{
    private ReservationRepository $reservationRepository;
    private StockRepository $stockRepository;

    public function __construct(
        ReservationRepository $reservationRepository,
        StockRepository $stockRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->stockRepository = $stockRepository;
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

    public function obtenirReservationParOutilIdEtDate(string $id, \DateTime $dateDebut, \DateTime $dateFin): ?ReservationDTO
    {
        $reservation = $this->reservationRepository->ReservationParOutilIdEtDate($id, $dateDebut, $dateFin);
        return $reservation ? new ReservationDTO($reservation) : null;
    }


    public function sauvegarderReservation(RsvinputDTO $reservationDTO): Reservation
    {
        //Conversion sécurisée des dates
        $dateDebut = $reservationDTO->dateDebut instanceof \DateTime
            ? $reservationDTO->dateDebut
            : new \DateTime((string)$reservationDTO->dateDebut);

        $dateFin = $reservationDTO->dateFin instanceof \DateTime
            ? $reservationDTO->dateFin
            : new \DateTime((string)$reservationDTO->dateFin);

        //Vérification de la cohérence des dates
        if ($dateFin < $dateDebut) {
            throw new \InvalidArgumentException("La date de fin ne peut pas être antérieure à la date de début.");
        }

        //Vérification basique des champs essentiels
        if (empty($reservationDTO->outilId) || empty($reservationDTO->userId)) {
            throw new \InvalidArgumentException("L'outil et l'utilisateur doivent être spécifiés.");
        }

        if ($reservationDTO->quantity <= 0) {
            throw new \InvalidArgumentException("La quantité doit être supérieure à zéro.");
        }

        //Création de l'entité Reservation
        $reservation = new Reservation(
            \Ramsey\Uuid\Uuid::uuid4()->toString(),
            $reservationDTO->outilId,
            $reservationDTO->userId,
            $reservationDTO->quantity,
            $dateDebut,
            $dateFin,
            $reservationDTO->status ?? 'pending'
        );

        //Enregistrement en base via le repository
        $this->reservationRepository->sauvegarderReservation($reservation);

        return $reservation;
    }


    public function annulerReservation(string $reservationId): void
    {
        $reservation = $this->reservationRepository->ReservationParId($reservationId);

        if (!$reservation) {
            throw new \Exception("Réservation non trouvée");
        }

        $this->stockRepository->cancelStockReservation($reservationId);
    }

    public function terminerReservation(string $reservationId): void
    {
        $reservation = $this->reservationRepository->ReservationParId($reservationId);

        if (!$reservation) {
            throw new \Exception("Réservation non trouvée");
        }

        $this->stockRepository->completeStockReservation($reservationId);
    }
}
