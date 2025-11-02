<?php

namespace abricotdepot\web\actions;

use abricotdepot\web\helpers\TokenHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DetailProduitAction
{
    public function __construct() {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
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
        $file = __DIR__ . '/../../../public/html/accueil.html';

        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);

        // Génération du sélecteur de quantité basé sur le stock disponible
        $stock = $stock ?? 0;
        
        // Vérifier l'authentification et rafraîchir le token si nécessaire
        $isAuthenticated = TokenHelper::ensureAuthenticated($request);

        if (!$isAuthenticated) {
            // Pas de token → utilisateur non connecté
            $quantiteSelect = '<a href="/connexion">Vous devez vous connecter pour réserver cet outil</a>';
        } else {
            $quantiteSelect = '<select name="quantite" id="quantite">';
            //si le stock est superieur a 0 on affiche le formulaire d'ajout au panier
            if ($stock > 0) {
                $quantiteSelect = '<form class="Reservation" method="POST" action="/' . htmlspecialchars($outilId) . '/ajouterPanier">';

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
            }
            //sinon on affiche rupture de stock et la date de restockage la plus proche si elle est définie
            else {
                $quantiteSelect = '<p>Rupture de stock</p>';
            }
        }

        $htmlDetaille = '<main class="detaille"><div class="articleDetaille">
            <h2>Détail du produit</h2>
            <img class="produit-image" src="{{outil_image}}" alt="{{outil_nom}}">
            <div class="produit-info">
                <h3 class="nom">{{outil_nom}}</h3>
                <p class="description">{{outil_description}}</p>
                <p class="prix">Prix : {{outil_prix}} €</p>
                <p class="categorie">Catégorie : {{outil_categorie}}</p>
            </div>
            ' . $quantiteSelect . '
        </div></main>';

        $remplacements = [
            '{{outil_nom}}' => htmlspecialchars($outil['nom'] ?? ''),
            '{{outil_description}}' => htmlspecialchars($outil['description'] ?? ''),
            '{{outil_prix}}' => htmlspecialchars($outil['prix'] ?? ''),
            '{{outil_image}}' => htmlspecialchars($outil['imageUrl'] ?? '/Image/default.png'),
            '{{outil_categorie}}' => htmlspecialchars($outil['categorie']['nom'] ?? 'N/A'),
            '{{outil_quantite_select}}' => $quantiteSelect
        ];
        $html = str_replace('{{Liste Outil}}', $htmlDetaille, $html);

        $html = str_replace(array_keys($remplacements), array_values($remplacements), $html);

        $menu = (new GenerateMenuClasse())->generateMenu();
        $html = str_replace('{{Menu}}', $menu, $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
?>
<script>
    //ajout des logs pour le debug
    document.addEventListener('DOMContentLoaded', () => {
        const dateDebutInput = document.getElementById('date_debut');
        const dateFinInput = document.getElementById('date_fin');
        const quantiteSelect = document.getElementById('quantite');
        const outilId = window.location.pathname.split('/').pop(); // récupère l'id depuis l'URL

        console.log("🧩 Outil ID détecté :", outilId);

        async function updateQuantiteOptions() {
            const dateDebut = dateDebutInput.value;
            const dateFin = dateFinInput.value;

            console.log("Dates sélectionnées :", {
                dateDebut,
                dateFin
            });

            // On attend que les deux dates soient renseignées
            if (!dateDebut || !dateFin) {
                console.log("En attente des deux dates...");
                return;
            }

            try {
                const reservationUrl = `/reservations/${outilId}/${dateDebut}/${dateFin}`;
                const stockUrl = `/outils/${outilId}/stocks`;

                console.log("Requêtes API :", {
                    reservationUrl,
                    stockUrl
                });

                const response = await fetch(reservationUrl);
                const stockage = await fetch(stockUrl);

                console.log("Réponses API :", {
                    response,
                    stockage
                });

                const reservations = await response.json();

                if (!stockage.ok) {
                    throw new Error("Erreur lors de la récupération du stock");
                }

                const stockData = await stockage.json();

                console.log("Données reçues :", {
                    reservations,
                    stockData
                });

                // Additionne leurs quantités si plusieurs réservations
                let totalReserve = 0;
                if (Array.isArray(reservations)) {
                    totalReserve = reservations.reduce((somme, res) => somme + (res.quantity ?? 0), 0);
                } else if (reservations.quantity) {
                    totalReserve = reservations.quantity;
                }

                const stockTotal = stockData.quantity ?? 0;
                const maxQuantite = Math.max(stockTotal - totalReserve, 0);

                console.log("Calculs :", {
                    stockTotal,
                    totalReserve,
                    maxQuantite
                });

                // Reconstruction de la combobox
                quantiteSelect.innerHTML = "";

                if (maxQuantite > 0) {
                    for (let i = 1; i <= maxQuantite; i++) {
                        const option = document.createElement("option");
                        option.value = i;
                        option.textContent = i;
                        quantiteSelect.appendChild(option);
                    }
                } else {
                    const option = document.createElement("option");
                    option.textContent = "Indisponible";
                    option.disabled = true;
                    quantiteSelect.appendChild(option);
                }

                console.log("Combobox mise à jour avec", maxQuantite, "valeurs disponibles.");

            } catch (error) {
                console.error("Erreur dans updateQuantiteOptions :", error);
            }
        }

        dateDebutInput.addEventListener("change", updateQuantiteOptions);
        dateFinInput.addEventListener("change", updateQuantiteOptions);
    });
</script>