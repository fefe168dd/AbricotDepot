<?php
namespace abricotdepot\infra\repository;

use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\core\domain\entities\Reservations\Reservation;
use Ramsey\Uuid\Uuid;

class PDOReservationRepository implements ReservationRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    public function listerReservations(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM reservation');
        $rows = $stmt->fetchAll();

        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = $this->hydrateReservation($row);
        }

        return $reservations;
    }

    public function ReservationParId(string $id): ?Reservation
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservation WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrateReservation($row) : null;
    }

    public function ReservationParOutilIdEtDate(string $outilId, \DateTime $dateDebut, \DateTime $dateFin): ?Reservation
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM reservation
            WHERE outil_id = :outil_id
            AND NOT (datefin < :datedebut OR datedebut > :datefin)
            LIMIT 1
        ');
        $stmt->execute([
            ':outil_id' => $outilId,
            ':datedebut' => $dateDebut->format('Y-m-d H:i:s'),
            ':datefin' => $dateFin->format('Y-m-d H:i:s'),
        ]);
        $row = $stmt->fetch();

        return $row ? $this->hydrateReservation($row) : null;
    }

    public function ReservationParUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservation WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = $this->hydrateReservation($row);
        }

        return $reservations;
    }

    public function sauvegarderReservation(Reservation $reservation): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO reservations (id, user_id, outil_id, datedebut, datefin, quantity)
            VALUES (:id, :user_id, :outil_id, :datedebut, :datefin, :quantity)
        ');
        $stmt->execute([
            ':id' => $reservation->getId() ?? Uuid::uuid4()->toString(),
            ':user_id' => $reservation->getUserId(),
            ':outil_id' => $reservation->getOutilId(),
            ':datedebut' => $reservation->getDateDebut()->format('Y-m-d H:i:s'),
            ':datefin' => $reservation->getDateFin()->format('Y-m-d H:i:s'),
            ':quantity' => $reservation->getQuantity(),
        ]);
    }

    /**
     * 💡 Méthode supplémentaire demandée par ton interface
     */
    public function createReservation(string $userId, string $outilId, string $datedebut, string $datefin, int $quantity): void
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo->prepare('
            INSERT INTO reservation (id, user_id, outil_id, datedebut, datefin, quantity)
            VALUES (:id, :user_id, :outil_id, :datedebut, :datefin, :quantity)
        ');
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':outil_id' => $outilId,
            ':datedebut' => $datedebut,
            ':datefin' => $datefin,
            ':quantity' => $quantity,
        ]);
    }

    private function hydrateReservation(array $row): Reservation
    {
        return new Reservation(
            $row['id'],
            $row['user_id'],
            $row['outil_id'],
            (int) $row['quantity'],
            new \DateTime($row['datedebut']),
            new \DateTime($row['datefin'])
        );
    }
}
