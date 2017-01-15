<?php
namespace quizzbox;
use \Illuminate\Database\Capsule\Manager;

class AppInit {
    public static function bootEloquent() {
        if(!file_exists('conf/conf.php'))
            throw new \Exception('impossible de charger le fichier de configuration !');
        include('conf/conf.php');
		$db = new Manager();
		$db->addConnection([
            'driver' => $dbconfig['driver'],
            'host' => $dbconfig['host'],
            'database' => $dbconfig['database'],
            'username' => $dbconfig['user'],
            'password' => $dbconfig['password'],
            'charset' => $dbconfig['charset'],
            'collation' => $dbconfig['collation'],
            'prefix' => $dbconfig['prefix']
        ]);
		$db->setAsGlobal();
		$db->bootEloquent();
    }
}
