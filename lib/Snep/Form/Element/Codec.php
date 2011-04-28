<?php

class Snep_Form_Element_Codec extends Zend_Form_Element_Select {

    public function __construct($spec, $options = null) {
        $this->addMultiOptions(array(
            "ulaw" => "ulaw",
            "alaw" => "alaw",
            "ilbc" => "ilbc",
            "g729" => "g729",
            "gsm" => "gsm",
            "h264" => "h264",
            "h263" => "h263",
            "h263p" => "h263p"
        ));
        parent::__construct($spec, $options);
    }
}
