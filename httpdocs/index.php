<?php
/**
 * Display config.php syntax errors
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Prevent external includes from not loading such as Google Webfonts
 * https://developers.google.com/webfonts/docs/troubleshooting
 */
header('Access-Control-Allow-Origin: *');


/**
 * Require config from application folder.
 */
require_once '../config.php';

/**
 * Start InitMVC Magic
 */
$init = new Init();