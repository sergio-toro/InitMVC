<?php 
class Model_ArrayToXML {

    /**
    * Función que encapsula una respuesta xml
    * @author Sergio Toro
    * @param array $data
    * @return string
    */
    public static function &toXml(&$data) {
	   
        $xml = "<?xml version='1.0' encoding='utf-8'?>\r\n";
        $xml .= "<animet>\r\n";
        $xml .= self::recursiveToXml($data);
	    $xml .= "</animet>";
	    return $xml;
    }
    /**
    * Función que convierte un array recursivamente a nodos xml.
    * @author Sergio Toro
    * @param array $data
    * @param string $indent
    * @return string
    */
    public static function &recursiveToXml(&$data, $indent = "\t") {
        $xml = '';
        foreach($data as $key => &$value) {
            // no numeric keys in our xml please!
            if (is_numeric($key)) $key = 'item';
            if (is_array($value)) { // if there is another array found recrusively call this function
                $xml .= "{$indent}<{$key}>\r\n";
                $xml .= self::recursiveToXml($value, $indent."\t");
                $xml .= "{$indent}</{$key}>\r\n";
            }
            else {

                if (preg_match('#[&<>\'"]#', $value))
                    $value = "<![CDATA[{$value}]]>";
                $xml .= "{$indent}<{$key}>{$value}</{$key}>\r\n";
            }
        } 
        return $xml;
    }
}
?>