<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
        if(Zend_Auth::getInstance()->hasIdentity() && $this->getRequest()->getActionName() == "installed") {
            $this->_redirect("index");
        }
    }

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Instalador do SNEP");
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

        $config = Zend_Registry::get('config');
        $path = $config->system->path;

        $schema = file_get_contents($path->base . "/default/installer/schema.sql");
        $system_data = file_get_contents($path->base . "/default/installer/system_data.sql");
        $cnl_data = file_get_contents($path->base . "/default/installer/cnl_data.sql");

        $db->beginTransaction();
        try {

            $db->query($schema);
            $db->query($system_data);
            $db->query($cnl_data);

            $db->commit();
        }
        catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }

    }

    public function installedAction() {
        $this->view->breadcrumb = $this->view->translate("Instalação Concluida");
        $this->view->hideMenu = true;

        $db = Zend_Registry::get('db');

        $select = $db->select()
           ->from('peers', array('name', 'password'))
           ->where("name = 'admin'");

        $stmt = $db->query($select);
        $secret = $stmt->fetch();
        $this->view->secret = $secret;

        $this->getRequest()->setActionName("installed");

    }

    public function configureAction() {
        $this->view->hideMenu = true;
        $this->view->breadcrumb = $this->view->translate("Instalador » Configuração");
        $form_config = new Zend_Config_Xml("./default/forms/installer.xml");

        $original_config = Zend_Registry::get('config');

        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . '/installer/configure');

        $asterisk_form = new Snep_Form_SubForm($this->view->translate("Configuração do Asterisk"), $form_config->asterisk);
        $database_form = new Snep_Form_SubForm($this->view->translate("Configuração do Banco de Dados"), $form_config->database);
        $snep_form = new Snep_Form_SubForm($this->view->translate("Senha do Administrador"), $form_config->snep);

        $asterisk_user = $asterisk_form->getElement('username');
        $asterisk_user->setValue( $original_config->ambiente->user_sock );
        $asterisk_secret = $asterisk_form->getElement('secret');
        $asterisk_secret->setValue( $original_config->ambiente->pass_sock );
        $asterisk_server = $asterisk_form->getElement('server');
        $asterisk_server->setValue( $original_config->ambiente->ip_sock );

        $db_user = $database_form->getElement('username');
        $db_user->setValue( $original_config->ambiente->db->username );
        $db_secret = $database_form->getElement('password');
        $db_secret->setValue( $original_config->ambiente->db->password );
        $db_server = $database_form->getElement('hostname');
        $db_server->setValue( $original_config->ambiente->db->host );
        $db_name = $database_form->getElement('dbname');
        $db_name->setValue( $original_config->ambiente->db->dbname );

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

                // Setando usuário do admin.
                $db->update("peers", array('password' => $snep_data['password']), "id = 1");

                // Gravando alterações no arquivo de configuração.
                $config_file = "./includes/setup.conf";
                $config = new Zend_Config_Ini($config_file, null, true);

                $config->ambiente->ip_sock     = $_POST['asterisk']['server'];
                $config->ambiente->user_sock   = $_POST['asterisk']['username'];
                $config->ambiente->pass_sock   = $_POST['asterisk']['secret'];

                $config->ambiente->db->host              = $_POST['database']['hostname'];
                $config->ambiente->db->username          = $_POST['database']['username'];
                $config->ambiente->db->password          = $_POST['database']['password'];
                $config->ambiente->db->dbname            = $_POST['database']['dbname'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                                           'filename' => $config_file));
                // Grava arquivo.
                $writer->write();
                
                $this->_redirect("installer/installed");

            }
        }

        $this->view->form = $form;
    }

}
