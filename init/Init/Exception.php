<?php
/**
 * @author Sergio Toro <ytii007@gmail.com>
 * Class to handle application errors
 * @method {string} get_message() get_message($code) Get pretty error message
 */
class Init_Exception extends Exception {

	/****************************************************************
	* Errores de Init_Mongo
	*****************************************************************/
	const MONGO_CONNECTION 			= -100;
	/****************************************************************
	* Errores de Init_Router
	*****************************************************************/
	const ROUTER_BAD_QUERY_SYNTAX	= 51;
	const ROUTER_CONTROLLER_ERROR	= 52;
	/****************************************************************
	* Errores de Init_Email
	*****************************************************************/
	const EMAIL_SEND_ERROR			= 100;
	/****************************************************************
	* JS and CSS errors
	*****************************************************************/
	const JS_FILES_ARRAY			= 150;
	const CSS_FILES_ARRAY			= 151;
	/****************************************************************
	* Errores de Init_Template
	*****************************************************************/
	const TEMPLATE_NOT_FOUND		= 200;
	/****************************************************************
	* HTTP Errors
	*****************************************************************/
	const URL_NOT_FOUND_404			= 404;
	const URL_DOMAIN_MISSCONFIGURED	= 425;

	//
	const TODO					= 999;


	public function __construct($code, $message = '', $previous = NULL) {
		$msg = $this->get_message($code);
		if ($message != '') $msg .= " {$message}";
		parent::__construct($msg, $code, $previous);
		Init_Log::insert('Init_Exception', array( 'code'=> $code, 'errorMessage'=> $msg), 'error');
	}

	/**
	 * Get pretty error message
	 * @param  int $code 	Error code
	 * @return string       Pretty error message.
	 */
	private function get_message($code){
		switch ($code) {
			case self::TODO: return 'This functionality is not yet implemented.';
			
			/****************************************************************
			* Errores de MongoDB
			*****************************************************************/
			case self::MONGO_CONNECTION: 			return 'Failed to connect to MongoDB.';

			/****************************************************************
			* Errores de Router
			*****************************************************************/
			case self::ROUTER_BAD_QUERY_SYNTAX: 	return 'Query URL syntax error.';
			case self::ROUTER_CONTROLLER_ERROR: 	return 'Error loading controller.';

			/****************************************************************
			* Errores de Email
			*****************************************************************/
			case self::EMAIL_SEND_ERROR: 			return 'Email send error. PHPMailer error: ';

			/****************************************************************
			* JS and CSS errors
			*****************************************************************/
			case self::JS_FILES_ARRAY: 				return 'JS files to process must be a valid array.';
			case self::CSS_FILES_ARRAY: 			return 'CSS files to process must be a valid array.';

			/****************************************************************
			* Errores de Init_Template
			*****************************************************************/
			case self::TEMPLATE_NOT_FOUND: 			return 'Template not found. File: ';

			/****************************************************************
			* HTTP Errors
			*****************************************************************/
			case self::URL_NOT_FOUND_404: 			return 'Not found.';
			case self::URL_DOMAIN_MISSCONFIGURED: 	return 'Missconfigured domain HTTP_HOST or SERVER_NAME.';
			//
			// DEFAULT
			default: return 'Unknown error.';
		}
	}
}