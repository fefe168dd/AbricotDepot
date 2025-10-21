<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\usecases\ServiceOutil;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DetailProduitAction
{
    private ServiceOutil $serviceOutil;

    public function __construct(ServiceOutil $serviceOutil)
    {
        $this->serviceOutil = $serviceOutil;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $outilId = $args['id'] ?? null;
        if (!$outilId) {
            $response->getBody()->write(json_encode(['error' => 'ID manquant']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //appel du usecase
        $outil = $this->serviceOutil->obtenirOutilParId($outilId);

        if (!$outil) {
            $response->getBody()->write('Outil introuvable');
            return $response->withStatus(404);
        }

        //lecture du template HTML
        $file = __DIR__ . '/../../../public/html/detail.html';

        $html = file_get_contents($file);

        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }
        

        $remplacements = [
            '{{outil_nom}}' => htmlspecialchars($outil->nom),
            '{{outil_description}}' => htmlspecialchars($outil->description),
            '{{outil_prix}}' => htmlspecialchars($outil->prix),
            '{{outil_image}}' => htmlspecialchars($outil->imageUrl ?? '/Image/default.png'),
            '{{outil_categorie}}' => htmlspecialchars($outil->categorie['nom'] ?? 'N/A')

        ];

        $html = str_replace(array_keys($remplacements), array_values($remplacements), $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
