<?php

namespace abricotdepot\infra\repository;

use Ramsey\Uuid\Uuid;

use abricotdepot\core\domain\entities\Reservations\Reservation;
use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;

class PDOReservationRepository implements ReservationRepository
{
    private \PDO $pdo;
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function listerReservations(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM reservations");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = new Reservation(
                $row['id'],
                $row['utilisateur_id'] ?? $row['user_id'] ?? '',
                $row['outil_id'],
                $row['quantity'],
                new \DateTime($row['start_date']),
                new \DateTime($row['end_date']),
                $row ['status']
            );
        }
        return $reservations;
    }

    public function ReservationParId(string $id): ?Reservation
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            return new Reservation(
                $row['id'],
                $row['utilisateur_id'] ?? $row['user_id'] ?? '',
                $row['outil_id'],
                $row['quantity'],
                new \DateTime($row['start_date']),
                new \DateTime($row['end_date']),
                $row ['status']
            );
        }
        return null;
    }

    public function ReservationParOutilIdEtDate(string $id, \DateTime $dateDebut, \DateTime $dateFin): ?Reservation
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE outil_id = :id AND start_date <= :dateFin AND end_date >= :dateDebut;");
        $stmt->execute([
            ':id' => $id,
            ':dateDebut' => $dateDebut->format('Y-m-d H:i:s'),
            ':dateFin' => $dateFin->format('Y-m-d H:i:s')
        ]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = new Reservation(
                $row['id'],
                $row['outil_id'],
                $row['user_id'],
                $row['quantity'],
                new \DateTime($row['start_date']),
                new \DateTime($row['end_date']),
                $row ['status']
            );
        }
        return null;
    }


    public function sauvegarderReservation(Reservation $reservation): void
    {
        if ($reservation->getId() === null) {
            $reservation->setId(Uuid::uuid4()->toString());
        }
        $stmt = $this->pdo->prepare("INSERT INTO reservations (id, outil_id, start_date, end_date, quantity, status) VALUES (:id, :outil_id, :start_date, :end_date, :quantity, :status)");
        $stmt->bindValue(':id', $reservation->getId());
        $stmt->bindValue(':outil_id', $reservation->getOutilId());
        $stmt->bindValue(':start_date', $reservation->getDateDebut()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':end_date', $reservation->getDateFin()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':quantity', $reservation->getQuantity());
        $stmt->bindValue(':status', 0); // Par défaut, le statut est 0 (en attente)
        $stmt->execute();
    }
    //TODO: Ajouter d'autres méthodes par ID outil ou utilisateur si nécessaire
}
