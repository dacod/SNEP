<?php

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {
    public function indexAction() {
        // Direcionando para o "snep antigo"
        $this->_redirect("../src/sistema.php");
    }
}
