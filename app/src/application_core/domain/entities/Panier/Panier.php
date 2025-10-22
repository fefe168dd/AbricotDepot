<?php

class Panier {
   private int $idpanier;
   private int $user_id;

   public function __construct(int $idpanier, int $user_id) {
       $this->idpanier = $idpanier;
       $this->user_id = $user_id;
   }
   public function getIdPanier(): int {
       return $this->idpanier;
   }

   public function getUserId(): int {
       return $this->user_id;
   }

   public function addItemToPanier(string $panierId, int $outilId, int $quantity = 1): void {
    $stmt = $this->pdo->prepare("
        INSERT INTO panier_item (panier_id, outil_id, quantity) 
        VALUES (:panier_id, :outil_id, :quantity)
        ON DUPLICATE KEY UPDATE quantity = quantity + :quantity
    ");
    $stmt->execute([
        ':panier_id' => $panierId,
        ':outil_id' => $outilId,
        ':quantity' => $quantity
    ]);
}
}