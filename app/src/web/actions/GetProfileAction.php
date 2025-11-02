<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;
use abricotdepot\web\helpers\TokenHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetProfileAction
{
    private ReservationRepository $reservationRepository;
    private OutilRepository $outilRepository;

    public function __construct(ReservationRepository $reservationRepository, OutilRepository $outilRepository)
    {
        $this->reservationRepository = $reservationRepository;
        $this->outilRepository = $outilRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Vérifier l'authentification et rafraîchir le token si nécessaire
        $userId = TokenHelper::getUserId($request);

        // Vérifier si l'utilisateur est connecté
        if (!$userId) {
            return $response
                ->withHeader('Location', '/connexion')
                ->withStatus(302);
        }

        // Récupérer l'email de l'utilisateur (on peut l'obtenir via une API ou depuis le token)
        // Pour l'instant, on utilise juste l'ID
        $userEmail = "Utilisateur #" . $userId;

        // Récupérer les réservations de l'utilisateur
        $reservations = $this->reservationRepository->ReservationParUserId($userId);

        // Charger le template HTML
        $file = __DIR__ . '/../../../public/html/profile.html';
        if (!file_exists($file)) {
            $response->getBody()->write('Template profil introuvable');
            return $response->withStatus(500);
        }
        $html = file_get_contents($file);

        // Générer le HTML des réservations
        $reservationsHtml = '';
        if (!empty($reservations)) {
            foreach ($reservations as $reservation) {
                // Récupérer les informations de l'outil
                $outil = $this->outilRepository->OutilParId($reservation->getOutilId());
                
                if ($outil) {
                    // Déterminer le statut
                    $statusClass = '';
                    $statusText = '';
                    switch ($reservation->getStatus()) {
                        case 0:
                            $statusClass = 'status-pending';
                            $statusText = 'En attente';
                            break;
                        case 1:
                            $statusClass = 'status-confirmed';
                            $statusText = 'Confirmée';
                            break;
                        case 2:
                            $statusClass = 'status-active';
                            $statusText = 'En cours';
                            break;
                        case 3:
                            $statusClass = 'status-completed';
                            $statusText = 'Terminée';
                            break;
                        case 4:
                            $statusClass = 'status-cancelled';
                            $statusText = 'Annulée';
                            break;
                        default:
                            $statusClass = 'status-pending';
                            $statusText = 'Inconnu';
                    }

                    $reservationsHtml .= '<div class="reservation-item">';
                    $reservationsHtml .= '<div class="reservation-header">';
                    $reservationsHtml .= '<h3>' . htmlspecialchars($outil->getNom()) . '</h3>';
                    $reservationsHtml .= '<span class="status-badge ' . $statusClass . '">' . $statusText . '</span>';
                    $reservationsHtml .= '</div>';
                    $reservationsHtml .= '<div class="reservation-details">';
                    $reservationsHtml .= '<p><strong>Description:</strong> ' . htmlspecialchars($outil->getDescription()) . '</p>';
                    $reservationsHtml .= '<p><strong>Quantité:</strong> ' . intval($reservation->getQuantity()) . '</p>';
                    $reservationsHtml .= '<p><strong>Date de début:</strong> ' . $reservation->getDateDebut()->format('d/m/Y H:i') . '</p>';
                    $reservationsHtml .= '<p><strong>Date de fin:</strong> ' . $reservation->getDateFin()->format('d/m/Y H:i') . '</p>';
                    $reservationsHtml .= '<p><strong>Prix unitaire:</strong> ' . number_format($outil->getPrix(), 2, ',', ' ') . ' €</p>';
                    $total = $outil->getPrix() * $reservation->getQuantity();
                    $reservationsHtml .= '<p><strong>Total:</strong> ' . number_format($total, 2, ',', ' ') . ' €</p>';
                    $reservationsHtml .= '</div>';
                    $reservationsHtml .= '</div>';
                }
            }
        } else {
            $reservationsHtml = '<div class="no-reservations">Vous n\'avez aucune réservation pour le moment.</div>';
        }

        // Remplacer les placeholders dans le HTML
        $html = str_replace('{{user_email}}', htmlspecialchars($userEmail), $html);
        $html = str_replace('{{reservations}}', $reservationsHtml, $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
