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


	/*public function afficherCategories(Request $req, Response $resp, $args)
	{
		$categories = \quizzbox\model\categorie::orderBy('nom')->get();

		return (new \quizzbox\view\quizzboxview($categories))->render('afficherCategories', $req, $resp, $args);
    }*/

	public function afficherQuizz(Request $req, Response $resp, $args)
	{
		//$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		$quizz = \quizzbox\model\quizz->orderBy('nom')->get();

		return (new \quizzbox\view\quizzboxview($quizz))->render('afficherQuizz', $req, $resp, $args);
    }
	
    public function accueil(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
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
	
	public function networkCategories(Request $req, Response $resp, $args)
	{
		$url = parse_ini_file($req->getUri()->getBasePath()."/conf/network.ini");
		$content = file_get_contents($url["url"].'/categories/json', FILE_USE_INCLUDE_PATH);
		
		if($content != false)
		{
			$categories = json_decode($content);
			return (new \quizzbox\view\quizzboxview($categories))->render('networkCategories', $req, $resp, $args);
		}
		else
		{
			$_SESSION["message"] = 'Impossible de récupérer les catégories de quizz sur le réseau Quizzbox Network';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function network(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\control\quizzboxcontrol($this))->networkCategories($req, $resp, $args);
	}
	
	public function networkQuizz(Request $req, Response $resp, $args)
	{
		$url = parse_ini_file($req->getUri()->getBasePath()."/conf/network.ini");
		$content = file_get_contents($url["url"].'/categories/json', FILE_USE_INCLUDE_PATH);
		
		if($content != false)
		{
			if($content != "[]")
			{
				$quizz = json_decode($content);
				return (new \quizzbox\view\quizzboxview($quizz))->render('networkQuizz', $req, $resp, $args);
			}
			else
			{
				$_SESSION["message"] = 'Aucun quizz trouvable dans cette catégorie';
				return (new \quizzbox\control\quizzboxcontrol($this))->networkCategories($req, $resp, $args);
			}
		}
		else
		{
			$_SESSION["message"] = 'Impossible de récupérer les quizz sur le réseau Quizzbox Network';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function installQuizzJson($json, Request $req, Response $resp, $args)
	{
		// OSEF de la catégorie
		
		// { "quizz" : {"id":1,"nom":"Sur les risques industriels li\u00e9s au net .","tokenWeb":"174086","id_categorie":1} , "questions" : [  ] }
		
		$erreur = false;
		
		if(isset($json->quizz))
		{
			if(isset($json->quizz->nom))
			{
				$quizz = new \quizzbox\model\quizz();
				$quizz->nom = $json->quizz->nom;
				if(isset($json->quizz->tokenWeb))
				{
					$quizz->tokenWeb = $json->quizz->tokenWeb;
					if(isset($json->quizz->questions))
					{
						$quizz->save();
						foreach($json->quizz->questions as $uneQuestion)
						{
							$question = new \quizzbox\model\question();
							if(isset($uneQuestion->enonce))
							{
								$question->enonce = $uneQuestion->enonce;
								if(isset($uneQuestion->coefficient))
								{
									$question->coefficient = $uneQuestion->coefficient;
									$question->id_quizz = $quizz->id;
									
									if(isset($uneQuestion->reponses))
									{
										$question->save();
										foreach($uneQuestion->reponses as $uneReponse)
										{
											$reponse = new \quizzbox\model\reponse();
											if(isset($uneReponse->nom))
											{
												$reponse->nom = $uneReponse->nom;
												if(isset($uneReponse->nom))
												{
													$reponse->estSolution = $uneReponse->estSolution;
													$reponse->id_question = $question->id;
													$reponse->id_quizz = $quizz->id;
													
													$reponse->save();
												}
												else
												{
													$erreur = true;
												}
											}
											else
											{
												$erreur = true;
											}
										}
									}
									else
									{
										$erreur = true;
									}
								}
								else
								{
									$erreur = true;
								}
							}
							else
							{
								$erreur = true;
							}
						}
					}
					else
					{
						$erreur = true;
					}
				}
				else
				{
					$erreur = true;
				}
			}
			else
			{
				$erreur = true;
			}
		}
		else
		{
			$erreur = true;
		}
		
		if($erreur == true)
		{
			\quizzbox\model\reponse::where('id_quizz', $quizz->id)->delete();
			\quizzbox\model\question::where('id_quizz', $quizz->id)->delete();
			\quizzbox\model\quizz::find($quizz->id)->scores()->detach();
			\quizzbox\model\quizz::destroy($quizz->id);
			
			$_SESSION["message"] = 'Erreur lors de l\'installation du quizz';
			return (new \quizzbox\control\quizzboxcontrol($this))->network($req, $resp, $args);
		}
		else
		{
			$_SESSION["message"] = 'Quizz installé avec succès !';
			return (new \quizzbox\control\quizzboxcontrol($this))->network($req, $resp, $args);
		}
	}
	
	public function networkInstallQuizz(Request $req, Response $resp, $args)
	{
		// ID = Token
		
		$id = filter_var($args['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$url = parse_ini_file($req->getUri()->getBasePath()."/conf/network.ini");
		$content = file_get_contents($url["url"].'/quizz/'.$id.'/install', FILE_USE_INCLUDE_PATH);
		
		if($content != false)
		{
			$quizz = json_decode($content);
			
			return (new \quizzbox\control\quizzboxcontrol($this))->installQuizzJson($quizz, $req, $resp, $args);
		}
		else
		{
			$_SESSION["message"] = 'Impossible de récupérer le quizz sur le réseau Quizzbox Network';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
}
