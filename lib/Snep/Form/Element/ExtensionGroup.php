<?php

class Snep_Form_Element_ExtensionGroup extends Zend_Form_Element_Select {
    public function __construct($spec, $options = null) {
    	$this->addMultiOption("", "");
        $groups = new Snep_GruposRamais();
        $extengroups = array();
        foreach ($groups->getAll() as $group) {
            $extengroups[$group['id_extensiongroup']] = $group['ds_name'];
        }
        $this->addMultiOptions($extengroups);
        parent::__construct($spec, $options);
    }
}
