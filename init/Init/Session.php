<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Store $_SESSION in MongoDB
 * @method {array} 	read() 		read($id) 					Read session
 * @method {bool} 	write() 	write($id, $sessionData) 	Store session
 * @method {bool} 	destroy() 	destroy($id) 				Destroy session
 */
class Init_Session {
	const COLLECTION = 'init.UserSessions';
	protected $max_time;
	protected $mdb;

	public function __construct($mdb) {
		$this->mdb = $mdb;
		$this->max_time = get_cfg_var("session.gc_maxlifetime");
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		session_start(); // Start session
	}
	public function open() { return TRUE; }
	public function close() { return TRUE; }

	/**
	 * Read session
	 * @param  string $id  	Session identifier
	 * @return array     	Session data
	 */
	public function read($id) {
		$doc = $this->mdb->findOne(self::COLLECTION, array( '_id'=> (string) $id), array( 'sessionData'=> 1 ));
		return $doc['sessionData'];
	}
	/**
	 * Guardar la sesión
	 * @param  string $id   Session identifier
	 * @param  array $data 	Session data
	 * @return bool       
	 */
	public function write($id, $sessionData) { 
		if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) $remote_addr = $_SERVER['HTTP_CF_CONNECTING_IP'];
		else $remote_addr = $_SERVER['REMOTE_ADDR'];
		$data = array(
			"_id" => $id, 
			"sessionData" => $sessionData, 
			'remote_addr' => $remote_addr,
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			"timeStamp" => time()
		);
		$this->mdb->save(self::COLLECTION, $data);
		return TRUE;
	}
	/**
	 * Destroy session
	 * @param  string $id Session identifier
	 * @return bool
	 */
	public function destroy($id) {
		$this->mdb->remove(self::COLLECTION, array( '_id'=> (string) $id ));
		return TRUE;
	}
	/**
	 * Deletes expired sessions
	 */
	public function gc() {
		$agedTime = time() - $this->max_time;
		$this->mdb->remove(self::COLLECTION, array( 'timeStamp'=> array( '$lt' => $agedTime ) ));
	}
}