<?php
/**
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



class ParametersController extends Zend_Controller_Action {

    public function indexAction() {
        // Title
        $this->view->breadcrumb = $this->view->translate("Configurações » Parâmetros");
        // Get configuration properties from Zend_Registry
        $config = Zend_Registry::get('config');

        // Include Inpector class, for permission test
        include_once( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        // Verify if there's any error, and if it's related to the setup.conf file
        if( $response['error'] && strpos($response['message'], "setup.conf") > 0 ) {
            // seta variavel verificada no template
            $this->view->error = $this->view->translate("O arquivo includes/setup.conf não possui permissão de escrita.");
        }
        // Create object Snep_Form
        $form = new Snep_Form();

        // Set form action
        $form->setAction($this->getFrontController()->getBaseUrl() . '/parameters/index');

        // Section General
        $modelGeneral = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'general', true);
        $general = new Snep_Form_SubForm($this->view->translate("Configurações Gerais"), $modelGeneral );
        $general->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações Gerais")));

        // Setting propoertie values
        $empName = $general->getElement('emp_nome');
        $empName->setValue( $config->ambiente->emp_nome );

        $debug = $general->getElement('debug');
        $debug->setValue( $config->system->debug );

        $ipSock = $general->getElement('ip_sock');
        $ipSock->setValue( $config->ambiente->ip_sock);

        $userSock = $general->getElement('user_sock');
        $userSock->setValue( $config->ambiente->user_sock );

        $passSock = $general->getElement('pass_sock');
        $passSock->setValue( $config->ambiente->pass_sock );

        $email = $general->getElement('mail');
        $email->setValue( $config->system->mail );

        $lineLimit = $general->getElement('linelimit');
        $lineLimit->setValue( $config->ambiente->linelimit);

        $dstExceptions = $general->getElement('dst_exceptions');
        $dstExceptions->setValue( $config->ambiente->dst_exceptions );

        $conferenceApp = $general->getElement('conference_app');
        $conferenceApp->setValue( $config->ambiente->conference_app);


        $form->addSubForm($general, "general");
        
        // Section Recording
        $modeRecording = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'gravacao', true);
        $recording = new Snep_Form_SubForm($this->view->translate("Configurações de Gravação"), $modeRecording );
        $recording->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Gravação")));

         // Setting propoertie values
        $application = $recording->getElement('application');
        $application->setValue( $config->general->record->application );

        $flag = $recording->getElement('flag');
        $flag->setValue( $config->general->record->flag );

        $recordMp3 = $recording->getElement('record_mp3');
        $recordMp3->setValue( $config->general->record_mp3 );

        $pathVoice = $recording->getElement('path_voz');
        $pathVoice->setValue( $config->ambiente->path_voz );

        $pathVoiceBkp = $recording->getElement('path_voz_bkp');
        $pathVoiceBkp->setValue( $config->ambiente->path_voz_bkp );
        $form->addSubForm($recording, "gravacao");

        // Section Extensions
        $modelPeers = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'ramais', true);
        $peers = new Snep_Form_SubForm($this->view->translate("Configurações de Ramais e Agentes"), $modelPeers );
        $peers->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Ramais e Agentes")));

        // Setting propoertie values
        $peersRange = $peers->getElement('peers_range');
        $peersRange->setValue( $config->canais->peers_range );

        $agents = $peers->getElement('agents');
        $agents->setValue( $config->ambiente->agents );

        $form->addSubForm($peers, "ramais");

        // Section Trunks
        $modelTrunks = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'troncos', true);
        $trunks = new Snep_Form_SubForm($this->view->translate("Configurações de Troncos"), $modelTrunks );
        $trunks->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Troncos")));

        // Setting propoertie values
        $qualControlValue = $trunks->getElement('valor_controle_qualidade');
        $qualControlValue->setValue( $config->ambiente->valor_controle_qualidade );
        $form->addSubForm($trunks, "troncos");

        // Section Panels
        $modelPanel = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'painel', true);
        $panel = new Snep_Form_SubForm($this->view->translate("Configurações de Painel"), $modelPanel );
        $panel->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Painel")));

        // Setting propoertie values
        $panel1 = $panel->getElement('menu_status_1');
        $panel1->setValue( $config->ambiente->menu_status_1 );

        $panel2 = $panel->getElement('menu_status_2');
        $panel2->setValue( $config->ambiente->menu_status_2 );

        $panel3 = $panel->getElement('menu_status_3');
        $panel3->setValue( $config->ambiente->menu_status_3 );
        $form->addSubForm($panel, "painel");

        $form->addElement(new Zend_Form_Element_Submit("submit", array("label" => "Salvar")));

        // Verify if the request is a post
        if($this->_request->getPost()) {

            $formIsValid = $form->isValid($_POST);
            $formData = $this->_request->getParams();

            // Specific verification for propertie path_voice
            if(! file_exists( $formData['gravacao']['path_voz'] ) ) {
                $recording->getElement('path_voz')->addError($this->view->translate("Caminho inválido ou inexistente."));
                $formIsValid = false;
            }

            //Validates form, then sets propertie values and records it on the configuration file
            if($formIsValid) {

                $configFile = "./includes/setup.conf";
                $config = new Zend_Config_Ini($configFile, null, true);

                $config->ambiente->emp_nome                 = $formData['general']['emp_nome'];
                $config->system->debug                      = $formData['general']['debug'];

                $config->ambiente->ip_sock                  = $formData['general']['ip_sock'];
                $config->ambiente->user_sock                = $formData['general']['user_sock'];
                $config->ambiente->pass_sock                = $formData['general']['pass_sock'];
                $config->system->mail                       = $formData['general']['mail'];
                $config->ambiente->linelimit                = $formData['general']['linelimit'];
                $config->ambiente->dst_exceptions           = $formData['general']['dst_exceptions'];
                $config->ambiente->conference_app           = $formData['general']['conference_app'];

                $config->general->record->application       = $formData['gravacao']['application'];
                $config->general->record->flag              = $formData['gravacao']['flag'];
                $config->general->record_mp3                = $formData['gravacao']['record_mp3'];

                $config->ambiente->path_voz                 = $formData['gravacao']['path_voz'];
                $config->ambiente->path_voz_bkp             = $formData['gravacao']['path_voz_bkp'];

                $config->canais->peers_range                = $formData['ramais']['peers_range'];
                $config->ambiente->agents                   = $formData['ramais']['agents'];

                $config->ambiente->valor_controle_qualidade = $formData['troncos']['valor_controle_qualidade'];

                $config->ambiente->menu_status_1            = $formData['painel']['menu_status_1'];
                $config->ambiente->menu_status_2            = $formData['painel']['menu_status_2'];
                $config->ambiente->menu_status_3            = $formData['painel']['menu_status_3'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                                           'filename' => $configFile));
                // Write file
                $writer->write();

                $this->_forward('index', 'parameters');

            }

        }

        $this->view->form = $form;
        
    }

}
