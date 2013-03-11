<?php

/**
 * Example controller
 */
class CT_Index extends Controller {
    
    // Init parent Controller
    public function __init($path = FALSE, $action = FALSE) {
        parent::__init($path, $action);
    }

    public function default_action() {
        $this->output('main.php');
    }

}