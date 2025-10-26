<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PanierAction
{
    private PanierRepository $panierRepository;

    public function __construct(PanierRepository $panierRepository)
    {
        $this->panierRepository = $panierRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $cookies = $request->getCookieParams();

        // Vérifie la présence du token et du user_id
        $token = $cookies['access_token'] ?? null;
        $userId = $cookies['user_id'] ?? null;

        if (!$token || !$userId) {
            // L'utilisateur n'est pas connecté → message d’erreur
            $response->getBody()->write('<a href="/connexion">Vous devez vous connecter pour accéder à votre panier.</a>');
            return $response->withHeader('Content-Type', 'text/html')->withStatus(401);
        }

        // Récupère les articles du panier de l’utilisateur connecté
        $panier = $this->panierRepository->getPanierItemsByUserId($userId);

        // Vérifie que le template HTML existe
        $file = __DIR__ . '/../../../public/html/panier.html';
        if (!file_exists($file)) {
            $response->getBody()->write('Template panier introuvable');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);

        // Construction de la section des items
        $itemsHtml = '';
        $total = 0.0;

        if (!empty($panier['items'])) {
            foreach ($panier['items'] as $item) {
                $sub = $item['prix'] * $item['quantity'];
                $total += $sub;

                $itemsHtml .= '<div class="panier-item">';
                $itemsHtml .= '<img src="' . htmlspecialchars($item['image_url'] ?? '') . '" style="width:80px;height:80px;">';
                $itemsHtml .= '<div class="info">';
                $itemsHtml .= '<h3>' . htmlspecialchars($item['name']) . '</h3>';
                $itemsHtml .= '<p>' . htmlspecialchars($item['description']) . '</p>';
                $itemsHtml .= '<p>Du: ' . htmlspecialchars($item['datedebut']) . ' au ' . htmlspecialchars($item['datefin']) . '</p>';
                $itemsHtml .= '<p>Prix unitaire: ' . number_format($item['prix'], 2, ',', ' ') . ' €</p>';
                $itemsHtml .= '<p>Quantité: ' . intval($item['quantity']) . '</p>';
                $itemsHtml .= '<div class="actions">';
                $itemsHtml .= '<a href="/panier/add/' . htmlspecialchars($item['outil_id']) . '" class="btn-add">➕</a>';
                $itemsHtml .= '<a href="/panier/remove/' . htmlspecialchars($item['outil_id']) . '" class="btn-remove">➖</a>';
                $itemsHtml .= '</div>';
                $itemsHtml .= '<p>Sous-total: ' . number_format($sub, 2, ',', ' ') . ' €</p>';
                $itemsHtml .= '</div></div>';
            }
        } else {
            $itemsHtml = '<p>Votre panier est vide.</p>';
        }

        $itemsHtml .= '<div class="panier-footer">';
        $itemsHtml .= '<p class="total">Total : ' . number_format($total, 2, ',', ' ') . ' €</p>';
        $itemsHtml .= '<div class="actions">';
        $itemsHtml .= '<form method="POST" action="/panier/reserver">';
        $itemsHtml .= '<button type="submit" class="btn-reserver">Réserver</button>';
        $itemsHtml .= '</form>';
        $itemsHtml .= '</div>';
        $itemsHtml .= '</div>';

        // Remplace les placeholders dans le template
        $html = str_replace('{{panier_items}}', $itemsHtml, $html);
        $html = str_replace('{{panier_total}}', number_format($total, 2, ',', ' '), $html);

        // Écrit la réponse finale
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
