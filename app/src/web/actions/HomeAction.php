<?php
namespace abricotdepot\web\actions ;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {

        $menu = (new GenerateMenuClasse())->generateMenu();

        $file = __DIR__ . '/../../../public/html/accueil.html';
        $apiUrl = "http://localhost:80/outils" ;

        $json = file_get_contents($apiUrl) ;

        $outils = json_decode($json , true);


        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);

        $outilHTML = '<label class="tri">
        <input type="checkbox" id="toggle-sort">
        <select class="dropdown">
            <option value="">Trier par :</option>
            <option value="peinture">Peinture</option>
            <option value="jardin">Jardin</option>
            <option value="garage">Garage</option>
        </select>
    </label>' . '<div class="Articles">' ;

        foreach ($outils as $outil) {
            $id  = htmlspecialchars($outil['id']);
            $url = htmlspecialchars($outil['imageUrl']);
            $nom = htmlspecialchars($outil['nom']);
            $prix = htmlspecialchars($outil['prix']);

            $apiUrlStock = "http://localhost:80/outils/$id/stocks" ;

            $json = file_get_contents($apiUrlStock) ;

            $stockJson = json_decode($json , true) ;

            $stock  = htmlspecialchars($stockJson['quantity']);

            $outilHTML .= "<div class=\"article\"><a href=\"/$id\"><img src=\"$url\" alt=\"\"><p class='nom'>$nom</p><p class='prix'>$prix €</p><p class='stock'>Stock Disponible : $stock</p></a></div>" ;
        }

        $outilHTML .= '</div>' ;



        $html = str_replace('{{Liste Outil}}', $outilHTML, $html);
        $html = str_replace('{{Menu}}', $menu, $html);



        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
