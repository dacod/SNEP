<?php

class Snep_Form_SubForm extends Snep_Form {
    /**
     * Whether or not form elements are members of an array
     * @var bool
     */
    protected $_isArray = true;

    public function __construct($legend, $options = null) {
        parent::__construct($options);
        $this->removeDecorator('form');
        $this->addDecorator("fieldset", array("legend" => $legend));
    }
}
