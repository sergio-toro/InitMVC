<?php
/**
* Autoloader de Clases, requires por carpeta y autoload antiguo.
* @author Sergio Toro
*/
spl_autoload_register(function ($class) {
	$class = str_replace('_','/',$class).'.php'; // Autoloader nuevo
	if (file_exists(APP_ROOT.'init/'.$class) OR 
		file_exists(APP_ROOT.'phplib/'.$class) OR 
		file_exists('./'.$class)) 
		require_once $class;
});
