app.controller("quizzController", ["$scope", "$http", "$location",
function($scope, $http, $location) {

	/* Variables */
	$scope.score = 0; // Score du joueur
	$scope.quizz = false; // Données du quizz joué
	$scope.finJeu = false; // Fin de jeu
	$scope.question = 0; // Indice de la question en cours
	$scope.timestamp = 0; // Timestamp lors de l'affichage de la question
	$scope.tempsReponse = 30; // Temps de réponse en secondes par défaut
	$scope.timer = 0; // Timer affiché
	$scope.passerQuestion = false; // Stocke le timout
	$scope.horloge = false; // Stock le setInterval du timer
	$scope.confNetwork = ""; // URL vers le serveur Quizzbox Network
	$scope.tempsPasse = 0; // Temps total

	var storageAvailable = function(type)
	{
		try
		{
			var storage = window[type],
				x = '__storage_test__';
			storage.setItem(x, x);
			storage.removeItem(x);
			return true;
		}
		catch(e)
		{
			return false;
		}
	};

	if(!storageAvailable('localStorage'))
	{
		alert('localStorage indisponible sur votre navigateur !');
		return false;
	}

	var errorHandler = function(e)
	{
		console.log(e);
	}

	// Affiche un message avec une couleur de fond et un temps pré-défini
	var showMsg = function(msg, bgcolor, time)
	{
		bgcolor = typeof bgcolor !== 'undefined' ? bgcolor : "rgba(0,128,0,0.9)";
		time = typeof time !== 'undefined' ? time : 5000;

		$("#messageJeu").html(msg);
		$("#messageJeu").css("background-color", bgcolor);
		$("#messageJeu").fadeIn();
		setTimeout(function(){ $("#messageJeu").fadeOut(); }, time);
	}
	
	// Récupère un paramètre en GET dans l'URL : getParamURL.param
	var getParamURL = function ()
	{
		var query_string = {};
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++)
		{
			var pair = vars[i].split("=");
			if (typeof query_string[pair[0]] === "undefined")
			{
				query_string[pair[0]] = decodeURIComponent(pair[1]);
			}
			else if (typeof query_string[pair[0]] === "string")
			{
				var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
				query_string[pair[0]] = arr;
			}
			else
			{
				query_string[pair[0]].push(decodeURIComponent(pair[1]));
			}
		} 
		return query_string;
	}();
	
	// Continuer : pas d'envoi du score
	$scope.continuer = function()
	{
		localStorage.removeItem("mode");
		$location.path('/');
	}

	// Envoyer le score en local
	$scope.envoiScoreLocal = function()
	{
		var pseudo = "Anonyme";
		
		if(document.getElementById("pseudoLocal") != undefined)
		{
			if(document.getElementById("pseudoLocal").value == "")
			{
				pseudo = "Anonyme";
			}
			else
			{
				pseudo = document.getElementById("pseudoLocal").value;
			}
		}
		
		$http.put("quizz/" + $scope.quizz.quizz.tokenWeb + "/joueur/" + pseudo + "/scores/" + localStorage.getItem('score')).then(function(response) {
			console.log(response.data);
			
			if(response.status == 201)
			{
				showMsg("Score envoyé !", "rgba(0,0,128,0.9)", 5000);
				$scope.continuer();
			}
			else
			{
				showMsg("Erreur lors de l'envoi du score" + response.data.error, "rgba(213,85,0,0.9)", 5000);
			}
		},
		function(error) {
			console.log(error);
			showMsg("Impossible d'inscrire votre score !", "rgba(213,85,0,0.9)", 5000);
		});
	};
	
	// Envoyer le score en ligne
	$scope.envoiScoreNetwork = function()
	{
		if(document.getElementById("pseudoNetwork") != undefined)
		{
			if(document.getElementById("pseudoNetwork").value != "")
			{
				if(document.getElementById("mdpNetwork") != undefined)
				{
					if(document.getElementById("mdpNetwork").value != "")
					{
						var urlEnvoi = $scope.confNetwork + "/quizz/joueur/" + document.getElementById("pseudoNetwork").value + "@" + Sha256.hash(document.getElementById("mdpNetwork").value) + "/scores/" + localStorage.getItem('score');
						
						console.log(urlEnvoi);
						
						var dataJson = JSON.stringify($scope.quizz);
						
						$.post(urlEnvoi, dataJson)
						.done(function(data) {
							console.log( data );
							if(data.success != undefined)
							{
								showMsg("Score envoyé !", "rgba(0,0,128,0.9)", 5000);
								$scope.continuer();
								$scope.$apply();
							}
							else
							{
								showMsg("Erreur lors de l'envoi du score : " + data.error, "rgba(213,85,0,0.9)", 5000);
							}
						})
						.fail(function(error) {
							console.log( error );
							showMsg("Erreur lors de l'envoi du score : " + $.parseJSON(error.responseText).error, "rgba(213,85,0,0.9)", 5000);
						});
					}
					else
					{
						showMsg("Vous devez renseigner un mot de passe !", "rgba(213,85,0,0.9)", 5000);
					}
				}
				else
				{
					showMsg("Vous devez renseigner un mot de passe !", "rgba(213,85,0,0.9)", 5000);
				}
			}
			else
			{
				showMsg("Vous devez renseigner un pseudo !", "rgba(213,85,0,0.9)", 5000);
			}
		}
		else
		{
			showMsg("Vous devez renseigner un pseudo !", "rgba(213,85,0,0.9)", 5000);
		}
	};
	
	// Fonction qui décrémente l'horloge/timer
	$scope.afficherTemps = function()
	{
		if($scope.timer > 0)
		{
			$scope.timer--;
			
			if($scope.timer < 6)
			{
				$("#jeuTimer").css("color", "red");
			}
			else
			{
				$("#jeuTimer").css("color", "black");
			}
			
			$("#jeuTimer").html($scope.timer);
		}
	}
	
	// Fin de jeu
	$scope.fin = function()
	{
		if($scope.finJeu == true)
		{
			localStorage.removeItem("score");
			localStorage.setItem('score', $scope.score);
			
			if(localStorage.getItem('mode') == "multi")
			{
				localStorage.removeItem("temps");
				localStorage.setItem('temps', $scope.tempsPasse);
			}
			
			console.log("Fin de jeu");
			$location.path('/finJeu');
			
			$scope.$apply(); // Résoue le bug de la fin de jeu qui ne se charge pas quand on arrive à la dernière question au timeOut.
			
			$("#leScore").html(localStorage.getItem('score'));
		}
	}
	
	// Fonction qui se déclanche après le temps imparti
	$scope.tropTard = function()
	{
		document.getElementsByClassName("avancementQuestion")[$scope.question].style.backgroundColor = "black";
		$scope.question++;
			
		if($scope.quizz.quizz.questions[$scope.question] == null)
		{
			$("#jeuTimer").html("0");
			console.log("Partie terminée");
			
			clearTimeout($scope.passerQuestion);
			clearInterval($scope.horloge);
			
			$scope.finJeu = true;
			$scope.fin();
		}
		else
		{
			$scope.tempsPasse = Math.round($scope.tempsPasse + (Math.floor(Date.now() / 1000) - $scope.timestamp));
			$scope.afficherQuestion();
		}
	}
	
	// Afficher la question
	$scope.afficherQuestion = function()
	{
		//console.log($scope.quizz);
		document.getElementsByClassName("avancementQuestion")[$scope.question].style.backgroundColor = "blue";
		$("#jeuEnnonce").html($scope.quizz.quizz.questions[$scope.question].enonce);
		
		var htmlReponses = "";
		for(var i = 0; i < $scope.quizz.quizz.questions[$scope.question].reponses.length; i++)
		{
			htmlReponses += "<button class='boutonReponse' style='background-color:#C0C0C0;'><input type='checkbox' name='uneReponse' />" + $scope.quizz.quizz.questions[$scope.question].reponses[i].nom + "</button><br/>";
		}
		
		// Bouton cliquable de la réponse
		function clicBouton()
		{
			if(this.firstChild.checked == true)
			{
				this.firstChild.checked = false;
				this.style.backgroundColor = "#C0C0C0";
			}
			else
			{
				this.firstChild.checked = true;
				this.style.backgroundColor = "#FFCC00";
			}
		}

		$(document).ready(function()
		{
			$('.boutonReponse').click(clicBouton);
		});
		
		$("#jeuReponses").html(htmlReponses);
		
		$scope.timestamp = Math.floor(Date.now() / 1000);
		
		// Timer pour passer à la question suivante
		$("#jeuTimer").html(Math.round($scope.tempsReponse / $scope.quizz.quizz.questions[$scope.question].coefficient));
		$scope.passerQuestion = setTimeout($scope.tropTard, Math.round(($scope.tempsReponse * 1000) / $scope.quizz.quizz.questions[$scope.question].coefficient));
		$scope.timer = Math.round($scope.tempsReponse / $scope.quizz.quizz.questions[$scope.question].coefficient);
		$scope.horloge = setInterval($scope.afficherTemps, 1000);
	}
	
	// Action de validation de réponse à la question en cours
	$scope.repondreQuestion = function()
	{
		var reponses = document.getElementsByName("uneReponse");
		
		// Vérifier si au moins une réponse est donnée
		var reponseFournie = false;
		for(var i = 0; i < reponses.length; i++)
		{
			if(reponses[i].checked == true)
			{
				reponseFournie = true;
			}
		}
		
		if(reponseFournie == true)
		{
			// Temps de réponse
			var timeReponse = Math.floor(Date.now() / 1000);
			
			// Interruption des timers
			clearTimeout($scope.passerQuestion);
			clearInterval($scope.horloge);
			
			// Vérifier bonnes réponses
			var correct = true;
			for(var i = 0; i < reponses.length; i++)
			{
				if(reponses[i].checked == true)
				{
					if($scope.quizz.quizz.questions[$scope.question].reponses[i].estSolution != 1)
					{
						correct = false;
					}
				}
				else
				{
					if($scope.quizz.quizz.questions[$scope.question].reponses[i].estSolution == 1)
					{
						correct = false;
					}
				}
			}
			
			if(correct == true)
			{
				// Le joueur a fourni la/les bonnes réponses à la question
				var temps = timeReponse - $scope.timestamp;
				$scope.score = $scope.score + $scope.quizz.quizz.questions[$scope.question].coefficient;
				
				$scope.tempsPasse = $scope.tempsPasse + temps;
				
				showMsg("Bonne réponse", "rgba(0,128,0,0.9)", 3000);
				
				document.getElementsByClassName("avancementQuestion")[$scope.question].style.backgroundColor = "green";
			}
			else
			{
				// Mauvaise réponse
				showMsg("Mauvaise réponse", "rgba(217,0,0,0.9)", 3000);
				document.getElementsByClassName("avancementQuestion")[$scope.question].style.backgroundColor = "red";
			}
			
			// Passer à la question suivante
			$scope.question++;
			
			if($scope.quizz.quizz.questions[$scope.question] == null)
			{
				console.log("Partie terminée");
				$scope.finJeu = true;
				$scope.fin();
			}
			else
			{
				$scope.afficherQuestion();
			}
		}
		else
		{
			showMsg("Vous n'avez pas répondu à la question !", "rgba(213,85,0,0.9)", 3000);
		}
	};
	
	$scope.afficherAvancement = function()
	{
		for(var i = 0; i < $scope.quizz.quizz.questions.length; i++)
		{
			document.getElementById("jeuAvancement").innerHTML = document.getElementById("jeuAvancement").innerHTML + "<div class='avancementQuestion'>" + (i + 1) + "</div>";
		}
	};
	
	/* Initialisation */
	if($location.$$path == "/jeu")
	{
		if($scope.finJeu == false)
		{
			if(!localStorage.getItem('mode'))
			{
				// Redirection
				$location.path('/');
			}
			else
			{
				// Récupère le token du quizz passé en paramètre dans l'URL
				var tokenQuizz = getParamURL.quizz;
				
				if(tokenQuizz != "undefined")
				{
					// Récupère les données du quizz
					$http.get("quizz/" + tokenQuizz).then(function(response)
					{
						if(response.status == 200)
						{
							$http.get("conf/network.ini").then(function(response2)
							{
								if(response2.status == 200)
								{
									//console.log(response.data.split('"'));
									$scope.confNetwork = response2.data.split('"')[1];
									
									localStorage.removeItem("score");
									console.log("Partie en cours");
									$scope.quizz = response.data;

									$scope.score = 0;
									$scope.question = 0;
									$scope.finJeu = false;
									
									$("#jeuNom").html($scope.quizz.quizz.nom);
									
									// Avancement
									$scope.afficherAvancement();
									
									// Afficher la première question
									$scope.afficherQuestion();
								}
								else
								{
									$location.path('/');
								}
							},
							function(error) {
								console.log(error);
								$location.path('/');
							});
						}
						else
						{
							$location.path('/');
						}
						
					},
					function(error) {
						console.log(error);
						$location.path('/');
					});
				}
				else
				{
					$location.path('/');
				}
			}
		}
	}
	else
	{
		$("#leScore").html(localStorage.getItem('score'));
		
		if(localStorage.getItem('mode') == "multi")
		{
			$("#tempsFinal").html("Temps de jeu total : " + localStorage.getItem('temps') + " secondes");
		}
		
		var tokenQuizz = getParamURL.quizz;
				
		if(tokenQuizz != "undefined")
		{
			$http.get("quizz/" + tokenQuizz).then(function(response)
			{
				if(response.status == 200)
				{
					$http.get("conf/network.ini").then(function(response2)
					{
						if(response2.status == 200)
						{
							$scope.quizz = response.data;
							$scope.confNetwork = response2.data.split('"')[1];
						}
						else
						{
							$location.path('/');
						}
					},
					function(error) {
						console.log(error);
						$location.path('/');
					});
				}
				else
				{
					$location.path('/');
				}
			},
			function(error) {
				console.log(error);
				$location.path('/');
			});
		}
		else
		{
			$location.path('/');
		}
	}
	
}]);
