<?php
require_once 'Zend/Form.php';

class Snep_Form extends Zend_Form {

    public function  __construct($options = null) {
        $this->addPrefixPath('Snep_Form', 'Snep/Form');
        parent::__construct($options);

        $this->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'snep_form_element'))
        ));

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            array('Form', array('class' => 'snep_form'))
        ));

        $i18n = Zend_Registry::get("i18n");
        $submit = new Snep_Form_Element_Submit("submit", array("label" => $i18n->translate("Save")));
        $submit->setOrder(1000);
        $this->addElement($submit);
    }

    /**
     * Inserts two selections and buttons to control the elements between them.
     *
     * @param string $name - Define elements id. Important to javascript interaction
     * @param string $label
     * @param array $start_itens
     * @param array $end_itens
     */
    public function setSelectBox($name, $label, $start_itens, $end_itens = false) {

        $i18n = Zend_Registry::get("i18n");

        $header = new Zend_Form_Element_Hidden('elementHeader');
        $header->removeDecorator("DtDdWrapper")
               ->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selects', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));

        $start_box = new Zend_Form_Element_Multiselect("box");
        $start_box->setLabel( $i18n->translate( $label ) )
                  ->setMultiOptions( $start_itens )
                  ->removeDecorator('DtDdWrapper')
                  ->setAttrib('id', $name.'_box');

        $end_box = new Zend_Form_Element_Multiselect("box_add");
        if($end_itens) {
            $end_box->setMultiOptions( $end_itens );
            $end_box->setValue( array_keys($end_itens) );
        }
        $end_box->removeDecorator('DtDdWrapper')
                ->removeDecorator('Label')
                ->setAttrib('id', $name.'_box_add')
                ->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selects', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND))
                ->setRegisterInArrayValidator(false);
        
        $add_action = new Zend_Form_Element_Button( $i18n->translate('Add'));
        $add_action->removeDecorator("DtDdWrapper")
                   ->addDecorator('HtmlTag', array('tag' => 'li'))
                   ->setAttrib('id', $name.'_add_bt')
                   ->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selectActions', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));

        $remove_action = new Zend_Form_Element_Button( $i18n->translate('Remove'));
        $remove_action->removeDecorator("DtDdWrapper")
                      ->addDecorator('HtmlTag', array('tag' => 'li'))
                      ->setAttrib('id', $name.'_remove_bt')
                      ->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selectActions', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        
        $this->addElements( array( $header,
                                   $start_box,
                                   $add_action,
                                   $remove_action,
                                   $end_box));

    }

}

