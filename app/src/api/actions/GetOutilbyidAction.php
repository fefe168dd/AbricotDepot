<?php 
namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\ports\api\dto\OutilDTO;
use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;

class GetOutilbyidAction
{
    private OutilRepository $outilRepository;

    public function __construct(OutilRepository $outilRepository)
    {
        $this->outilRepository = $outilRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $outil = $this->outilRepository->OutilParId($id);

        if (!$outil) {
            $response->getBody()->write(json_encode(['error' => 'Outil not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $outilData = new OutilDTO($outil);
        $response->getBody()->write(json_encode($outilData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}