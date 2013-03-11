<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * $mdb = new Init_Mongo('mongodb://localhost', 'dbname', array());
 * 
 * Find example:
 * $mdb->find('collection', $where)->skip(5)->limit(15)->sort(array())->doQuery();
 * 
 * Insert example:
 * $mdb->insert('collection', array('name'=>'example'));
 */
class Init_Mongo {
	public static $i = 0; // Usada para contar la cantidad de Querys (con DEBUG activo)
	// Variables de querys
	private $where = array();
	private $fields = array();
	private $skip;
	private $limit;
	private $sort;
	private $cursor;
	// Variables de conexion
	private $server;
	private $dbname;
	private $options;
	// Objetos de MongoDB
	private $connection = FALSE;
	private $db;
	private $collection;

	/**
	 * Constructor del objeto MongoDB
	 * @param string $server  Datos de conexión con el servidor de MongoDb
	 * @param string $dbname  Base de datos a utilizar
	 * @param array  $options Opciones de MongoDB
	 */
	public function __construct($server, $dbname, $options = array()) {
		$this->server = $server;
		$this->dbname = $dbname;
		$this->options = $options;
	}

	/**
	 * Función que inicia MongoDB
	 * @param  boolean $QueryOnMaster Query por master
	 * @return object                 Devuelve la propia instancia
	 */
	public function &init($QueryOnMaster = TRUE) {
		if ($this->connection) return $this;
		try {
			// Connect to Mongo ReplicaSet
			$this->connection = new MongoClient($this->server.$this->dbname, $this->options); 
			// Si $QueryOnMaster... todas las querys por primario.
			if ($QueryOnMaster) $this->connection->setReadPreference(Mongo::RP_PRIMARY); 
			else $this->connection->setReadPreference(Mongo::RP_SECONDARY); 
		} catch(Exception $e) {
			throw new Init_Exception(Init_Exception::MONGO_CONNECTION);
		}
		return $this->selectDB($this->dbname);
	}

	/**
	 * Selecciona la base de datos
	 * @param string $database Base de datos de MongoDB
	 * @return $this
	 */
	public function &selectDB($database) {
		$this->db = $this->init()->connection->selectDB($database);
		return $this;
	}

	/**
	 * Selecciona la collection
	 * @param string $collection Collection a seleccionar
	 * @return $this
	 */
	public function &selectCollection($collection) {
		$this->collection = $this->init()->db->selectCollection($collection);
		return $this;
	}

	/**
	 * Salta resultados
	 * @param int $skip Cantidad de resultados a saltar
	 * @return $this
	 */
	public function &skip($skip) {
		$this->skip = $skip;
		return $this;
	}

	/**
	 * Limita la cantidad de resultados a devolver
	 * @param int $limit Cantidad de resultados
	 * @return $this
	 */
	public function &limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Ordenar resultados
	 * @param array $sort Array con las reglas de ordenación
	 * @return $this
	 */
	public function &sort($sort) {
		$this->sort = $sort;
		return $this;
	}

	/**
	 * Busqueda de resultados
	 * @param string $collection 	Collection a seleccionar
	 * @param array $where 			Reglas de búsqueda
	 * @param array $fields  		Campos a devolver
	 * @return $this
	 */
	public function &find($collection, $where = array(), $fields = array()) {
		$this->where = $where;
		$this->fields = $fields;
		return $this->selectCollection($collection);
	}

	/**
	 * Contar cantidad de resultados
	 * @param string $collection 	Collection a seleccionar
	 * @param array $where 			Reglas de búsqueda
	 * @param array $limit  		Límite de resultados a contar
	 * @param array $skip  			Cantidad de resultados a saltar desde el inicio
	 * @return int 					Cantidad de resultados
	 */
	public function count($collection, $where = array(), $limit = 0, $skip = 0) {
		if (TIMERS) {
			$timer = new Init_Timer("MDB count[{$collection}] ".self::$i++, array('where'=> $where, 'limit'=> $limit, 'skip'=>$skip));	
		}
		$res = $this->selectCollection($collection)->collection->count($where, $limit, $skip);
		if (TIMERS) $timer->end();
		return $res;
	}

	//
	/**
	 * Después de llamar un doQuery, devuelve el total de resultados
	 * @param  boolean $foundOnly 	Contar los obtenidos solo, no el total
	 * @return int             		Cantidad de resultados
	 */
	public function countResults($foundOnly = FALSE) {
		return $this->cursor->count($foundOnly);
	}

	/**
	 * Busqueda de resultados
	 * @param string $collection 	Collection a seleccionar
	 * @param array $where 			Reglas de búsqueda
	 * @param array $fields  		Campos a devolver
	 * @return $this
	 */
	public function &findOne($collection, $where = array(), $fields = array()) {
		$this->cursor = null; // No hay cursor ni countResults con esta función
		if (TIMERS) {
			$timer = new Init_Timer("MDB findOne[{$collection}] ".self::$i++, $where);	
		}
		$result = $this->selectCollection($collection)->collection->findOne($where, $fields);
		if (TIMERS) $timer->end();
		return $result;
	}

	/**
	 * Guarda un registro, si no existe lo crea
	 * @param  string $collection 	Collection a seleccionar
	 * @param  array $data       	Datos a guardar
	 * @param  array  $options    	Opciones de la Query
	 * @return mixed             	MongoCollection save result
	 */
	public function save($collection, $data, $options = array()) {
		if (TIMERS) {
			$timer = new Init_Timer("MDB save[{$collection}] ".self::$i++, $data);	
		} 
		$result = $this->selectCollection($collection)->collection->save($data, $options);
		if (TIMERS) $timer->end();
		return $result;
	}

	/**
	 * Inserta un registro
	 * @param  string $collection 	Collection a seleccionar
	 * @param  array $data       	Datos a guardar
	 * @param  array  $options    	Opciones de la query
	 * @return mixed             	MongoCollection insert result
	 */
	public function insert($collection, $data, $options = array()) {
		if (TIMERS) {
			$timer = new Init_Timer("MDB insert[{$collection}] ".self::$i++, $data);	
		} 
		$result = $this->selectCollection($collection)->collection->insert($data, $options);
		if (TIMERS) $timer->end();
		return $result;
	}

	/**
	 * Actualiza un registro
	 * @param  string $collection 	Collection a seleccionar
	 * @param  array $where       	Reglas de búsqueda del registro
	 * @param  array $update      	Datos a modificar
	 * @param  array $options    	Opciones de la query
	 * @return mixed             	MongoCollection update result
	 */
	public function update($collection, $where, $update, $options = array()) {
		$this->selectCollection($collection);
		if (TIMERS) {
			$timer = new Init_Timer("MDB update[{$collection}] ".self::$i++, array('where' => $where, 'update' => $update));	
		} 
		$result = $this->collection->update($where, $update, $options);
		if (TIMERS) $timer->end();
		return $result;
	}

	/**
	 * Elimina un registro
	 * @param  string $collection 	Collection a seleccionar
	 * @param  array $where       	Reglas de búsqueda del registro
	 * @param  bool $one    		If TRUE, solo remueve un registro
	 * @return bool             	MongoCollection remove result
	 */
	public function remove($collection, $where, $one = FALSE) {
		if (TIMERS) {
			$timer = new Init_Timer("MDB remove[{$collection}] " . self::$i++, $where);	
		} 
		$options = array(
			'justOne'=> $one
		);
		$result = $this->selectCollection($collection)->collection->remove($where, $options);
		if (TIMERS) $timer->end();
		return $result;
	}

	/**
	 * Ejecuta la consulta y devuelve un array con los resultados
	 * @return Array Resultados de la búsqueda
	 */
	public function &doQuery() {
		$this->cursor = $this->collection->find($this->where, $this->fields);
		if ($this->skip) $this->cursor->skip($this->skip);
		if ($this->limit) $this->cursor->limit($this->limit);
		if ($this->sort) $this->cursor->sort($this->sort);
		if (TIMERS) {
			$timer = new Init_Timer('MDB find '.self::$i++, $this->cursor->info());	
		} 
		$this->clear();
		$k = array();
		while ($this->cursor->hasNext()) {
		    $k[] = $this->cursor->getNext();
		}
		if (TIMERS) $timer->end();
		return $k;
	}

	/**
	 * Función que limpia variables de búsqueda y filtrado
	 * @return object $this por referencia
	 */
	protected function &clear() {
		$this->where = array();
		$this->fields = array();
		$this->limit = null;
		$this->sort = null;
		$this->skip = null;
		return $this;
	}
}