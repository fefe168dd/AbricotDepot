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
    )
    {
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

    public function sauvegarderReservation(RsvinputDTO $reservationDTO): Reservation
    {
        $dateDebut = $reservationDTO->dateDebut instanceof \DateTime ? $reservationDTO->dateDebut : new \DateTime((string)$reservationDTO->dateDebut);
        $dateFin = $reservationDTO->dateFin instanceof \DateTime ? $reservationDTO->dateFin : new \DateTime((string)$reservationDTO->dateFin);

        // Vérifier que le stock existe et est suffisant
        $stock = $this->stockRepository->StockParOutilId($reservationDTO->outilId);
        
        if (!$stock) {
            throw new \Exception("Aucun stock trouvé pour cet outil");
        }

        if (!$stock->hasEnoughStock($reservationDTO->quantity)) {
            throw new \Exception(
                "Stock insuffisant. Disponible: {$stock->getAvailable()}, Demandé: {$reservationDTO->quantity}"
            );
        }

        // Créer la réservation
        $reservation = new Reservation(
            null,
            $reservationDTO->outilId,
            $reservationDTO->quantity,
            $dateDebut,
            $dateFin
        );
        
        // Sauvegarder la réservation
        $this->reservationRepository->sauvegarderReservation($reservation);
        
        // Créer l'entrée dans stock_reservations (qui déclenche automatiquement la mise à jour du stock via trigger)
        try {
            $this->stockRepository->createStockReservation(
                $stock->getId(),
                $reservation->getId(),
                $reservationDTO->quantity,
                $dateDebut,
                $dateFin
            );
        } catch (\Exception $e) {
            // Si la création de la réservation de stock échoue, on pourrait vouloir annuler la réservation
            throw new \Exception("Erreur lors de la réservation du stock: " . $e->getMessage());
        }
        
        return $reservation;
    }

    public function annulerReservation(string $reservationId): void
    {
        $reservation = $this->reservationRepository->ReservationParId($reservationId);
        
        if (!$reservation) {
            throw new \Exception("Réservation non trouvée");
        }

        // Annuler la réservation de stock (le trigger mettra à jour automatiquement les quantités)
        $this->stockRepository->cancelStockReservation($reservationId);
    }

    public function terminerReservation(string $reservationId): void
    {
        $reservation = $this->reservationRepository->ReservationParId($reservationId);
        
        if (!$reservation) {
            throw new \Exception("Réservation non trouvée");
        }

        // Terminer la réservation de stock (le trigger mettra à jour automatiquement les quantités)
        $this->stockRepository->completeStockReservation($reservationId);
    }
}