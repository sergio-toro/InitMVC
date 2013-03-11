<?php
/**
 * If DEBUG = TRUE:
 *  - Init_Template class always compile templates
 */
define('DEBUG', TRUE);
/**
 * If TRUE, log timers into MongoDB Collection
 */
define('TIMERS', TRUE);
/**
 * If TRUE, save logs into MongoDB Collection
 */
define('LOG', TRUE);

/****************************************************************************************************
 * Application config
 ***************************************************************************************************/
if (!isset($_SERVER['SERVER_NAME'])) 
    $_SERVER['SERVER_NAME'] = 'example.com'; 

/**
 * Aplication full path
 */
define('APP_ROOT',  '/var/www/example.com/');  
/**
 * Init full path
 */
define('INIT_ROOT', '/var/www/example.com/init/');
//
define('APP_LIB_PATH',      APP_ROOT . 'phplib/');     
define('APP_CONTROLLERS',   APP_ROOT . 'controllers/');
define('APP_COMPILE',       APP_ROOT . 'compile/');    
define('APP_TEMPLATES',     APP_ROOT . 'templates/');  
define('APP_CRONJOBS',      APP_ROOT . 'cronJobs/');   
define('APP_TMP',           APP_ROOT . 'tmp/');        
define('APP_PUBLIC',        APP_ROOT . 'httpdocs/');   

// Public root
define('WEB_HOST', 'http://' . $_SERVER['SERVER_NAME']); 
define('WEB_ROOT', '/'); //Directorio web donde está la raíz de la aplicación


/****************************************************************************************************
 * URL config
 ***************************************************************************************************/
// If TRUE and URL with trailing slash, will be redirected without slash.
// Check domain name 
define('URL_UNIFIER', TRUE); // www.example.com/ => example.com, example.com/es/ => example.com/es


/****************************************************************************************************
 * Language config
 ***************************************************************************************************/
define('LANGUAGE_DEFAULT', 'en');           // Default language, If not in LANGUAGE_AVAILABLE, will be added
define('LANGUAGE_AVAILABLE', 'es,en,ca');   // List of available languages, separated by ','
define('LANGUAGE_BY_FOLDER', TRUE);         // If TRUE, enables language by URL
//Country config
define('COUNTRY_DEFAULT', 'ES'); 
// Set locale and timezone
setlocale(LC_ALL, "es_ES");
date_default_timezone_set("Europe/Madrid");



/****************************************************************************************************
 * Init_Translate config
 ***************************************************************************************************/
// If TRUE, translate template content using google translate
// Read more: https://developers.google.com/translate/v2/getting_started
define('TRANSLATE_GOOGLE', FALSE); 
define('TRANSLATE_GOOGLE_SERVER_KEY', '<YOUR-PRIVATE-SERVER-KEY>');
// If TRUE and translation not found then fallback to default text
define('TRANSLATE_FALLBACK_DEFAULT', TRUE); 


/****************************************************************************************************
 * MongoDB config
 ***************************************************************************************************/
define('MONGODB_IP',    'mongodb://127.0.0.1/');    // Your MongoDB Server IP
define('MONGODB_NAME',  'example');                 // Your MongoDB database               
// MongoDB options:  http://www.php.net/manual/en/mongoclient.construct.php
global $MongoDB_OPTIONS; 
$MongoDB_OPTIONS = array('timeout' => 1);


/****************************************************************************************************
 * Email config 
 * Read more: http://swiftmailer.org/docs/introduction.html
 ***************************************************************************************************/
define('EMAIL_FROM',        'info@example.com');
define('EMAIL_FROM_NAME',   'example.com');
//
define('EMAIL_PROTOCOL',    'mail'); // mail, sendmail, or smtp
define('EMAIL_TYPE',        'text/html'); // text/html
define('EMAIL_EMOGRIFY',    APP_PUBLIC . 'css/email.css');  // If FALSE, no use Emogrifier

// Sendmail config
define('SENDMAIL_PATH',     '/usr/sbin/sendmail -bs');

// SMTP Config
define('SMTP_HOST', 'smtp.gmail.com');      // SMTP Server address
define('SMTP_CRYPTO', 'ssl');               // Available encription: ssl, tls, ''
define('SMTP_PORT', 465);                   // Port, most common: 25, 465 or 587
define('SMTP_USER', 'info@example.com');    // Account username
define('SMTP_PASS', '');                    // Account password


/****************************************************************************************************
 * Session config
 ***************************************************************************************************/
define('SESSION', TRUE);                // Enables session, If FALSE disable LANGUAGE and COUNTRY
define('SESSION_USE_MONGODB', TRUE);    // If true, save native user session to MongoDB


/****************************************************************************************************
 * Javascript compile level
 ***************************************************************************************************/
/**
 * Read more: https://developers.google.com/closure/compiler/docs/compilation_levels
 */
define('JS_COMPILE_LEVEL', 'SIMPLE_OPTIMIZATIONS'); // WHITESPACE_ONLY / SIMPLE_OPTIMIZATIONS
    

/****************************************************************************************************
 * Display errors on DEBUG mode
 ***************************************************************************************************/
if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
else {
    error_reporting(ERR_LEVEL);
    ini_set('display_errors', 0);
}

/****************************************************************************************************
 * Set open basedir and include path
 ***************************************************************************************************/
ini_set('open_basedir', APP_ROOT . PATH_SEPARATOR . '/tmp/'); 
ini_set('include_path','.'. PATH_SEPARATOR . APP_LIB_PATH . PATH_SEPARATOR . INIT_ROOT);


/****************************************************************************************************
 * Accelerate includes, saved a lot of work to autoloader with common files
 ***************************************************************************************************/
// Root includes
require_once INIT_ROOT.'Init.php';
require_once INIT_ROOT.'Init/Model.php';
require_once INIT_ROOT.'Init/Controller.php';
require_once INIT_ROOT.'Init/Session.php';
require_once INIT_ROOT.'Init/Language.php';
require_once INIT_ROOT.'Init/Mongo.php';
require_once INIT_ROOT.'Init/Router.php';
if (TIMERS) require_once INIT_ROOT.'Init/Timer.php';

// Apliccation base controller
require_once APP_LIB_PATH.'Controller.php';