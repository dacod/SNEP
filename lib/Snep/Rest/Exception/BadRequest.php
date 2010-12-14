<?php

class Snep_Rest_Exception_BadRequest extends Snep_Rest_Exception_HTTP {
    public function  __construct($message = "Bad Request") {
        parent::__construct($message, 400, "Bad Request");
    }
}
