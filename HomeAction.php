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

        $outilHTML = '<div class="Articles">' ;

        foreach ($outils as $outil) {
            $id  = htmlspecialchars($outil['id']);
            $url = htmlspecialchars($outil['imageUrl']);
            $nom = htmlspecialchars($outil['nom']);
            $prix = htmlspecialchars($outil['prix']);
            $outilHTML .= "<div class=\"article\"><a href=\"/$id\"><img src=\"$url\" alt=\"\"><p class='nom'>$nom</p><p class='prix'>$prix €</p></a></div>" ;
        }

        $outilHTML .= '</div>' ;



        $html = str_replace('{{Liste Outil}}', $outilHTML, $html);



        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
