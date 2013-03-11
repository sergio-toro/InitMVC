<?php

class Init_Log 
{
    const COLLECTION = 'init.Log';

    /**
     * Insert new log
     * @param  string $type   Log identifier
     * @param  string $status Log status: info, warning, error.
     * @param  array $data    Data to log, typecasted to array
     */
    public static function insert($type, $data, $status = 'info') {
        // Only log if enabled
        if (!LOG) return;

        $init =& get_instance(); // Obtener instancia de ejecución (Init_Controller)

        $available = array('info', 'warning', 'error');
        if (!in_array($status, $available)) $status = 'info';

        $log = array(
            'type' => (string) $type,
            'date' => new MongoDate(),
            'status' => (string) $status,
            'data' => (array) $data,
        );
        $init->mdb->insert(self::COLLECTION, $log);
    }
}