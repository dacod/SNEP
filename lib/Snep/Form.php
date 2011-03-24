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

    public function setButtom($name = null) {
        $this->setButton($name);
    }

    public function setButton($name = null) {

        $i18n = Zend_Registry::get("i18n");

        $submit = new Zend_Form_Element_Submit("submit", array("label" => ($name ? $i18n->translate($name) : $i18n->translate("Salvar"))));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator('HtmlTag', array('tag' => 'li'));
        $submit->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'menus', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));

        $this->addElement($submit);

        $back = new Zend_Form_Element_Button("buttom", array("label" => $i18n->translate("Cancelar")));
        $back->setAttrib("onclick", "location.href='javascript:history.back();'");
        $back->removeDecorator('DtDdWrapper');
        $back->addDecorator('HtmlTag', array('tag' => 'li'));
        $back->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'menus', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));

        $this->addElement($back);
    }

    /**
     * Inserts two selections and buttons to control the elements between them.
     *
     * @param string $name - Define elements id, important to javascript interaction
     * @param string $start_label
     * @param array $start_itens
     * @param string $end_label
     * @param array $end_itens
     */
    public function setSelectBox($name, $start_label, $start_itens, $end_label, $end_itens = false) {

        $i18n = Zend_Registry::get("i18n");

        $start_box = new Zend_Form_Element_Multiselect("box");
        $start_box->setMultiOptions( $start_itens );
        $start_box->removeDecorator('DtDdWrapper');
        $start_box->addDecorator('HtmlTag', array('tag' => 'li'));
        $start_box->setAttrib('id', $name.'_box');
        $start_box->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selects', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));
        $start_box->setLabel( $i18n->translate( $start_label ) );
         
        $end_box = new Zend_Form_Element_Multiselect("box_add");
        if($end_itens){
            $end_box->setMultiOptions( $end_itens );
        }
        $end_box->removeDecorator('DtDdWrapper');
        $end_box->setAttrib('id', $name.'_box_add');
        $end_box->addDecorator('HtmlTag', array('tag' => 'li'));
        $end_box->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selects', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $end_box->setLabel( $i18n->translate( $end_label ) );

        $add_action = new Zend_Form_Element_Button( $i18n->translate('Adicionar'));
        $add_action->removeDecorator("DtDdWrapper");        
        $add_action->setAttrib('id', $name.'_add_bt');

        $remove_action = new Zend_Form_Element_Button( $i18n->translate('Remover'));
        $remove_action->removeDecorator("DtDdWrapper");        
        $remove_action->setAttrib('id', $name.'_remove_bt');
        
        $this->addElements(array($start_box,
                                 $add_action,
                                 $remove_action,
                                 $end_box));

    }

}

