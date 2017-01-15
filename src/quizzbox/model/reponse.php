<?php
namespace quizzbox\model;

class reponse extends \Illuminate\Database\Eloquent\Model {
	protected $table = 'reponse';
	protected $primaryKey = 'id';
	public $timestamps = false;

    public function question() {
		return $this->belongsTo('games\model\question', 'id_question');
	}
}
