<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */

require_once 'Zend/Controller/Action.php';
require_once 'Snep/Inspector.php';

/**
 * Snep Installer Interface
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli <henrique@opens.com.br>
 */
class InstallerController extends Zend_Controller_Action {

    public function  preDispatch() {
        parent::preDispatch();
        $this->view->hideMenu = true;
        // Fazer checagem futura se o sistema está instalado ou não.
        if(Zend_Auth::getInstance()->hasIdentity() && $this->getRequest()->getActionName() != "installed") {
            $this->_redirect("index");
        }
    }

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

    protected function install(Zend_Db_Adapter_Abstract $db) {
        $path = Zend_Registry::get('config')->system->path;
        $schema = file_get_contents($path->base . "/default/installer/schema.sql");
        $system_data = file_get_contents($path->base . "/default/installer/system_data.sql");

        $db->beginTransaction();
        try {
            // Schema creation
            $db->query($schema);
            // System data insertions
            $db->query($system_data);

            Zend_Debug::dump(Zend_Registry::get("config"));
            throw new Exception("NAO");

            $db->commit();
        }
        catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }

    }

    public function installedAction() {
        $this->view->breadcrumb = $this->view->translate("Instalação Concluida");
    }

    public function configureAction() {
        $this->view->hideMenu = true;
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

            if($form_isValid) {
                try {
                    $this->install($db);
                }
                catch(Exception $ex) {
                    $this->view->message = $ex->getMessage();
                    $this->renderScript("installer/error.phtml");
                }
                
            }
        }

        $this->view->form = $form;
    }

}
