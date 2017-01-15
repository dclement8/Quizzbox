<?php
namespace quizzbox\model;

class categorie extends \Illuminate\Database\Eloquent\Model {
	protected $table = 'categorie';
	protected $primaryKey = 'id';
	public $timestamps = false;

	public function quizzs() {
		return $this->hasMany('quizzbox\model\quizz', 'id_categorie');
	}
}
