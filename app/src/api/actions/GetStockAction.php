<?php 
namespace App\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\core\application\usecases\ServiceStock;

class GetStockAction 
{
    private ServiceStock $serviceStock;

    public function __construct(ServiceStock $serviceStock)
    {
        $this->serviceStock = $serviceStock;
    }
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $stocks = $this->serviceStock->listerStocks();

        if (empty($stocks)) {
            $response->getBody()->write(json_encode(['error' => 'No stocks found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $stocksData = array_map(fn($stock) => $stock, $stocks);
        $response->getBody()->write(json_encode($stocksData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}