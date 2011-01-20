<?php

class Snep_Form_Filter extends Zend_Form {

    protected $submit;
    protected $reset;

    public function __construct() {
        $config_file = "./default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);
        parent::__construct($config);

        $i18n = Zend_Registry::get("i18n");

        $this->setElementDecorators(
                array(
                    'ViewHelper',
                    'Label'
                )
        );

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class'=>'zend_form')),
            'Form'
        ));

        $submit = new Zend_Form_Element_Submit("submit", array("label" => $i18n->translate("Procurar")));
        $submit->removeDecorator('DtDdWrapper');
        $this->submit = $submit;

        // Botão Lista Completa
        $reset = new Zend_Form_Element_Button("buttom", array("label" => $i18n->translate("Lista Completa")));
        ;
        $reset->removeDecorator('DtDdWrapper');
        $this->reset = $reset;

        $this->addElement($submit);
        $this->addElement($reset);
    }

    public function setFieldOptions($options) {
        $campo = $this->getElement('campo');
        $campo->setMultiOptions($options);
    }

    public function setFieldValue($value) {
        $filtro = $this->getElement('filtro');
        $filtro->setValue($value);
    }

    public function setValue($value) {
        $filter_value = $this->getElement('campo');
        $filter_value->setValue($value);
    }

    public function setResetUrl($url) {
        $this->reset->setAttrib("onclick", "location.href='$url'");
    }

}
