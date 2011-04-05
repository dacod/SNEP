<?php

class Snep_Form_Element_PickupGroup extends Zend_Form_Element_Select {
    public function __construct($spec, $options = null) {
        $this->addMultiOption("", "");
        $this->addMultiOptions(Snep_PickupGroups_Manager::getAll());
        parent::__construct($spec, $options);
    }
}
