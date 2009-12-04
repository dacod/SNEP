<?php
/**
 * Classe que auxilia o uso e configuração de Ações das Regras de Negócio do
 * SNEP.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Rule_ActionConfig {

    /**
     * Formulário gerado com os parametros.
     *
     * @var Zend_Form $form
     */
    private $form;

    /**
     * Elemento XML com os parametros
     *
     * @var SimpleXMLElement $xml
     */
    private $xml;

    /**
     * Construtor.
     *
     * Aqui é feito o parse do XML para formar um formulário(Zend_Form). E para
     * fazer o parse do retorno do formulário.
     *
     * @param string $xml XML com os parametros requeridos pela ação.
     */
    public function __construct($xml) {
        if($xml == "") {
            $i18n = Zend_Registry::get('Zend_Translate');
            $this->form = new Zend_Form();
            $this->form->addElement(new Zend_Form_Element_Submit($i18n->translate('Salvar')));
        }
        else {
            $this->xml = new SimpleXMLElement($xml);
            $this->parseForm();
        }
    }

    /**
     * Faz o parse do XML e gera o formulário.
     */
    private function parseForm() {
        $form = new Zend_Form();
        $i18n = Zend_Registry::get('Zend_Translate');
        // Para cada elemento do XML
        foreach( $this->xml as $element ) {
            switch( $element->getName() ) {
                case 'string':
                    $form->addElement( $this->parseString($element) );
                    break;
                case 'int':
                    $form->addElement( $this->parseInt($element) );
                    break;
                case 'ramal':
                    $form->addElement( $this->parseRamal($element) );
                    break;
                case 'tronco':
                    $form->addElement( $this->parseTronco($element) );
                    break;
                case 'radio':
                    $form->addElement( $this->parseRadio($element) );
                    break;
                case 'ccustos':
                    $form->addElement( $this->parseCCustos($element) );
                    break;
                default:
                    $form->addElement( $this->parseString($element) );
            }
        }
        $form->addElement(new Zend_Form_Element_Submit($i18n->translate('Salvar')));
        $this->form = $form;
        return $this->form;
    }

    /**
     * Retorna um formulário para os parametros da ação.
     *
     * @return Zend_Form com as configurações
     */
    public function getForm() {
        return $this->form;
    }

    /**
     * Faz o parse de um array de configuração baseado em um array cru vindo do
     * $_POST
     *
     * @param array $post informações do post para serem processadas
     */
    public function parseConfig($post) {
        $config = array();
        
        foreach ($this->xml as $element) {
            $config["{$element->id}"] = $post["{$element->id}"];
        }

        return $config;
    }

    /**
     * Faz o parse de um campo <ramal>
     * @param SimpleXMLElement $element
     */
    private function parseRamal($element) {
        $i18n = Zend_Registry::get('Zend_Translate');
        $element->addChild('label', $i18n->translate("Ramal"));
        $element->addChild('size', '4');
        return $this->parseString($element);
    }

    /**
     * Faz o parse de um campo <tronco>
     * @param SimpleXMLElement $element
     */
    private function parseTronco($element) {
        $i18n = Zend_Registry::get('Zend_Translate');

        $form_element = new Zend_Form_Element_Select((string)$element->id);
        $form_element->setLabel( (string)$i18n->translate("Tronco") );

        foreach(PBX_Trunks::getAll() as $tronco) {
            $form_element->addMultiOption($tronco->getId(), $tronco->getName());
            if(isset($element->value) && $tronco->getId() == $element->value)
                $form_element->setValue($element->value);
        }

        return $form_element;
    }

    /**
     * Faz o parse de um campo <ccustos>
     * @param SimpleXMLElement $element
     */
    private function parseCCustos($element) {
        $i18n = Zend_Registry::get('Zend_Translate');

        $form_element = new Zend_Form_Element_Select((string)$element->id);
        $form_element->setLabel( (string)$i18n->translate("Centro de Custos") );

        foreach(Snep_CentroCustos::getAll() as $ccustos) {
            $form_element->addMultiOption($ccustos['codigo'], $ccustos['codigo'] . " - " . $ccustos['nome']);
            if(isset($element->value) && $ccustos['codigo'] == $element->value)
                $form_element->setValue($element->value);
        }

        return $form_element;
    }

    /**
     * Faz o parse de um elemento <string> para Zend_Form.
     * @param SimpleXMLElement $element
     */
    private function parseString($element) {
        $form_element = new Zend_Form_Element_Text( (string)$element->id );
        $form_element->setLabel( (string)$element->label );
        $form_element->setAttrib('size', $element->size);
        if(isset($element->value)) {
            $form_element->setValue($element->value);
        }
        else if(isset($element->default)) {
            $form_element->setValue($element->default);
        }

        return $form_element;
    }

    /**
     * Faz o parse de um elemento <int> para Zend_Form.
     * @param SimpleXMLElement $element
     */
    private function parseInt($element) {
        $form_element = new Zend_Form_Element_Text( (string)$element->id );
        $form_element->setLabel( (string)$element->label );
        $form_element->setAttrib('size', $element->size);
        if(isset($element->value)) {
            $form_element->setValue($element->value);
        }
        else if(isset($element->default)) {
            $form_element->setValue($element->default);
        }

        return $form_element;
    }

    /**
     * Faz o parse de um elemento <radio> para Zend_Form.
     * @param SimpleXMLElement $element
     */
    private function parseRadio($element) {
        $form_element = new Zend_Form_Element_Radio((string)$element->id);
        foreach( $element->option as $option ) {
            $form_element->addMultiOption((string)$option->value, $option->label);
            if(isset($element->value) && $option->value == $element->value)
                $form_element->setValue($element->value);
        }

        return $form_element;
    }
}
