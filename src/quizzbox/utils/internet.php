<?php
namespace quizzbox\utils;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class internet {
    // Middleware qui vérifie l'état de la connexion Internet
    /*
        Utilisation de "internet" dans une route dans index.php :
        ->setName('nomDeVotreRoute')->add(new quizzbox\utils\internet() ); // A placer à la fin d'un $app->get par exemple
    */

    public function __invoke(Request $req, Response $resp, callable $next)
    {
        $connected = @fsockopen("www.example.com", 80); 
		if($connected)
		{
			// Connecté
			fclose($connected);
			return $next($req, $resp);
		}
		else
		{
			// Pas connecté
			$isConnected = false;
			
			$_SESSION["message"] = "Aucune connexion Internet n'est disponible actuellement !";
            return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, null);
		}
    }
}
