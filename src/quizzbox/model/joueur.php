<?php
namespace quizzbox\model;

class quizz extends \Illuminate\Database\Eloquent\Model {
	protected $table = 'quizz';
	protected $primaryKey = 'id';
	public $timestamps = false;

    public function quizzs() {
		return $this->belongsToMany(
			'games\model\quizz',
			'jouer',
			'id_joueur', 'id_quizz'
		);
	}
}
