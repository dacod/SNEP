<?php

class Snep_Rest_Exception_MethodNotAllowed extends Snep_Rest_Exception_HTTP {
    public function  __construct($message = "Method Not Allowed") {
        parent::__construct($message, 405, "Method Not Allowed");
    }
}
