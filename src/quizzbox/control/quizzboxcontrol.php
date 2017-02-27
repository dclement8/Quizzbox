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
		$quizz = \quizzbox\model\quizz::orderBy('nom')->get();

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
				$resultats = \quizzbox\model\quizz::where('nom', 'like', '%'.$q.'%')->get();

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
		function get_http_response_code($url)
		{
			$headers = get_headers($url);
			return substr($headers[0], 9, 3);
		}
		
		$url = parse_ini_file("conf/network.ini");
		
		if(get_http_response_code($url["url"].'/categories/json') == "200")
		{
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
		function get_http_response_code($url)
		{
			$headers = get_headers($url);
			return substr($headers[0], 9, 3);
		}
		
		$url = parse_ini_file("conf/network.ini");
		
		if(get_http_response_code($url["url"].'/categories/json') == "200")
		{
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
		else
		{
			$_SESSION["message"] = 'Impossible de récupérer les catégories de quizz sur le réseau Quizzbox Network';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function installQuizzJson($json, Request $req, Response $resp, $args)
	{
		// OSEF de la catégorie
		
		// { "quizz" : {"id":1,"nom":"Sur les risques industriels li\u00e9s au net .","tokenWeb":"174086","id_categorie":1} , "questions" : [  ] }
		
		$erreur = false;
		
		//var_dump($json);
		
		$quizz = new \quizzbox\model\quizz();
		
		if(isset($json->quizz))
		{
			if(isset($json->quizz->nom))
			{
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
			if(isset($quizz->id))
			{
				\quizzbox\model\reponse::where('id_quizz', $quizz->id)->delete();
				\quizzbox\model\question::where('id_quizz', $quizz->id)->delete();
				\quizzbox\model\quizz::find($quizz->id)->scores()->detach();
				\quizzbox\model\quizz::destroy($quizz->id);
			}
			
			$_SESSION["message"] = 'Erreur lors de l\'installation du quizz';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
		else
		{
			$_SESSION["message"] = 'Quizz installé avec succès !';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function networkInstallQuizz(Request $req, Response $resp, $args)
	{
		// ID = Token
		
		function get_http_response_code($url)
		{
			$headers = get_headers($url);
			return substr($headers[0], 9, 3);
		}
		
		$id = filter_var($args['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$url = parse_ini_file("conf/network.ini");
		
		if(get_http_response_code($url["url"].'/categories/json') == "200")
		{
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
		else
		{
			$_SESSION["message"] = 'Impossible de récupérer les catégories de quizz sur le réseau Quizzbox Network';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function formUploadQuizz(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\view\quizzboxview($this))->render('formUploadQuizz', $req, $resp, $args);
	}
	
	public function uploadInstallQuizz(Request $req, Response $resp, $args)
	{
		// Formulaire d'upload
		
		if(isset($_FILES['quizz']))
		{
			$dossier = 'upload/';
			$fichier = basename($_FILES['quizz']['name']);
			$taille_maxi = 2 * 1000000; // En octets (ici 2 Mo)
			$taille = filesize($_FILES['quizz']['tmp_name']);
			$extensions = array('.quizz', '.qzz');
			$extension = strrchr($_FILES['quizz']['name'], '.'); 
			
			// Vérification de l'extension
			if(!in_array($extension, $extensions))
			{
				$_SESSION["message"] = 'Extension de fichier non-autorisée.';
				return (new \quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
			}
			else
			{
				if($taille > $taille_maxi)
				{
					$_SESSION["message"] = 'Le fichier est trop volumineux.';
					return (new \quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
				}
				else
				{
					if(move_uploaded_file($_FILES['quizz']['tmp_name'], $dossier . $fichier))
					{
						// Traitement du fichier de quizz uploadé
						$contenu = file_get_contents("http://".$_SERVER["SERVER_NAME"].$req->getUri()->getBasePath()."/".$dossier.$fichier);
						
						$decrypt = base64_decode($contenu);
						if($decrypt != false)
						{
							// Décryptage réussi
							$json = json_decode($decrypt);
							if($json != false)
							{
								// Encodage JSON réussi
								return (new \quizzbox\control\quizzboxcontrol($this))->installQuizzJson($json, $req, $resp, $args);
							}
							else
							{
								// Erreur JSON
								$_SESSION["message"] = 'Une erreur est survenue durant la lecture du fichier.';
								return (new \quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
							}
						}
						else
						{
							// Erreur de lecture
							$_SESSION["message"] = 'Une erreur est survenue durant la lecture du fichier.';
							return (new \quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
						}
						
						// Suppression du fichier uploadé.
						chmod($dossier.$fichier, 0777);
						@unlink($dossier.$fichier);
					}
					else
					{
						$_SESSION["message"] = 'Une erreur est survenue durant l\'upload.';
						return (new \quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
					}
				}
			}
		}
		else
		{
			return (new \quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
		}
	}
	
	public function envoiScore(Request $req, Response $resp, $args)
	{
		// On authentifie le joueur par son pseudo dans l'URL uniquement
		
		$joueur = filter_var($args['joueur'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$score = filter_var($args['score'], FILTER_SANITIZE_NUMBER_INT);

		$args['id'] = filter_var($jsonClient->quizz->tokenWeb, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if(\quizzbox\model\quizz::where('tokenWeb', $args['id'])->get()->toJson() != "[]")
		{
			// Créer joueur
			$lejoueur = new \quizzbox\model\joueur();
			$lejoueur->pseudo = $joueur;
			$lejoueur->save();
			
			$scores = \quizzbox\model\quizz::where('tokenWeb', $id)->scores()->where("id_joueur", $lejoueur->id)->first();
			$scores->pivot->score = $score;
			$scores->save();

			$arr = array('success' => 'Score ajouté avec succès.');
			$resp = $resp->withStatus(201);
			return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
		}
		else
		{
			$arr = array('error' => 'Le quizz est introuvable sur le serveur.');
			$resp = $resp->withStatus(404);
			return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
		}
	}
}
