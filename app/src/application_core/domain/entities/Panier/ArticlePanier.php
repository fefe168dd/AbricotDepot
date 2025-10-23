<?php

Require_once 'app/src/application_core/domain/entities/Panier/Panier.php';

class ArticlePanier {
    public $articles = [];

    public function ajouterArticle(ArticlePanier $article) {
        $this->articles[] = $article;
    }

}