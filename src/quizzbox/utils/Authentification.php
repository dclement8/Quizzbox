<?php
namespace quizzbox\utils;
class Authentification extends AbstractAuthentification
{
	public function __construct()
	{
		if(isset($_SESSION["user_login"]))
		{
			$this->user_login = $_SESSION["user_login"];
			$this->logged_in = true;
		}
		else
		{
			$this->user_login = null;
			$this->logged_in = false;
		}
	}

	// Vérification du couple Login/Mot de Passe renseigné par l'utilisateur avec celui de la base de données + Création variable SESSION si authentifié.
	public function login($leLogin, $lePassword)
	{
		$infos = \sportnet\model\joueur::where('pseudo', '=', $leLogin);
		if($infos == null)
		{
			// Login introuvable
			//throw new \Exception("Login introuvable");
			$this->logged_in = false;
		}
		else
		{
			// Login trouvé
			if(password_verify($lePassword, $infos->motDePasse))
			{
				// Mot de passe juste
				$this->user_login = $leLogin;
				$_SESSION["user_login"] = $this->user_login;
				$this->logged_in = true;
			}
			else
			{
				$this->logged_in = false;
			}
		}
	}

	// Détruit la variable SESSION d'authentification si elle existe
	public function logout()
	{
		unset($_SESSION["user_login"]);
		$this->user_login = null;
		$this->logged_in = false;
	}
}
