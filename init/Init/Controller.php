<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Default Controller, 
 */
class Init_Controller  {
	// Current controller instance
	private static $instance;
	//
	public $mdb 		= FALSE;
	public $language 	= FALSE;
	public $country 	= FALSE;

	/**
	 * Store current instance to static variable
	 */
	public function __construct() {
		self::$instance =& $this;
	}

	/**
	 * Starts controller
	 * @param  string $path   Controller path
	 * @param  string $action Controller action (function)
	 */
	public function __init($path = FALSE, $action= FALSE) { }
	
	/**
	* Get current instance, used to acces to public variables
	* @return object
	*/
	public static function &get_instance() {
		return self::$instance;
	}
}