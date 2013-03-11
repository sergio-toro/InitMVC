<?php
/**
* @author Sergio Toro
* Función para obtener parámetros y escaparlos.
* @param string $field 	Key a buscar en $_REQUEST
* @param string $type 	Tipo de datos a validar
* @param mixed $default Devolución por defecto si no se encuentra $field
* @return mixed
*/
function _gf($field, $type = FALSE, $default = FALSE) {
	if (!isset($_REQUEST[$field])) return $default;
	$value = $_REQUEST[$field];
	switch($type) {
		case 'bool': 
			if ($value === TRUE OR $value==1 OR strtolower($value)=='true') return TRUE;
			return FALSE;
		break;
		case 'int': return intval($value);
		case 'float': return floatval($value);
		case 'string': return trim($value);
		case 'strip_tags': return trim(strip_tags($value));
		default: return $value;
	}
}

/**
* @author Sergio Toro
* Reference to the Init_Controller method.
* Returns current Init instance object
* @return object
*/
function &get_instance() {
	return Init_Controller::get_instance();
}

/**
 * Modifies a string to remove all non ASCII characters and spaces.
 * http://sourcecookbook.com/es/recipes/8/function-to-slugify-strings-in-php
 */
function slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
     // transliterate
    if (function_exists('iconv')) $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text); // lowercase
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    if (empty($text)) return 'n-a';
    return $text;
}

 
 
