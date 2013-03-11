<?php

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Clase que enruta la petición MVC
 */
class Init_Router {
    private $path;
    private $action;
    private $parameters;
    private $controller;

    public function __construct($url) { 
        $this->route($url);
    }

    public function &getController() {
        return $this->controller;
    }

    /**
     * Función que enruta la REQUEST_URI e inicia el controller
     * @param string $url URL to route
     * @return object Controller iniciado
     */
    public function &route($url) {
        // Escapar URL y prepararla para utilizarla
        $route = $this->prepareRoute($url);
        $arr_path = array();
        if (!empty($route)) $arr_path = explode('/', $route);
        $this->requireController($arr_path); // Obtener controller
        return $this->controller;
    }

    /**
     * Función que inicia el controller y ejecuta la ruta
     * @return bool If FALSE, error happened
     */
    public function executeController() {
        $this->controller->__init($this->path, $this->action);

        if (!empty($this->parameters)) 
            return call_user_func_array(array($this->controller, $this->action), $this->parameters);
        else 
            return call_user_func(array($this->controller, $this->action));
    }

    /**
     * Función que escapa la ruta y la limpia para usarla
     * @param  string $url Url a escapar
     * @return string      Ruta lista para ser utilizada
     */
    private function prepareRoute($url) {
        $url = str_replace(array('\\', '..'), '', $url); // Remove linux 
        $url_path = parse_url($url, PHP_URL_PATH);
        if ($url_path === FALSE) throw new Init_Exception(Init_Exception::ROUTER_BAD_QUERY_SYNTAX);
        $path_to_route = urldecode(substr($url_path, strlen(WEB_ROOT)));
        if (substr($path_to_route, -1)=='/') $path_to_route = substr($path_to_route,0,-1);
        return $path_to_route;
    }

    /**
     * Función que obtiene que fichero ejecutar
     * @param  array $arr_path      Array con el path a probar array('user', 'media')
     * @param  string $action       Función a ejecutar en el controller (usada en la recursión)
     * @param  array  $parameters   Parámetros para la función
     * @return array                Array con 'file_path', 'class', 'path', 'action', 'parameters'
     */
    private function requireController($arr_path, $action = NULL, $parameters = array()) {
        $route_files = array();
        
        if (!empty($arr_path)) {
            $path = implode('/', $arr_path);
            $class = ucfirst($arr_path[count($arr_path)-1]);
            $route_files[] = array('file' => APP_CONTROLLERS . "{$path}/index.php", 'class' => 'Index');
            $route_files[] = array('file' => APP_CONTROLLERS . "{$path}.php", 'class' => $class);
        }
        else {
            $path = '';
            $route_files[] = array('file' => APP_CONTROLLERS . "index.php", 'class' => 'Index');
        }
        foreach ($route_files as $route_file) {
            if (!file_exists($route_file['file'])) continue;
            // Encontrado fichero válido, procedemos a iniciarlo
            require_once $route_file['file'];
            // Build namespaced file name
            $controller = 'CT_' . $route_file['class'];
            $this->controller = new $controller();
            // Comprobamos que existe el método 
            if (!method_exists($this->controller, $action)) {
                if (!empty($action)) $parameters[] = $action;
                $action = 'default_action';
                if (!method_exists($this->controller, $action)) throw new Init_Exception(Init_Exception::ROUTER_CONTROLLER_ERROR);
            }
            $this->path = '/' . $path;
            $this->action = $action;
            // Ponemos en orden los parámetros
            $this->parameters = array_reverse($parameters);
            return TRUE;
        }
        // Si esta vacío, no podemos seguir enrutando, hay un error
        if (empty($arr_path)) throw new Init_Exception(Init_Exception::ROUTER_CONTROLLER_ERROR);
        // Continuamos al siguiente nivel...
        if (!empty($action)) $parameters[] = $action;
        $action = array_pop($arr_path);
        return $this->requireController($arr_path, $action, $parameters);     
    }
    
}
