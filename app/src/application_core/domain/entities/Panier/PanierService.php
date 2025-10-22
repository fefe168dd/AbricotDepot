<?php
class PanierService
{
    private $panierRepository;

    public function __construct($panierRepository)
    {
        $this->panierRepository = $panierRepository;
    }

    public function addItem(int $outilId, int $quantity = 1): void
    {
        session_start();
        if (!isset($_SESSION['panier_id'])) {
            $_SESSION['panier_id'] = \Ramsey\Uuid\Uuid::uuid4()->toString();
        }
        $panierId = $_SESSION['panier_id'];

        $this->panierRepository->addItem($panierId, $outilId, $quantity);
    }
}
