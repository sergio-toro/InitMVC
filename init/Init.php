<?php
// Requerir autoload
require_once 'autoload.php';
// Requerir funciones útiles
require_once 'functions.php';

/**
* Clase que inicia el MVC
* @author Sergio Toro
*/
class Init {
	public function __construct() {
		try {
			if (TIMERS) $timer = new Init_Timer('animet.org', 'Application runtime');
			// Start MongoDB object
			$mdb = new Init_Mongo(MONGODB_IP, MONGODB_NAME, $GLOBALS['MongoDB_OPTIONS']);
			// Start user session
			if (SESSION) { 
				if (SESSION_USE_MONGODB) $s = new Init_Session($mdb);
				else session_start();
			}
			// Unify url, redirect If URL has a trailing slash and domain name
			if (URL_UNIFIER) Init_Url::unify($_SERVER['REQUEST_URI']);

			// Start Language class
			Init_Language::init();
			// Start Country class
			Init_Country::init();

			// Ensure language in folder (Using redirections), or throw 404 error.
			if (LANGUAGE_BY_FOLDER) 
				$_SERVER['REQUEST_URI'] = Init_Url::languageByFolder();
			// Execute router, searchs wich controller will be executed
			$router = new Init_Router($_SERVER['REQUEST_URI']);
			$controller =& $router->getController();
			
			// MongoDB object reference in controller
			$controller->mdb =& $mdb; 
			// Language object reference in controller
			$router->executeController();

			if (TIMERS) Init_Timer::logAllTimers();
		} catch (Exception $e) {
			// @TODO: Lanzar template de error
			die("Error[{$e->getCode()}] {$e->getMessage()}");
			//$this->display('index.php', $index_data);
		}
	}
}