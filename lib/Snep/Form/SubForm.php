<?php

class Snep_Form_SubForm extends Zend_Form {
    /**
     * Whether or not form elements are members of an array
     * @var bool
     */
    protected $_isArray = true;

    public function __construct($legend = null, $options = null, $name = null) {
        $this->addPrefixPath('Snep_Form', 'Snep/Form');
        parent::__construct($options);

        if($legend !== null) {
            $legend = new Snep_Form_Element_SectionTitle("title", array("label"=>$legend));
            $legend->setOrder(0);
            $this->addElement($legend);
        }

        $this->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element" . " $name"))
        ));

        $this->setDecorators(array(
            'FormElements'
        ));
    }
}
