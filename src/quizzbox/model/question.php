<?php

class question extends \Illuminate\Database\Eloquent\Model {
	protected $table = 'question';
	protected $primaryKey = 'id';
	public $timestamps = false;

	public function reponses() {
		return $this->hasMany('quizzbox\model\reponse', 'id_question');
	}

    public function quizz() {
		return $this->belongsTo('games\model\Users', 'id_quizz');
	}
}
