<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Clase para crear un Template básico en PHP
 */
class Init_Template {
    const TEMPLATES_PATH = APP_TEMPLATES; // Default template directory
    const COMPILE_PATH = APP_COMPILE; // Default template directory

    public static $assign = array();
    public static $refAssign = array();


    /**
     * Assign data
     * @param  string $key   Template variable name
     * @param  mixed $value  Data to assign
     */
    public static function assign($key, $value) {
        self::$assign[$key] = $value;
    }

    /**
     * Assign variable by reference
     * @param  string $key   Template variable name
     * @param  mixed $value  Data to assign
     */
    public static function assignByRef($key, &$value) {
        self::$assign[$key] =& $value;
    }


    /**
     * @author Sergio Toro <yiti007@gmail.com>
     * Función que ejecuta un template, devuelve su contenido
     * @param  string $file Template
     * @param  array $data  Datos a enviar al template
     * @return string       String HTML del template
     */
    public static function &fetch($file, $data = array()) {
        if (!self::checkFile($file)) return FALSE;

        self::mergeAssign($data);

        $closure = function(&$data, $file_path) {
            // 1. Open output buffer to catch all output
            // 2. Include file inside of buffer and let $data populate
            // 3. Get contents of buffer and close buffer
            extract($data, EXTR_OVERWRITE); // Extract array values to real variables for template
            ob_start();
            include $file_path;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        };
        // Hide undefined variable error
        $err = error_reporting();
        error_reporting($err & ~E_NOTICE);
        $output = $closure($data, self::getCompiled($file));
        error_reporting($err);
        return $output;
    }

    // Return contents of include + $values populated data
    public static function display($file, $data = array()) {
        echo self::fetch($file, $data);
    }

    /**
     * @author Sergio Toro <yiti007@gmail.com>
     * Compila el fichero y lo guarda en directorio de compilados
     * @param  string $file Path del ficherp php a compilar
     * @return string       Path del fichero compilado
     */
    private static function getCompiled($file) {
        $modify_time = filemtime(self::TEMPLATES_PATH . $file);
        $file_name = basename($file);
        $hash = Init_Language::getCurrent() . '_' . md5($file);
        //
        $compiled_file = $hash . '_' . $modify_time . '_' . $file_name;
        if (!file_exists(self::COMPILE_PATH . $compiled_file)) {
            self::gcCompiled(self::COMPILE_PATH, $hash.'_*'); // Clear old templares
            $content = file_get_contents(self::TEMPLATES_PATH . $file);
            // Translate content
            $TranslateAdmin = new Init_Translate_Content($content, 'template');
            $content = $TranslateAdmin->translate();
            file_put_contents(self::COMPILE_PATH . $compiled_file, $content);
        }
        // If we can find the file, we can use it.
        return self::COMPILE_PATH . $compiled_file;
    }

    /**
    * Merges assigned variables
    * @param array &$data 
    */
    private static function mergeAssign(&$data) {
        
        // Add assign to template data
        foreach (self::$assign as $key => $value) $data[$key] = $value;
        foreach (self::$refAssign as $key => &$value) $data[$key] =& $value;

        $data['lang'] = Init_Language::getCurrent();
        $data['country'] = Init_Country::getCurrent();
    }

    /**
     * Deletes old compiled templates
     * @param  string $dir     Compile path
     * @param  string $pattern File pattern to delete
     * @return bool
     */
    public static function gcCompiled($dir, $pattern) {
        $errors = 0;
        $files = glob($dir . $pattern);
        if (empty($files)) return TRUE;
        foreach ($files as &$file) {
            $errors += (unlink($file) === FALSE ? 1 : 0);
        }
        return $errors === 0 ? TRUE : FALSE;
    }

    /**
     * @author Sergio Toro <yiti007@gmail.com>
     * Comprueba que exista el fichero
     * @param  string $file Path del fichero a comprobar
     * @return bool       TRUE si existe / FALSE si no
     */
    private static function checkFile($file) {
        // If we can find the file, we can use it.
        if (file_exists(self::TEMPLATES_PATH . $file)) return TRUE;
        throw new Init_Exception(Init_Exception::TEMPLATE_NOT_FOUND, $file);
          
    }
}