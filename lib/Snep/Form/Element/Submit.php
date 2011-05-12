<?php

class Snep_Form_Element_Submit extends Zend_Form_Element {
    public function __construct($spec, $options = null) {
        parent::__construct($spec, $options);
    }

    /**
     * Default decorators
     *
     * Uses only 'Submit' and 'TrTdWrapper' decorators by default.
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->setDecorators(array(
                array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
                array(array('emptyTd' => 'HtmlTag'), array('tag' => 'td', 'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
                array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'snep_form_submit'))
            ));
        }
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $i18n = Zend_Registry::get('i18n');

        $disabled = $this->getAttrib("disabled") === NULL ? "" : sprintf('disabled="%s"', $this->getAttrib("disabled"));

        $content = sprintf('<input type="submit" value="%s" %s />', $this->_label, $disabled);
        if(Zend_Registry::isRegistered("cancel_url")) {
            $url = Zend_Registry::get("cancel_url");
        }
        else {
            $url = "javascript:history.back();";
        }
        $content .= sprintf(' <a class="snep_form_cancel" href="%s">%s</a>', $url, $i18n->translate("Cancel"));
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
    }
}
