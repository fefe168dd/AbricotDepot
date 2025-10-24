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
        $cookies = $request->getCookieParams();
        $panierId = $cookies['panier_id'] ?? null;
        $setCookieHeader = null;

        if (!$panierId) {
            // génère cookie guest, ne crée pas de ligne en base
            $panierId = Uuid::uuid4()->toString();
            $setCookieHeader = 'panier_id=' . $panierId . '; Path=/; HttpOnly';
        }

        $panier = $this->panierRepository->getPanierItemsByUserId($panierId);

        $file = __DIR__ . '/../../../public/html/panier.html';
        if (!file_exists($file)) {
            $response->getBody()->write('Template panier introuvable');
            return $response->withStatus(500);
        }
        $html = file_get_contents($file);

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
                $itemsHtml .= '<p>Sous-total: ' . number_format($sub, 2, ',', ' ') . ' €</p>';
                $itemsHtml .= '</div></div>';
            }
        } else {
            $itemsHtml = '<p>Votre panier est vide.</p>';
        }

        $html = str_replace('{{panier_items}}', $itemsHtml, $html);
        $html = str_replace('{{panier_total}}', number_format($total, 2, ',', ' '), $html);

        $response->getBody()->write($html);
        $response = $response->withHeader('Content-Type', 'text/html');
        if ($setCookieHeader) {
            $response = $response->withHeader('Set-Cookie', $setCookieHeader);
        }
        return $response;
    }
}