<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
        // Título
        $this->view->breadcrumb = $this->view->translate("Configurações » Parâmetros");
        // Carrega arquivo config do Zend_Registry
        $config = Zend_Registry::get('config');

        // Include da classe Inpector de verificação de permissão.
        include( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        // Verifica se há erros e se são relacionados com o setup.conf
        if( $response['error'] && strpos($response['message'], "setup.conf") > 0 ) {
            // seta variavel verificada no template
            $this->view->error = $this->view->translate("O arquivo includes/setup.conf não possui permissão de escrita.");
        }
        // Cria objeto Snep_Form
        $form = new Snep_Form();

        // Seta o action do formulário
        $form->setAction($this->getFrontController()->getBaseUrl() . '/parameters/index');

        // Sessao General
        $model_general = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'general', true);
        $general = new Snep_Form_SubForm($this->view->translate("Configurações Gerais"), $model_general );
        $general->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações Gerais")));

        // Setando valores dos arquivo
        $emp_nome = $general->getElement('emp_nome');
        $emp_nome->setValue( $config->ambiente->emp_nome );

        $debug = $general->getElement('debug');
        $debug->setValue( $config->system->debug );

        $ip_sock = $general->getElement('ip_sock');
        $ip_sock->setValue( $config->ambiente->ip_sock);

        $user_sock = $general->getElement('user_sock');
        $user_sock->setValue( $config->ambiente->user_sock );

        $pass_sock = $general->getElement('pass_sock');
        $pass_sock->setValue( $config->ambiente->pass_sock );

        $email = $general->getElement('mail');
        $email->setValue( $config->system->mail );

        $linelimit = $general->getElement('linelimit');
        $linelimit->setValue( $config->ambiente->linelimit);

        $dst_exceptions = $general->getElement('dst_exceptions');
        $dst_exceptions->setValue( $config->ambiente->dst_exceptions );

        $conference_app = $general->getElement('conference_app');
        $conference_app->setValue( $config->ambiente->conference_app);


        $form->addSubForm($general, "general");
        
        // Sessão Gravação
        $model_gravacao = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'gravacao', true);
        $gravacao = new Snep_Form_SubForm($this->view->translate("Configurações de Gravação"), $model_gravacao );
        $gravacao->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Gravação")));

        // Setando valores do arquivo.
        $application = $gravacao->getElement('application');
        $application->setValue( $config->general->record->application );

        $flag = $gravacao->getElement('flag');
        $flag->setValue( $config->general->record->flag );

        $record_mp3 = $gravacao->getElement('record_mp3');
        $record_mp3->setValue( $config->general->record_mp3 );

        $path_voz = $gravacao->getElement('path_voz');
        $path_voz->setValue( $config->ambiente->path_voz );

        $path_voz_bkp = $gravacao->getElement('path_voz_bkp');
        $path_voz_bkp->setValue( $config->ambiente->path_voz_bkp );
        $form->addSubForm($gravacao, "gravacao");

        // Sessão Ramais
        $model_ramais = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'ramais', true);
        $ramais = new Snep_Form_SubForm($this->view->translate("Configurações de Ramais e Agentes"), $model_ramais );
        $ramais->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Ramais e Agentes")));

        // Setando valores do arquivo.
        $peers_range = $ramais->getElement('peers_range');
        $peers_range->setValue( $config->canais->peers_range );

        $agents = $ramais->getElement('agents');
        $agents->setValue( $config->ambiente->agents );

        $form->addSubForm($ramais, "ramais");

        // Sessão Troncos
        $model_troncos = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'troncos', true);
        $troncos = new Snep_Form_SubForm($this->view->translate("Configurações de Troncos"), $model_troncos );
        $troncos->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Troncos")));

        // Setando valores do arquivo.
        $valor_controle_qualidade = $troncos->getElement('valor_controle_qualidade');
        $valor_controle_qualidade->setValue( $config->ambiente->valor_controle_qualidade );
        $form->addSubForm($troncos, "troncos");

        // Sessão Paineis
        $model_painel = new Zend_Config_Xml('./default/forms/setup.conf.xml', 'painel', true);
        $painel = new Snep_Form_SubForm($this->view->translate("Configurações de Painel"), $model_painel );
        $painel->addDecorator("fieldset", array("legend" => $this->view->translate("Configurações de Painel")));

        // Setando valores do arquivo.
        $painel1 = $painel->getElement('menu_status_1');
        $painel1->setValue( $config->ambiente->menu_status_1 );

        $painel2 = $painel->getElement('menu_status_2');
        $painel2->setValue( $config->ambiente->menu_status_2 );

        $painel3 = $painel->getElement('menu_status_3');
        $painel3->setValue( $config->ambiente->menu_status_3 );
        $form->addSubForm($painel, "painel");

        $form->addElement(new Zend_Form_Element_Submit("submit", array("label" => "Salvar")));

        // Verifica se o formulario foi submetido
        if($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            // Verificação especifica do path_voz.
            if(! file_exists( $dados['gravacao']['path_voz'] ) ) {
                $gravacao->getElement('path_voz')->addError($this->view->translate("Caminho inválido ou inexistente."));
                $form_isValid = false;
            }

            // Se o formulario validar, seta valores e grava arquivo.
            if($form_isValid) {

                $config_file = "./includes/setup.conf";
                $config = new Zend_Config_Ini($config_file, null, true);

                $config->ambiente->emp_nome                 = $dados['general']['emp_nome'];
                $config->system->debug                      = $dados['general']['debug'];

                $config->ambiente->ip_sock                  = $dados['general']['ip_sock'];
                $config->ambiente->user_sock                = $dados['general']['user_sock'];
                $config->ambiente->pass_sock                = $dados['general']['pass_sock'];
                $config->system->mail                       = $dados['general']['mail'];
                $config->ambiente->linelimit                = $dados['general']['linelimit'];
                $config->ambiente->dst_exceptions           = $dados['general']['dst_exceptions'];
                $config->ambiente->conference_app           = $dados['general']['conference_app'];

                $config->general->record->application       = $dados['gravacao']['application'];
                $config->general->record->flag              = $dados['gravacao']['flag'];
                $config->general->record_mp3                = $dados['gravacao']['record_mp3'];

                $config->ambiente->path_voz                 = $dados['gravacao']['path_voz'];
                $config->ambiente->path_voz_bkp             = $dados['gravacao']['path_voz_bkp'];

                $config->canais->peers_range                = $dados['ramais']['peers_range'];
                $config->ambiente->agents                   = $dados['ramais']['agents'];

                $config->ambiente->valor_controle_qualidade = $dados['troncos']['valor_controle_qualidade'];

                $config->ambiente->menu_status_1            = $dados['painel']['menu_status_1'];
                $config->ambiente->menu_status_2            = $dados['painel']['menu_status_2'];
                $config->ambiente->menu_status_3            = $dados['painel']['menu_status_3'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                                           'filename' => $config_file));
                // Grava arquivo.
                $writer->write();

                // Redirecionamento.
                $this->_redirect("./default/parameters/");

            }

        }

        $this->view->form = $form;
        
    }

}
