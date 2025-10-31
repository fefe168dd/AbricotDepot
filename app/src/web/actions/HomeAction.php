<?php
namespace abricotdepot\web\actions ;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {


        $file = __DIR__ . '/../../../public/html/accueil.html';
        $apiUrl = "http://apicot:80/outils" ;

        $json = file_get_contents($apiUrl) ;

        $outils = json_decode($json , true);


        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);

        $outilHTML = '<label class="tri">
        <input type="checkbox" id="toggle-sort">
        <select class="dropdown" id="category-filter">
            <option value="">Toutes les catégories</option>
            <option value="jardinage">Jardinage</option>
            <option value="bricolage">Bricolage</option>
            <option value="construction">Construction</option>
            <option value="peinture">Peinture</option>
            <option value="outils électriques">Outils électriques</option>
        </select>
    </label>' . '<div class="Articles" id="articles-container">' ;

        foreach ($outils as $outil) {
            $id  = htmlspecialchars($outil['id']);
            $url = htmlspecialchars($outil['imageUrl']);
            $nom = htmlspecialchars($outil['nom']);
            $prix = htmlspecialchars($outil['prix']);
            
            // Gérer le cas où categorie est un tableau ou une chaîne
            $categorieRaw = $outil['categorie'] ?? '';
            if (is_array($categorieRaw)) {
                $categorie = isset($categorieRaw['nom']) ? htmlspecialchars($categorieRaw['nom']) : '';
            } else {
                $categorie = htmlspecialchars($categorieRaw);
            }

            $stock = htmlspecialchars(getMaxQuantite($outil['id']));

            $outilHTML .= "<div class=\"article\" data-category=\"$categorie\"><a href=\"/$id\"><img src=\"$url\" alt=\"\"><p class='nom'>$nom</p><p class='prix'>$prix €</p><p class='stock'>Stock Disponible Aujourd'hui: $stock</p></a></div>" ;
        }

        $outilHTML .= '</div>' ;
        
        // Ajouter le script JavaScript pour le filtrage
        $outilHTML .= '<script>
        document.getElementById("category-filter").addEventListener("change", function() {
            const selectedCategory = this.value.toLowerCase();
            const articles = document.querySelectorAll(".article");
            
            articles.forEach(article => {
                const articleCategory = article.getAttribute("data-category").toLowerCase();
                
                if (selectedCategory === "" || articleCategory === selectedCategory) {
                    article.style.display = "";
                } else {
                    article.style.display = "none";
                }
            });
        });
        </script>';




        $html = str_replace('{{Liste Outil}}', $outilHTML, $html);
        $menu = (new GenerateMenuClasse())->generateMenu();
        $html = str_replace('{{Menu}}', $menu, $html);



        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}

function getMaxQuantite(string $outilId): int //Récupère la quantité disponible pour aujourd'hui
{
    $today = date('Y-m-d');
    $reservationUrl = "http://apicot:80/reservations/{$outilId}/{$today}/{$today}";
    $stockUrl       = "http://apicot:80/outils/{$outilId}/stocks";
    

    // --- Récupération des réservations ---
    $ch1 = curl_init($reservationUrl);
    curl_setopt_array($ch1, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $resReservations = curl_exec($ch1);
    curl_close($ch1);

    $reservations = json_decode($resReservations, true) ?? [];

    // --- Récupération du stock ---
    $ch2 = curl_init($stockUrl);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $resStock = curl_exec($ch2);
    curl_close($ch2);

    $stockData = json_decode($resStock, true) ?? [];

    // --- Calcul total réservé ---
    $totalReserve = 0;
    if (is_array($reservations)) {
        if (array_keys($reservations) === range(0, count($reservations) - 1)) {
            // Tableau de réservations
            foreach ($reservations as $res) {
                $totalReserve += $res['quantity'] ?? 0;
            }
        } else {
            // Une seule réservation
            $totalReserve = $reservations['quantity'] ?? 0;
        }
    }

    $stockTotal  = $stockData['quantity'] ?? 0;
    $maxQuantite = max($stockTotal - $totalReserve, 0);

    return $maxQuantite;
}
