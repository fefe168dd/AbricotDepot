<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;

class PanierAction
{
    private PanierRepository $panierRepository;

    public function __construct(PanierRepository $panierRepository)
    {
        $this->panierRepository = $panierRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        session_start();

        // ID de panier depuis session
        if (!isset($_SESSION['panier_id'])) {
            $_SESSION['panier_id'] = Uuid::uuid4()->toString();
        }
        $panierId = $_SESSION['panier_id'];

        $panier = $this->panierRepository->getPanierWithItemsByPanierId($panierId);

        $file = __DIR__ . '/../../../public/html/panier.html';
        if (!file_exists($file)) {
            $response->getBody()->write('Template panier introuvable');
            return $response->withStatus(500);
        }
        $html = file_get_contents($file);

        if (!$panier || empty($panier['items'])) {
            $html = str_replace('{{panier_items}}', '<p>Votre panier est vide.</p>', $html);
            $html = str_replace('{{panier_total}}', '0.00', $html);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        }

        $itemsHtml = '';
        $total = 0.0;
        foreach ($panier['items'] as $item) {
            $subTotal = $item['prix'] * $item['quantity'];
            $total += $subTotal;
            $itemsHtml .= '<div class="panier-item">';
            $itemsHtml .= '<img src="' . htmlspecialchars($item['image_url'] ?? '/Image/default.png') . '" alt="" style="width:80px;height:80px;">';
            $itemsHtml .= '<div class="info">';
            $itemsHtml .= '<h3>' . htmlspecialchars($item['name']) . '</h3>';
            $itemsHtml .= '<p>' . htmlspecialchars($item['description']) . '</p>';
            $itemsHtml .= '<p>Prix unitaire: ' . number_format($item['prix'], 2, ',', ' ') . ' €</p>';
            $itemsHtml .= '<p>Quantité: ' . intval($item['quantity']) . '</p>';
            $itemsHtml .= '<p>Sous-total: ' . number_format($subTotal, 2, ',', ' ') . ' €</p>';
            $itemsHtml .= '</div></div>';
        }

        $html = str_replace('{{panier_items}}', $itemsHtml, $html);
        $html = str_replace('{{panier_total}}', number_format($total, 2, ',', ' '), $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
