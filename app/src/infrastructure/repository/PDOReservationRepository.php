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
                new \DateTime($row['date_debut'] ?? $row['start_date']),
                new \DateTime($row['date_fin'] ?? $row['end_date'])
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
                new \DateTime($row['date_debut'] ?? $row['start_date']),
                new \DateTime($row['date_fin'] ?? $row['end_date'])
            );
        }
        return null;
    }

    public function sauvegarderReservation(Reservation $reservation): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO reservations (id, outil_id, utilisateur_id, date_debut, date_fin) VALUES (:id, :outil_id, :utilisateur_id, :date_debut, :date_fin)");
        $stmt->bindParam(':id', $reservation->getId());
        $stmt->bindParam(':outil_id', $reservation->getOutilId());
        $stmt->bindParam(':utilisateur_id', $reservation->getUserId());
        $stmt->bindParam(':date_debut', $reservation->getDateDebut()->format('Y-m-d H:i:s'));
        $stmt->bindParam(':date_fin', $reservation->getDateFin()->format('Y-m-d H:i:s'));
        $stmt->execute();
    }
    //TODO: Ajouter d'autres méthodes par ID outil ou utilisateur si nécessaire
}