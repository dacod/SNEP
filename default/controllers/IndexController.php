<?php

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {
    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Welcome to Snep PBX version %s", SNEP_VERSION);

        // Direcionando para o "snep antigo"
        $config = Zend_Registry::get('config');

        if( trim ( $config->ambiente->db->host ) == "" ) {            
            $this->_redirect("/installer/");
        }
        
    }
}
