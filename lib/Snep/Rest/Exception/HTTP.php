<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HTTP
 *
 * @author guax
 */
class Snep_Rest_Exception_HTTP extends Exception {

    protected $errorMessage = "";

    public function __construct($message = "Server Error", $code = 503, $errorMessage = "Server Error") {
        parent::__construct($message, $code);
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

}
