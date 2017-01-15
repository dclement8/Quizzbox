<?php
namespace quizzbox\view;
class QuizzboxView extends AbstractView {
    /* Constructeur
    *
    * On appelle le constructeur de la classe parent
    *
    */
    public function __construct($data) {
        parent::__construct($data);
    }

    /* ... */

    /*
     * Affiche une page HTML complète.
     *
     * En fonction du sélecteur, le contenu de la page changera.
     *
     */
    public function render($selector) {
        switch($selector) {
			/*case 'exemple':
                $breadcrumb = $this->renderBreadcrumb(array(array('Exemple', '/exemple/')));
				$main = $this->exemple();
				break;
			default:
                $breadcrumb = $this->renderBreadcrumb();
                $main = $this->default();
				break;*/
        }

        $style_file = $this->app_root.'css/main.css';
        $header 	= $this->renderHeader();
        $menu   	= $this->renderMenu();
		$messages	= $this->renderMessage();
		$footer		= $this->renderFooter();

        /*
         * Utilisation de la syntaxe HEREDOC pour écrire la chaine de caractère de
         * la page entière. Voir la documentation ici:
         *
         * http://php.net/manual/fr/language.types.string.php#language.types.string.syntax.heredoc
         *
         * Noter bien l'utilisation des variables dans la chaine de caractère
         *
         */

        $html = <<<EOT
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Quizzbox</title>
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<link rel="shortcut icon" href="{$this->app_root}/favicon.ico">
        <link rel="stylesheet" href="${style_file}">
		<script type="text/javascript" src="{$this->app_root}/js/main.js"></script>
    </head>
    <body>
        ${header}
        ${menu}
		${breadcrumb}
		${messages}
		<div class="container line">
			${main}
		</div>
		${footer}
    </body>
</html>
EOT;

        echo $html;
    }
}
