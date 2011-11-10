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

        $config = Zend_Registry::get('config');
        if( trim ( $config->ambiente->db->host ) != "" ) {
            $this->_redirect("auth/login/");
        }

        parent::preDispatch();
        $this->view->hideMenu = true;
        if(Zend_Auth::getInstance()->hasIdentity() && $this->getRequest()->getActionName() == "installed") {
            $this->_redirect("index");
        }
    }

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("SNEP Installer");
        //$this->view->next = $this->view->url(array("controller"=>"installer", "action"=>"diagnostic"), null, true);

        $objInspector = new Snep_Inspector('Permissions');
        $inspect = $objInspector->getInspects();

        $this->view->error = $inspect['Permissions'];

        $form = new Snep_Form();
        $form_xml = new Zend_Config_Xml('./default/forms/setup.conf.xml');

        $locale_form = new Snep_Form_SubForm($this->view->translate("Locale Configuration"), $form_xml->locale);
        $locale = Snep_Locale::getInstance()->getZendLocale();

        $locales = array();
        foreach ($locale->getTranslationList("territory", Snep_Locale::getInstance()->getLanguage(), 2) as $ccode => $country) {
            $locales[$country] = $locale->getLocaleToTerritory($ccode);
        }
        ksort($locales, SORT_LOCALE_STRING);
        foreach ($locales as $country => $ccode) {
            $locale_form->getElement("locale")->addMultiOption($ccode, $country);
        }
        $locale_form->getElement("locale")->setValue(Snep_Locale::getInstance()->getLocale());

        foreach ($locale->getTranslationList("territorytotimezone", Snep_Locale::getInstance()->getLanguage()) as $timezone => $territory) {
            $locale_form->getElement("timezone")->addMultiOption($timezone, $timezone);
        }
        $locale_form->getElement("timezone")->setValue(Snep_Locale::getInstance()->getTimezone());

        $languages = array();
        $languageElement = $locale_form->getElement("language");
        $available_languages = Snep_Locale::getInstance()->getAvailableLanguages();
        foreach ($locale->getTranslationList("language", Snep_Locale::getInstance()->getLanguage()) as $lcode => $language) {
            if (in_array($lcode, $available_languages)) {
                $languageElement->addMultiOption($lcode, ucfirst($language));
            }
        }
        $languageElement->setValue(Snep_Locale::getInstance()->getLanguage());        
        

        if($this->getRequest()->isPost()) {
            
            $form_isValid = $form->isValid($_POST);

            $configFile = APPLICATION_PATH . "/includes/setup.conf";
            $config = new Zend_Config_Ini( $configFile, null, true );

            $config->system->locale = $_POST['locale']['locale'];
            $config->system->timezone = $_POST['locale']['timezone'];
            $config->system->language = $_POST['locale']['language'];

            if($form_isValid) {
                $writer = new Zend_Config_Writer_Ini(array('config' => $config,
                                                       'filename' => $configFile));
                $writer->write();
                $this->_redirect('installer/diagnostic/');
            }
        }

        $submit_button = $form->getElement('submit');
        $submit_button->setLabel('Iniciar a Instalar');
        
        if( $this->view->error['error'] ) {
            $submit_button->setAttrib('disabled', true);
        }

        $this->view->form = $form->addSubForm($locale_form, "locale");

    }

    public function diagnosticAction() {
        $this->view->breadcrumb = $this->view->translate("Installer » Diagnostic");
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
        $this->view->breadcrumb = $this->view->translate("Installation finished");
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

        $objInspector = new Snep_Inspector('Permissions');
        $inspect = $objInspector->getInspects();
        $this->view->error = $inspect['Permissions'];

        $this->view->hideMenu = true;
        $this->view->breadcrumb = $this->view->translate("Installer » Configuration");
        $form_config = new Zend_Config_Xml("./default/forms/installer.xml");

        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . '/installer/configure');

        $asterisk_form = new Snep_Form_SubForm($this->view->translate("Asterisk Configuration"), $form_config->asterisk);
        $database_form = new Snep_Form_SubForm($this->view->translate("Database Configuration"), $form_config->database);
        $snep_form = new Snep_Form_SubForm($this->view->translate("Admin Password"), $form_config->snep);

        $form->addSubForm($database_form, "database");
        $form->addSubForm($asterisk_form, "asterisk");
        $form->addSubForm($snep_form, "snep");

        $submit_button = $form->getElement('submit');
        if( $this->view->error['error'] ) {
            $submit_button->setAttrib('disabled', true);
        }


        if($this->getRequest()->isPost()) {
            $form_isValid = $form->isValid($_POST);

            $snep_data = $form->getValue("snep");
            if($snep_data['password'] !== $snep_data['confirmpassword']) {
                $snep_form->getElement('confirmpassword')->addError($this->view->translate("The password confirmation is different from the original"));
                $form_isValid = false;
            }

            if(!$asterisk_form->isErrors()) {
                $asterisk_data = $form->getValue("asterisk");
                $asterisk = new Asterisk_AMI(null, $asterisk_data);

                try {
                    $asterisk->connect();
                }
                catch(Asterisk_Exception_Auth $ex) {
                    $asterisk_form->getElement('secret')->addError($this->view->translate("User and/or password rejected by Asterisk"));
                    $form_isValid = false;
                }
                catch(Asterisk_Exception_CantConnect $ex) {
                    $asterisk_form->getElement('server')->addError($this->view->translate("Unable to connect: %s", $ex->getMessage()));
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
                    $database_form->getElement('hostname')->addError($this->view->translate("Unable to connect: %s", $ex->getMessage()));
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
