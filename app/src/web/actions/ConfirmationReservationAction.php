<?php

namespace abricotdepot\web\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConfirmationReservationAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .confirmation-message {
            background-color: white;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .message {
            font-size: 1.5rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }
        .countdown {
            font-size: 1.2rem;
            color: #666;
        }
        .timer {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="confirmation-message">
        <div class="success-icon">✅</div>
        <div class="message">Votre réservation a bien été enregistrée</div>
        <div class="countdown">
            Redirection vers l'accueil dans
            <div class="timer" id="countdown">5</div>
            secondes...
        </div>
    </div>
    
    <script>
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        
        const interval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = '/';
            }
        }, 1000);
    </script>
</body>
</html>
HTML;
        
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}