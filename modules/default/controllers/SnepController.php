<?php

require_once 'Zend/Controller/Action.php';

/**
 * Correção de leitura inadequada de request por conta da falta de mod_rewrite.
 */
class SnepController extends Zend_Controller_Action {
    public function indexAction() {
        // Direcionando para o "snep antigo"
        $this->_redirect("/");
    }
}
