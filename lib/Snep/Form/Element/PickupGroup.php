<?php

class Snep_Form_Element_PickupGroup extends Zend_Form_Element_Select {
    public function __construct($spec, $options = null) {
        $this->addMultiOption("", "");
        $pickupgroup = new Snep_PickupGroups_Manager();
        $pickupgroups = array();
        foreach ($pickupgroup->fetchAll() as $key => $val) {
        	$pickupgroups[$val->id_pickupgroup] = $val->ds_name;
        }
        $this->addMultiOptions($pickupgroups);
        parent::__construct($spec, $options);
    }
}
