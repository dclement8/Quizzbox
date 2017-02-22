<?php
namespace quizzbox\control;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \quizzbox\AppInit;

// Connexion à la BDD
$connexion = new AppInit();
$connexion->bootEloquent("./conf/config.ini");

class quizzboxcontrol
{
    protected $c=null;

    public function __construct($c)
	{
        $this->c = $c;
    }


	public function afficherCategories(Request $req, Response $resp, $args)
	{
		$categories = \quizzbox\model\categorie::orderBy('nom')->get();

		return (new \quizzbox\view\quizzboxview($categories))->render('afficherCategories', $req, $resp, $args);
    }

	public function afficherQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		$quizz = \quizzbox\model\quizz::where('id_categorie', $id)->orderBy('nom')->get();

		return (new \quizzbox\view\quizzboxview($quizz))->render('afficherQuizz', $req, $resp, $args);
    }
	
    public function accueil(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\control\quizzboxcontrol($this))->afficherCategories($req, $resp, $args);
    }
	
	public function connexionFormAdmin(Request $req, Response $resp, $args)
	{
		if(isset($_SESSION["admin"]))
		{
			// Déconnexion et destruction de tous les éléments de session
			unset($_SESSION);
            session_destroy();

			$_SESSION["message"] = "Vous êtes à présent déconnecté !";
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
		else
		{
			return (new \quizzbox\view\quizzboxview($this))->render('connexionFormAdmin', $req, $resp, $args);
		}
    }
	
	public function connexionTraitement(Request $req, Response $resp, $args) {
        if(isset($_POST['mdp']))
            $mdp = $_POST['mdp'];

        if(!empty($mdp)) {
			
			$iniAdmin =  parse_ini_file("./conf/admin.ini");
			
			if($mdp === $iniAdmin["adminPassword"])
			{
				$_SESSION["admin"] = "admin";
				$_SESSION["message"] = 'Vous êtes connecté en tant qu\'administrateur !';
				return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
			}
			else
			{
				$_SESSION["message"] = 'Mot de passe incorrect !';
				return (new \quizzbox\view\quizzboxview($this))->render('connexionFormAdmin', $req, $resp, $args);
			}
        }
        else
		{
			return (new \quizzbox\view\quizzboxview($this))->render('connexionFormAdmin', $req, $resp, $args);
		}
    }
	
	public function rechercher(Request $req, Response $resp, $args)
	{
		if(isset($_GET["q"]))
		{
			if($_GET["q"] === "")
			{
				return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
			}
			else
			{
				$q = filter_var($_GET["q"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				$resultats = \quizzbox\model\quizz::where('nom', 'like', '*'.$q.'*')->get();

				return (new \quizzbox\view\quizzboxview($resultats))->render('rechercher', $req, $resp, $args);
			}
		}
		else
		{
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function supprimerQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if(\quizzbox\model\quizz::where('id', $id)->get()->toJson() != "[]")
		{
			\quizzbox\model\reponse::where('id_quizz', $id)->delete();
			\quizzbox\model\question::where('id_quizz', $id)->delete();
			\quizzbox\model\quizz::find($id)->scores()->detach();
			\quizzbox\model\quizz::destroy($id);

			$_SESSION["message"] = 'Quizz supprimé';
		}
		else
		{
			$_SESSION["message"] = 'Quizz introuvable';
		}


		return (new \quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
	}
}
