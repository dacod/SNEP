<?php
require_once 'Zend/Form.php';

class Snep_Form_Sectioned extends Zend_Form {

    public function  __construct($options = null) {
        parent::__construct($options);

        $this->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr'))
        ));

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            array('Form', array('class' => 'snep_form sectioned_form'))
        ));

        $this->setButton();
    }

    public function getElementDecorators() {
        return $this->_elementDecorators;
    }


    protected function setButton() {
        $submit = new Zend_Form_Element_Submit("submit", array("label" => "Salvar"));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator(array("opentd" => 'HtmlTag'), array('class' => 'form_control', "colspan" => 2, 'tag' => 'td', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));
        $submit->addDecorator(array("opentr" => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));
        $submit->setOrder(100);

        $this->addElement($submit);

        $back = new Zend_Form_Element_Button("cancel", array("label" => "Cancelar" ));
        $back->setAttrib("onclick", "location.href='javascript:history.back();'");
        $back->removeDecorator('DtDdWrapper');
        $back->addDecorator(array("closetd" => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $back->addDecorator(array("closetr" => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $back->setOrder(101);

        $this->addElement($back);
    }

}
