<?php
namespace abricotdepot\infra\repository;

use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\core\domain\entities\Reservations\Reservation;
use Ramsey\Uuid\Uuid;
use DateTime;
use PDO;

class PDOReservationRepository implements ReservationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function createReservation(string $userId, string $outilId, string $startDate, string $endDate, int $quantity, int $status = Reservation::STATUS_PENDING): void
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo->prepare('
            INSERT INTO reservations (id, user_id, outil_id, start_date, end_date, quantity, status)
            VALUES (:id, :user_id, :outil_id, :start_date, :end_date, :quantity, :status)
        ');
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':outil_id' => $outilId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':quantity' => $quantity,
            ':status' => $status,
        ]);
    }

    public function sauvegarderReservation(Reservation $reservation): void
    {
        if ($reservation->getId()) {
            // update existing
            $stmt = $this->pdo->prepare('
                UPDATE reservations
                SET user_id = :user_id, outil_id = :outil_id, start_date = :start_date,
                    end_date = :end_date, quantity = :quantity, status = :status
                WHERE id = :id
            ');
            $stmt->execute([
                ':id' => $reservation->getId(),
                ':user_id' => $reservation->getUserId(),
                ':outil_id' => $reservation->getOutilId(),
                ':start_date' => $reservation->getDateDebut()->format('Y-m-d H:i:s'),
                ':end_date' => $reservation->getDateFin()->format('Y-m-d H:i:s'),
                ':quantity' => $reservation->getQuantity(),
                ':status' => $reservation->getStatus(),
            ]);
        } else {
            // insert new
            $this->createReservation(
                $reservation->getUserId(),
                $reservation->getOutilId(),
                $reservation->getDateDebut()->format('Y-m-d H:i:s'),
                $reservation->getDateFin()->format('Y-m-d H:i:s'),
                $reservation->getQuantity(),
                $reservation->getStatus()
            );
        }
    }

    public function listerReservations(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM reservations');
        $rows = $stmt->fetchAll();
        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = new Reservation(
                $row['id'],
                $row['outil_id'],
                $row['user_id'],
                (int)$row['quantity'],
                new DateTime($row['start_date']),
                new DateTime($row['end_date']),
                (int)$row['status']
            );
        }
        return $reservations;
    }

    public function ReservationParId(string $id): ?Reservation
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        return new Reservation(
            $row['id'],
            $row['outil_id'],
            $row['user_id'],
            (int)$row['quantity'],
            new DateTime($row['start_date']),
            new DateTime($row['end_date']),
            (int)$row['status']
        );
    }

    public function ReservationParOutilIdEtDate(string $outilId, DateTime $startDate, DateTime $endDate): ?Reservation
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM reservations
            WHERE outil_id = :outil_id
              AND start_date = :start_date
              AND end_date = :end_date
            LIMIT 1
        ');
        $stmt->execute([
            ':outil_id' => $outilId,
            ':start_date' => $startDate->format('Y-m-d H:i:s'),
            ':end_date' => $endDate->format('Y-m-d H:i:s')
        ]);
        $row = $stmt->fetch();
        if (!$row) return null;

        return new Reservation(
            $row['id'],
            $row['outil_id'],
            $row['user_id'],
            (int)$row['quantity'],
            new DateTime($row['start_date']),
            new DateTime($row['end_date']),
            (int)$row['status']
        );
    }

    public function ReservationParUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();
        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = new Reservation(
                $row['id'],
                $row['outil_id'],
                $row['user_id'],
                (int)$row['quantity'],
                new DateTime($row['start_date']),
                new DateTime($row['end_date']),
                (int)$row['status']
            );
        }
        return $reservations;
    }
}
