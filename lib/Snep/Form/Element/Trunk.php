<?php

class Snep_Form_Element_Trunk extends Zend_Form_Element_Select {
    public function __construct($spec, $options = null) {
        foreach (PBX_Trunks::getAll() as $trunk) {
            $data[$trunk->getId()] = $trunk->getName();
        }
        $this->addMultiOptions($data);
        parent::__construct($spec, $options);
    }
}
