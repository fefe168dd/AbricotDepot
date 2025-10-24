<?php

namespace abricotdepot\web\actions;

class GenerateMenuClasse
{


    public function generateMenu() {

        $connected = $_COOKIE['access_token'] ?? null;


        if($connected) {
            $menu = '<li><a href="/profil"><div class="inscription"><img class="icon" src="Image/icones/User.png"></div></a></li>
            <li><a href="/panier"><div class="panier"><img class="icon" src="Image/icones/shopping-basket.png"></div></a></li>
            <li><a href="/"><div class="deconnexion"><img class="icon" src="Image/icones/Log-out.png"></div></a></li>' ;
        }else {
            $menu = '<li><a href="/connexion"><div class="inscription"><img class="icon" src="Image/icones/User.png"></div></a></li>' ;
        }

        return $menu ;

    }

}