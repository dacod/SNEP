<?php

/*
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * System settings controller.
 */
class ParametersController extends Zend_Controller_Action {

    public function indexAction() {
        // Title
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Configure"),
            $this->view->translate("Parameters")
        ));

        // Get configuration properties from Zend_Registry
        $config = Zend_Registry::get('config');

        // Include Inpector class, for permission test
        include_once( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        // Verify if there's any error, and if it's related to the setup.conf file
        if ($response['error'] && strpos($response['message'], "setup.conf") > 0) {
            // seta variavel verificada no template
            $this->view->error = $this->view->translate("The File includes/setup.conf does not have permission to be modified.");
        }
        // Create object Snep_Form
        $form = new Snep_Form();

        // Set form action
        $form->setAction($this->getFrontController()->getBaseUrl() . '/parameters/index');

        $form_xml = new Zend_Config_Xml('./modules/default/formssetup.conf.xml');

        // Section General
        $general = new Snep_Form_SubForm($this->view->translate("General Configuration"), $form_xml->general);

        // Setting propoertie values
        $empName = $general->getElement('emp_nome');
        $empName->setValue($config->ambiente->emp_nome);

        $debug = $general->getElement('debug');
        $debug->setValue($config->system->debug);

        $ipSock = $general->getElement('ip_sock');
        $ipSock->setValue($config->ambiente->ip_sock);

        $userSock = $general->getElement('user_sock');
        $userSock->setValue($config->ambiente->user_sock);

        $passSock = $general->getElement('pass_sock');
        $passSock->setValue($config->ambiente->pass_sock);

        $email = $general->getElement('mail');
        $email->setValue($config->system->mail);

        $lineLimit = $general->getElement('linelimit');
        $lineLimit->setValue($config->ambiente->linelimit);

        $dstExceptions = $general->getElement('dst_exceptions');
        $dstExceptions->setValue($config->ambiente->dst_exceptions);

        $conferenceApp = $general->getElement('conference_app');
        $conferenceApp->setValue($config->ambiente->conference_app);


        $form->addSubForm($general, "general");

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


        $form->addSubForm($locale_form, "locale");

        // Section Recording
        $recording = new Snep_Form_SubForm($this->view->translate("Call Recording Configuration"), $form_xml->recording);

        // Setting propoertie values
        $application = $recording->getElement('application');
        $application->setValue($config->general->record->application);

        $flag = $recording->getElement('flag');
        $flag->setValue($config->general->record->flag);

        $recordMp3 = $recording->getElement('record_mp3');
        $recordMp3->setValue($config->general->record_mp3);

        $pathVoice = $recording->getElement('path_voz');
        $pathVoice->setValue($config->ambiente->path_voz);

        $pathVoiceBkp = $recording->getElement('path_voz_bkp');
        $pathVoiceBkp->setValue($config->ambiente->path_voz_bkp);
        $form->addSubForm($recording, "gravacao");

        // Section Trunks
        $trunks = new Snep_Form_SubForm($this->view->translate("Trunks Configuration"), $form_xml->trunks);

        // Setting propoertie values
        $qualControlValue = $trunks->getElement('valor_controle_qualidade');
        $qualControlValue->setValue($config->ambiente->valor_controle_qualidade);
        $form->addSubForm($trunks, "troncos");

        // Verify if the request is a post
        if ($this->_request->getPost()) {

            $formIsValid = $form->isValid($_POST);
            $formData = $this->getRequest()->getParams();

            // Specific verification for propertie path_voice
            if (!file_exists($formData['gravacao']['path_voz'])) {
                $recording->getElement('path_voz')->addError($this->view->translate("Invalid path"));
                $formIsValid = false;
            }

            //Validates form, then sets propertie values and records it on the configuration file
            if ($formIsValid) {

                $configFile = APPLICATION_PATH . "/includes/setup.conf";
                $config = new Zend_Config_Ini($configFile, null, true);

                $config->ambiente->emp_nome = $formData['general']['emp_nome'];
                $config->system->debug = $formData['general']['debug'];


                $config->system->language = $formData['locale']['language'];
                $config->system->locale = $formData['locale']['locale'];
                $config->system->timezone = $formData['locale']['timezone'];

                $config->ambiente->ip_sock = $formData['general']['ip_sock'];
                $config->ambiente->user_sock = $formData['general']['user_sock'];
                $config->ambiente->pass_sock = $formData['general']['pass_sock'];
                $config->system->mail = $formData['general']['mail'];
                $config->ambiente->linelimit = $formData['general']['linelimit'];
                $config->ambiente->dst_exceptions = $formData['general']['dst_exceptions'];
                $config->ambiente->conference_app = $formData['general']['conference_app'];

                $config->general->record->application = $formData['gravacao']['application'];
                $config->general->record->flag = $formData['gravacao']['flag'];
                $config->general->record_mp3 = $formData['gravacao']['record_mp3'];

                $config->ambiente->path_voz = $formData['gravacao']['path_voz'];
                $config->ambiente->path_voz_bkp = $formData['gravacao']['path_voz_bkp'];

                $config->ambiente->valor_controle_qualidade = $formData['troncos']['valor_controle_qualidade'];

                $writer = new Zend_Config_Writer_Ini(array('config' => $config,
                            'filename' => $configFile));
                // Write file
                $writer->write();

                $this->_redirect('parameters');
            }
        }

        $this->view->form = $form;
    }

}
