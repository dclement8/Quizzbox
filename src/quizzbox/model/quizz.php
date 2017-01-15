<?php

class quizz extends \Illuminate\Database\Eloquent\Model {
	protected $table = 'quizz';
	protected $primaryKey = 'id';
	public $timestamps = false;

	public function questions() {
		return $this->hasMany('quizzbox\model\question', 'id_quizz');
	}

    public function categorie() {
    	return $this->belongsTo('games\model\categorie', 'id_categorie');
    }

    public function joueurs() {
		return $this->belongsToMany(
			'games\model\joueur',
			'jouer',
			'id_quizz', 'id_joueur'
		);
	}
}
