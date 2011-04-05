<?php

class Snep_Form_Element_SectionTitle extends Zend_Form_Element {

    /**
     * Use formSelect view helper by default
     * @var string
     */
    public $helper = 'htmlElement';

    public function __construct($spec, $options = null) {
        parent::__construct($spec, $options);
        $this->setDecorators(array());
    }

    public function render() {
        return "<tr class=\"snep_form_section_title\"><td colspan='2'><h2>" . $this->_label . "</h2></tr></td>";
    }
}
