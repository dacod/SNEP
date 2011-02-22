<?php

class Snep_Form_Element_Html extends Zend_Form_Element {
    public function __construct($script, $spec, $options = null) {
        parent::__construct($spec, $options);

        $this->setDecorators(array(
            'ViewScript',
            array(array('element' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr'))
        ));

        $this->getDecorator('ViewScript')->setViewScript($script);
    }
}
