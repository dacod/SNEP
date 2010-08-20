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
            $form_isValid = $form->isValid($_POST);

            $snep_data = $form->getValue("snep");
            if($snep_data['password'] !== $snep_data['confirmpassword']) {
                $snep_form->getElement('confirmpassword')->addError($this->view->translate("A confirmação de senha não é igual a senha informada"));
                $form_isValid = false;
            }

            if(!$asterisk_form->isErrors()) {
                $asterisk_data = $form->getValue("asterisk");
                $asterisk = new Asterisk_AMI(null, $asterisk_data);

                try {
                    $asterisk->connect();
                }
                catch(Asterisk_Exception_Auth $ex) {
                    $asterisk_form->getElement('secret')->addError($this->view->translate("Usuário ou senha recusada pelo servidor Asterisk"));
                    $form_isValid = false;
                }
                catch(Asterisk_Exception_CantConnect $ex) {
                    $asterisk_form->getElement('server')->addError($this->view->translate("Falha ao conectar: %s", $ex->getMessage()));
                    $form_isValid = false;
                }
            }

            if(!$database_form->isErrors()) {
                $database_data = $form->getValue('database');
                $db = Zend_Db::factory('Pdo_Mysql', $database_data);
                try {
                    $db->getConnection();
                }
                catch(Zend_Db_Exception $ex) {
                    $database_form->getElement('hostname')->addError($this->view->translate("Falha ao conectar: %s", $ex->getMessage()));
                    $form_isValid = false;
                }
            }


            // Fazer a verificação de banco de dados

            if($form_isValid) {
                // Escrever as configurações na tela ou arquivo.
            }
        }

        $this->view->form = $form;
    }

}
