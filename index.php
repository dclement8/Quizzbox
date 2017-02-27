<?php
session_start();
// Autoloaders
require_once("./vendor/autoload.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true ] ,
	'notFoundHandler' => function($c) {
		return (function($req, $resp) {
			$args = null;
			$resp = $resp->withStatus(404);

			$_SESSION["message"] = "Erreur 404 : la page que vous avez demandé est introuvable !";

			return (new quizzbox\control\quizzboxcontrol(null))->accueil($req, $resp, $args);
		});
	}
];
$c = new\Slim\Container($configuration);
$app = new \Slim\App($c);

// -------------------

$app->get('/', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
})->setName('accueil');

/*$app->get('/categories', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->afficherCategories($req, $resp, $args);
})->setName('afficherCategories');*/

$app->get('/categories/{id}', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
})->setName('afficherQuizz');

$app->get('/admin', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->connexionFormAdmin($req, $resp, $args);
})->setName('connexionFormAdmin');

$app->post('/admin', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->connexionTraitement($req, $resp, $args);
})->setName('connexionTraitement');

$app->get('/recherche', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->rechercher($req, $resp, $args);
})->setName('rechercher');

$app->post('/quizz/{id}/supprimer', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->supprimerQuizz($req, $resp, $args);
})->setName('supprimerQuizz')->add(new quizzbox\utils\authentificationAdmin());

$app->get('/network', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->network($req, $resp, $args);
})->setName('network')->add(new quizzbox\utils\internet());

$app->get('/network/categories', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->networkCategories($req, $resp, $args);
})->setName('networkCategories')->add(new quizzbox\utils\internet());

$app->get('/network/categories/{id}', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->networkQuizz($req, $resp, $args);
})->setName('networkQuizz')->add(new quizzbox\utils\internet());

$app->get('/network/quizz/{id}/install', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->networkInstallQuizz($req, $resp, $args);
})->setName('networkInstallQuizz')->add(new quizzbox\utils\internet());

$app->get('/uploadQuizz', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->formUploadQuizz($req, $resp, $args);
})->setName('uploadQuizzForm');

$app->post('/uploadQuizz', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->uploadInstallQuizz($req, $resp, $args);
})->setName('uploadQuizz');

$app->get('/test', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->test($req, $resp, $args);
})->setName('test');

$app->run();