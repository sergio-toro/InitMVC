<?php
/**
* @author Sergio Toro <yiti007@gmail.com>
* Clase báse de todos los modelos, obtiene la instancia en ejecución.
*	- Provee el poder usar $this->db, $this->mdb
* 	- Usar cualquier $this->{var_name} dentro de un model
* NOTA: Sólo funciona con las variables definidas como public,
* en la clase que extienda de Init_Controller.
*/
class Init_Model {
	protected $mdb 		= NULL;
	protected $language = NULL;

	/**
	 * Provee el uso de variables públicas de Controller en cualquier Modelo que extienda de Init_Model
	 */
	public function __construct() {
		$init =& get_instance();
		// Obtener todas las variables PUBLICAS de la instancia
		if (is_object($init)) {
			foreach (get_object_vars($init) as $var_name => $var_value) { 
				$this->{$var_name} =& $init->{$var_name};
			}
		}
	}

	public function &getMongoDB() {
		return $this->mdb;
	}
}