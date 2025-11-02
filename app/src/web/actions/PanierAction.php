<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\web\helpers\TokenHelper;
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
        // Vérifier l'authentification et rafraîchir le token si nécessaire
        $userId = TokenHelper::getUserId($request);

        // Si l'utilisateur n'est pas connecté
        if (!$userId) {
            $file = __DIR__ . '/../../../public/html/index.html';
            $html = file_exists($file) ? file_get_contents($file) : '<h1>Erreur : template introuvable</h1>';
            $menu = (new GenerateMenuClasse())->generateMenu();
            $message = '<p><a href="/connexion">Vous devez vous connecter pour accéder à votre panier.</a></p>';
            $html = str_replace(['{{Menu}}', '{{Liste Outil}}'], [$menu, $message], $html);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html')->withStatus(401);
        }

        // Récupération du panier
        $panier = $this->panierRepository->getPanierItemsByUserId($userId);
        $itemsHtml = '';
        $total = 0.0;

        if (!empty($panier['items'])) {
            foreach ($panier['items'] as $item) {
                $sub = $item['prix'] * $item['quantity'];
                $total += $sub;

                $datedebut = urlencode($item['datedebut']);
                $datefin   = urlencode($item['datefin']);

                $itemsHtml .= '
                <div class="panier-item">
                    <img src="' . htmlspecialchars($item['image_url'] ?? '') . '" 
                        alt="Image de ' . htmlspecialchars($item['name']) . '" 
                        style="width:80px;height:80px;">
                    <div class="info">
                        <h3>' . htmlspecialchars($item['name']) . '</h3>
                        <p>' . htmlspecialchars($item['description']) . '</p>
                        <p>Du : ' . htmlspecialchars($item['datedebut']) . ' au ' . htmlspecialchars($item['datefin']) . '</p>
                        <p>Prix unitaire : ' . number_format($item['prix'], 2, ',', ' ') . ' €</p>
                        <p>Quantité : ' . intval($item['quantity']) . '</p>

                        <div class="actions" style="display:flex;gap:10px;align-items:center;">
                            <!-- Ajouter -->
                            <form method="POST" action="/panier/add/' . htmlspecialchars($item['outil_id']) . '/' . $datedebut . '/' . $datefin . '">
                                <button type="submit" class="btn-add" style="background:none;border:none;cursor:pointer;font-size:1.2em;">➕</button>
                            </form>

                            <!-- Retirer -->
                            <form method="POST" action="/panier/remove/' . htmlspecialchars($item['outil_id']) . '/' . $datedebut . '/' . $datefin . '">
                                <button type="submit" class="btn-remove" style="background:none;border:none;cursor:pointer;font-size:1.2em;">➖</button>
                            </form>

                            <!-- Supprimer complètement cet outil -->
                            <form method="POST" action="/panier/delete/' . htmlspecialchars($item['outil_id']) . '" 
                                onsubmit="return confirm(\'Supprimer cet article du panier ?\');">
                                <button type="submit" class="btn-delete" 
                                        style="background:none;border:none;color:red;cursor:pointer;font-size:1.2em;">🗑️</button>
                            </form>
                        </div>

                        <p>Sous-total : ' . number_format($sub, 2, ',', ' ') . ' €</p>
                    </div>
                </div>';
            }
        }



        // Injection dans le template principal (index.html)
        $file = __DIR__ . '/../../../public/html/accueil.html';
        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : template index introuvable');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);
        $menu = (new GenerateMenuClasse())->generateMenu();

        // Insertion du contenu
        $html = str_replace('{{Menu}}', $menu, $html);
        $html = str_replace('{{Liste Outil}}', '
            <main class="panier">
                <h2>Votre panier</h2>
                ' . $itemsHtml . '
            </main>
        ', $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
?>