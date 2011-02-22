<?php
require_once 'Zend/Form.php';

class Snep_Form_Simple extends Snep_Form_Sectioned {

    public function  __construct($options = null) {
        parent::__construct($options);

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            array('Form', array('class' => 'snep_form simple_form'))
        ));
    }

    protected function setButton() {
        $submit = new Zend_Form_Element_Submit("submit", array("label" => "Salvar"));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator(array("opentd" => 'HtmlTag'), array('class' => 'form_control_left' , 'tag' => 'td' ));
        $submit->addDecorator(array("opentr" => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));
        $submit->setOrder(100);

        $this->addElement($submit);

        $back = new Zend_Form_Element_Button("cancel", array("label" => "Cancelar" ));
        $back->setAttrib("onclick", "location.href='javascript:history.back();'");
        $back->removeDecorator('DtDdWrapper');
        $back->addDecorator(array("closetd" => 'HtmlTag'), array('class' => 'form_control_right', 'tag' => 'td'));
        $back->addDecorator(array("closetr" => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $back->setOrder(101);

        $this->addElement($back);
    }

}

