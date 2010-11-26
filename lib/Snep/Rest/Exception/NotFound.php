<?php

class Snep_Rest_Exception_NotFound extends WebApi_Exception_HTTP {
    public function  __construct($message = "Not Found") {
        parent::__construct($message, 404, "Not Found");
    }
}
