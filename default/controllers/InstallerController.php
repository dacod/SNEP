<?php

require_once 'Zend/Controller/Action.php';

class InstallerController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador");
        $this->view->next = $this->view->url(array("controller"=>"installer", "action"=>"diagnostic"), null, true);
    }

    public function diagnosticAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador » Diagnóstico");
        $this->view->next = $this->view->url(array("controller"=>"installer", "action"=>"configure"), null, true);
    }

    public function configureAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador » Configuração");
        $form_config = new Zend_Config_Xml("./default/forms/installer.xml");

        $form = new Zend_Form();
        $asterisk_form = new Zend_Form($form_config->asterisk);
        $database_form = new Zend_Form($form_config->database);

        $asterisk_form->setIsArray(true);
        $asterisk_form->setElementsBelongTo("asterisk");
        $asterisk_form->removeDecorator('form');
        $asterisk_form->addDecorator("fieldset", array("legend" => $this->view->translate("Configuração do Asterisk")));
        
        $database_form->setIsArray(true);
        $database_form->setElementsBelongTo("database");
        $database_form->removeDecorator('form');
        $database_form->addDecorator("fieldset", array("legend" => $this->view->translate("Configuração do Banco de Dados")));

        $form->addSubForm($database_form, "database");
        $form->addSubForm($asterisk_form, "asterisk");
        
        $form->addElement(new Zend_Form_Element_Submit("submit"));

        $this->view->form = $form;
    }

}
