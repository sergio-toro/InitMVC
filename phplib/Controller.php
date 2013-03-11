<?php
/**
 * Example Base controller file
 */
class Controller extends Init_Controller {
    
    /**
     * Javascripts to process and assign
     * @var array
     */
    protected $js = array(
        'example/history.js'            => array( 'minify'=> FALSE, 'translate'=> FALSE ), 
        'example.js'                    => array( 'minify'=> TRUE, 'translate'=> TRUE),
    );
    /**
     * Css to process and assign
     * @var array
     */
    protected $css = array(
        'example.css' => array( 'minify' => TRUE )
    );

    /**
     * Controller initialitzer, use __init() instead of __construct()
     * @param  boolean $path   Current execution path
     * @param  boolean $action Current action
     */
    public function __init($path = FALSE, $action = FALSE) {
        // Initialitzacion code here
        Init_Template::assign('lang', Init_Language::getCurrent()); // Assign current language to all templates

        // Full computed rute path+action (without parameters)
        // $path = $path . '/' . $action
    }
   

    /**
     * Output template wrapped into parent (index.php) template
     * @param  string $include_file Template to fetch and wrap
     * @param  array  $data         Data to be assigned
     */
    protected function output($file, $data = array()) {
        
        // If is an ajaz request just display content and finish
        if (Init_Url::isAjax()) {
            $this->display($file, $data);
        }
        else { 
            // Wrap given file into parent index.php template
            $index_data = array(
                'file' => $file,                        // Assign file to template
                'data' => $data,                        // Assign file data to template 
                'js' => Init_JS::process($this->js),    // Assign processed javascripts
                'css' => Init_CSS::process($this->css)  // Assign processed css
            ); 
            $this->display('index.php', $index_data);
        }
    }

    /**
     * Output template content
     * @param  string $file Template to fetch and Output
     * @param  array  $data Data to be assigned
     */
    protected function display($file, &$data = array()) {
        Init_Template::display($file, $data);
    }

}