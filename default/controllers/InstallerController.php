<?php
require_once 'Zend/Controller/Action.php';
require_once 'Snep/Inspector.php';

class InstallerController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador");
        $this->view->next = $this->view->url(array("controller"=>"installer", "action"=>"diagnostic"), null, true);
    }

    public function diagnosticAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador » Diagnóstico");
        $this->view->next = $this->view->url(array("controller"=>"installer", "action"=>"configure"), null, true);

        $inspector = new Snep_Inspector();
        $this->view->errored = $inspector->errored();
        $this->view->testResult = $inspector->getInspects();
    }

    public function configureAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador » Configuração");
        $form_config = new Zend_Config_Xml("./default/forms/installer.xml");

        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . '/installer/configure');

        $asterisk_form = new Snep_Form_SubForm($this->view->translate("Configuração do Asterisk"), $form_config->asterisk);
        $database_form = new Snep_Form_SubForm($this->view->translate("Configuração do Banco de Dados"), $form_config->database);
        $snep_form = new Snep_Form_SubForm($this->view->translate("Senha do Administrador"), $form_config->snep);

        $form->addSubForm($database_form, "database");
        $form->addSubForm($asterisk_form, "asterisk");
        $form->addSubForm($snep_form, "snep");

        $form->addElement(new Zend_Form_Element_Submit("submit", array("label" => "Enviar")));

        if($this->getRequest()->isPost()) {
            if($form->isValid($_POST)) {
                echo "só manda jogadô";
            }
        }

        $this->view->form = $form;
    }

}
