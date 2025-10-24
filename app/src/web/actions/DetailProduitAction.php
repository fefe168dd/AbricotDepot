<?php
namespace abricotdepot\web\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DetailProduitAction
{
    public function __construct()
    {

    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $token = $_SESSION['accessToken'] ?? null;

        $outilId = $args['id'] ?? null;

        if (!$outilId) {
            $response->getBody()->write(json_encode(['error' => 'ID manquant']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $apiUrl = "http://apicot:80/outils/$outilId";

        $json = file_get_contents($apiUrl);

        $outil = json_decode($json, true);

        $apiUrlStock = "http://apicot:80/outils/$outilId/stocks";

        $json = file_get_contents($apiUrlStock);

        $stockJson = json_decode($json, true);

        $stock = htmlspecialchars($stockJson['quantity']);

        if (!$outil) {
            $response->getBody()->write('Outil introuvable');
            return $response->withStatus(404);
        }

        if (!$stock) {
            $response->getBody()->write('Stock introuvable');
            return $response->withStatus(404);
        }

        //lecture du template HTML
        $file = __DIR__ . '/../../../public/html/detail.html';

        $html = file_get_contents($file);

        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }


        // Génération du sélecteur de quantité basé sur le stock disponible
        $stock = $stock ?? 0;

        if (!$token) {
            // Pas de token → utilisateur non connecté
            $quantiteSelect = '<a href="/connexion">Vous devez vous connecter pour réserver cet outil</a>';
        } 
        
        else {
            $quantiteSelect = '<select name="quantite" id="quantite">';
            //si le stock est superieur a 0 on affiche le formulaire d'ajout au panier
            if ($stock > 0) {
                $quantiteSelect = '<form method="POST" action="/ajouterPanier">';

                //selection des dates
                $quantiteSelect .= '<div class="dates">';
                $quantiteSelect .= '<div class="dateD">';
                $quantiteSelect .= '<label for="date_debut">Date de début :</label>';
                $quantiteSelect .= '<input type="date" name="date_debut" id="date_debut" required>';
                $quantiteSelect .= '</div>';

                $quantiteSelect .= '<div class="dateF">';
                $quantiteSelect .= '<label for="date_fin">Date de fin :</label>';
                $quantiteSelect .= '<input type="date" name="date_fin" id="date_fin" required>';
                $quantiteSelect .= '</div>';
                $quantiteSelect .= '</div>';

                //combo quantité
                $quantiteSelect .= '<div class="quant">';
                $quantiteSelect .= '<label for="quantite" class="quantite" id="quantite-section">Quantité :</label>';
                $quantiteSelect .= '<select name="quantite" id="quantite">';
                for ($i = 1; $i <= $stock; $i++) {
                    $quantiteSelect .= "<option value=\"$i\">$i</option>";
                }
                $quantiteSelect .= '</select>';
                $quantiteSelect .= '</div>';

                //bouton ajouter au panier
                $quantiteSelect .= '<button type="submit" class="ajoutPanier">Ajouter au panier</button>';
                $quantiteSelect .= '</form>';
            }
            //sinon on affiche rupture de stock et la date de restockage la plus proche si elle est définie
            else {
                $quantiteSelect = '<p>Rupture de stock</p>';
            }
        }

        $remplacements = [
            '{{outil_nom}}' => htmlspecialchars($outil['nom'] ?? ''),
            '{{outil_description}}' => htmlspecialchars($outil['description'] ?? ''),
            '{{outil_prix}}' => htmlspecialchars($outil['prix'] ?? ''),
            '{{outil_image}}' => htmlspecialchars($outil['imageUrl'] ?? '/Image/default.png'),
            '{{outil_categorie}}' => htmlspecialchars($outil['categorie']['nom'] ?? 'N/A'),
            '{{outil_quantite_select}}' => $quantiteSelect
        ];
        $html = str_replace(array_keys($remplacements), array_values($remplacements), $html);

        $menu = (new GenerateMenuClasse())->generateMenu();
        $html = str_replace('{{Menu}}', $menu, $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}

