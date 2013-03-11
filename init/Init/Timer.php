<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Clase para controlar tiempos de carga en secciones de la web.
 * Mode of use:
 *   $obj = new Timer( 'Timer name 1' );
 *   $obj->end();
 *   $obj->result();
 */
class Init_Timer {
	const COLLECTION = 'init.Timers';
	//
	public static $i = 0;
	public static $timers = array();

	public $type;
	public $data;
	public $start;
	public $end;

	public function __construct($type = '', $data = '') {
		$this->start = microtime(TRUE);

		if (empty($type)) $type = 'Timer '. self::$i++;


        $this->type = $type;
        $this->data = $data;

        self::$timers[] =& $this;
	}

	/**
	 * @author Sergio Toro <yiti007@gmail.com>
	 * Finaliza el timer
	 * @return bool TRUE al acabar
	 */
	public function end() {
		$this->end = microtime(TRUE);
		return TRUE;
	}
	
	/**
	 * @author Sergio Toro <yiti007@gmail.com>
	 * Finaliza el Timer si no lo está y devuelve un array con el resultado
	 * @return array Resultado del timer
	 */
	public function result() {
		if (empty($this->end)) $this->end();
		$total = $this->end-$this->start;
		return array(
			'type' => trim($this->type),
			'time' => $total,
			'status' => self::getStatus($total),
			'data' => $this->data
		);
	}
	/**
	 * @author Sergio Toro <yiti007@gmail.com>
	 * Dinaliza todos los timers e inserta en MongoDB el resultado
	 * @return bool TRUE
	 */
	public static function logAllTimers(){
		$results = array(
			'date' => new MongoDate(),
			'timers' => array()
		);
		foreach (self::$timers as $timer) {
			$results['timers'][] = $timer->result(TRUE);
		}
		$init =& get_instance(); // Obtener instancia de ejecución (Init_Controller)
		$init->mdb->insert(self::COLLECTION, $results);
		return TRUE;
	}

	/**
	 * @author Sergio Toro <yiti007@gmail.com>
	 * Devuelve en texto una valoración del tiempo que ha tardado la ejecución
	 * @param  float $time microtime
	 * @return string
	 */
	public static function getStatus($time) {
		if ($time > 0.01)
			return 'SLOW';
		else if ($time < 0.006)
			return 'FAST';
		return 'NORMAL';
	}

}