<?php
namespace quizzbox\control;
class QuizzboxController {
    /* Attribut pour stocker l'objet HttpRequest */
    private $request = null;

    public function __construct(\quizzbox\utils\HttpRequest $http_req){
        $this->request = $http_req;
    }

    /* ... */
}
