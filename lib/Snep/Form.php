<?php
require_once 'Zend/Form.php';

class Snep_Form extends Zend_Form {

    public function  __construct($options = null) {
        parent::__construct($options);

        $this->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('dd' => 'HtmlTag'), array('tag' => 'dd')),
            array('Label', array('tag' => 'dt')),
            array(array('elementDiv' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_element'))
        ));
    }

    public function setButtom() {
        $this->setButton();
    }

    public function setButton() {
        $submit = new Zend_Form_Element_Submit("submit", array("label" => "Salvar"));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator('HtmlTag', array('tag' => 'li'));
        $submit->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'menus', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));

        $this->addElement($submit);

        $back = new Zend_Form_Element_Button("buttom", array("label" => "Cancelar" ));
        $back->setAttrib("onclick", "location.href='javascript:history.back();'");
        $back->removeDecorator('DtDdWrapper');
        $back->addDecorator('HtmlTag', array('tag' => 'li'));
        $back->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'menus', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));

        $this->addElement($back);
    }

}

