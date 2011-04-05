<?php

class Snep_Form_Element_ExtensionGroup extends Zend_Form_Element_Select {
    public function __construct($spec, $options = null) {
        $groups = new Snep_GruposRamais();
        $data = array();
        foreach ($groups->getAll() as $group) {
            $data[$group['name']] = $group['name'];
        }
        $this->addMultiOptions($data);
        parent::__construct($spec, $options);
    }
}
