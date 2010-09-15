<?php

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {
    public function indexAction() {
        // Direcionando para o "snep antigo"
        $config = Zend_Registry::get('config');

        if( trim ( $config->ambiente->db->host ) == "" ) {            
            $this->_redirect("/installer/");

        }else{            
            $this->_redirect("../src/sistema.php");
        }
        
    }
}
