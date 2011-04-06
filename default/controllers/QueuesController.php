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

/**
 * Queues Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class QueuesController extends Zend_Controller_Action {

    /**
     * List all Queues
     */
    public function indexAction() {
        
        $this->view->breadcrumb = $this->view->translate("Cadastro » Filas");

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from("queues");
                        
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }
        
        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect( $select );
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->queues = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("name"      => $this->view->translate("Nome"),
                        "musiconhold"      => $this->view->translate("Classe de Áudio"),
                        "strategy"    => $this->view->translate("Estratégia"),
                        "sla"    => $this->view->translate("SLA"),
                        "timeout"    => $this->view->translate("Tempo Limite"));
                        

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Incluir Fila"),
                                          "css"     => "include"));
    }

    /**
     *  Add Queue
     */
    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Filas » Cadastro");

        $db = Zend_Registry::get('db');

        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section = array_keys( $sections->toArray() );
        $section = array();
        foreach ($_section as $value) {
            $section[$value] = $value;
        }

        
        $files = scandir( APPLICATION_PATH . '/sounds/pt_BR' );
        $sounds=array("" => "");

        foreach($files as $i => $value) {
            if (substr($value, 0, 1) == '.') {
               unset($files[$i]);
               continue ;
            }
            if (is_dir( APPLICATION_PATH .'/sounds/pt_BR/'. $value)) {
               unset($files[$i]);
               continue ;
            }
           $sounds[$value] = $value;
        }

        $form = new Snep_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/add');        

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $essentialData = new Zend_Config_Xml('./default/forms/queues.xml', 'essential', true);
        $essential = new Snep_Form_SubForm( $this->view->translate("Configurações Gerais"), $essentialData );
        
        $name = $essential->getElement('name');
        $name->setLabel( $this->view->translate("Nome da Fila"));

        $musiconhold = $essential->getElement('musiconhold');
        $musiconhold->setLabel( $this->view->translate("Classe da Música de Espera"))
                    ->setMultiOptions($section);

        $timeout = $essential->getElement('timeout');
        $timeout->setLabel( $this->view->translate("Tempo de toque por agente"))
                ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                ->setValue(0);                

        $announce_frequency  = $essential->getElement('announce_frequency');
        $announce_frequency->setLabel($this->view->translate("Intervalo de repetição das mensagens ao chamador") )
                ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                ->setValue(0);

        $retry  = $essential->getElement('retry');
        $retry->setLabel($this->view->translate("Tempo de espera para tentar chamar todos os Agentes novamente") )
              ->setDescription( '('. $this->view->translate('Em segundos') . ')')
              ->setValue(0);

        $wrapuptime  = $essential->getElement('wrapuptime');
        $wrapuptime->setLabel($this->view->translate("Tempo de descanso do agente entre uma chamada e outra") )
                   ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                   ->setValue(0);

        $maxlen  = $essential->getElement('maxlen');
        $maxlen->setLabel($this->view->translate("Numero maximo de chamadas em espera na fila") );

        $servicelevel  = $essential->getElement('servicelevel');
        $servicelevel->setLabel($this->view->translate("Nível de Serviço da Fila (Utilizado para Estatísticas e Monitoramento)") )
                     ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                     ->setValue(0);

        $strategy = $essential->getElement('strategy');
        $strategy->setLabel($this->view->translate("Estratégia de distribuição das chamadas") )
                 ->addMultiOptions( array('ringall' => $this->view->translate('Para todos agentes disponíveis (ringall)'),
                                          'roundrobin' => $this->view->translate('Procura por um agente disponível (roundrobin)'),
                                          'leastrecent' => $this->view->translate('Para o agente ocioso há mais tempo (leastrecent)'),
                                          'random'    => $this->view->translate('Aleatoriamente (random)'),
                                          'fewestcalls' => $this->view->translate('Para o agente que atendeu menos ligações (fewestcalls)'),
                                          'rrmemory' => $this->view->translate('Igualmente (rrmemory)') ));

        $form->addSubForm($essential, "essential");
        
        $advancedData =  new Zend_Config_Xml('./default/forms/queues.xml', 'advanced', true);
        $advanced = new Snep_Form_SubForm( $this->view->translate("Configurações Avançadas"), $advancedData );
        
        $announce = $advanced->getElement('announce');
        $announce->setLabel( $this->view->translate("Áudio de Anúncio da Fila"))
                 ->setMultiOptions($sounds)
                 ->setDescription( $this->view->translate('Arquivo de som para Anúncio da chamada ao Agente imediatamente após atendimento') );

        $context = $advanced->getElement('context');
        $context->setLabel($this->view->translate("Desvio para Contexto"));
        $context->setDescription( $this->view->translate("Para qual contexto desviar a chamada quando o chamador digitar qualquer dígito enquanto espera") );

        $queue_youarenext = $advanced->getElement('queue_youarenext');
        $queue_youarenext->setLabel($this->view->translate("Áudio: Você é o próximo da fila") )
                         ->setMultiOptions($sounds);

        $queue_thereare = $advanced->getElement('queue_thereare');
        $queue_thereare->setLabel($this->view->translate("Áudio: Você está aqui") )
                       ->setMultiOptions($sounds);

        $queue_callswaiting = $advanced->getElement('queue_callswaiting');
        $queue_callswaiting->setLabel($this->view->translate("Áudio: Número de chamadas aguardando") )
                           ->setMultiOptions($sounds);

        $queue_thankyou  = $advanced->getElement('queue_thankyou');
        $queue_thankyou->setLabel($this->view->translate("Áudio: Obrigado por aguardar") )
                        ->setMultiOptions($sounds);

        $boolOptions = array(1 => $this->view->translate('Sim'),
                             0 => $this->view->translate('Não') );

        $joinempty  = $advanced->getElement('joinempty');
        $joinempty->setLabel($this->view->translate("Usuários podem entrar na Fila mesmo sem Agentes presentes?") )
                  ->setMultiOptions( array('yes' => $this->view->translate('Sim') ,
                                           'no' => $this->view->translate('Não'),
                                           'strict' => $this->view->translate('Restrito')) )
                  ->setValue('no');

        $leavewhenempty  = $advanced->getElement('leavewhenempty');
        $leavewhenempty->setLabel($this->view->translate("Chamadas devem sair da fila quando os Agentes sairem?") )
                        ->setMultiOptions( $boolOptions )
                        ->setValue(0);

        $reportholdtime  = $advanced->getElement('reportholdtime');
        $reportholdtime->setLabel($this->view->translate("Avisar ao Agente o tempo que a chamada esta esperando na fila") )
                        ->setMultiOptions( $boolOptions )
                        ->setValue(0);

        $memberdelay  = $advanced->getElement('memberdelay');
        $memberdelay->setLabel($this->view->translate("Tempo de silêncio para o Agente antes de conectá-lo ao chamador") )
                    ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                    ->setValue(0);

        $weight  = $advanced->getElement('weight');
        $weight->setLabel($this->view->translate("Prioridade da fila") );
/*
        $autofill  = $advanced->getElement('autofill');
        $autofill->setLabel($this->view->translate("Distribuir chamadas simultaneamente na fila até que não existam mais agentes disponíveis ou chamadas na fila") )
                 ->setMultiOptions( $boolOptions )
                 ->setValue('no');

        $autopause  = $advanced->getElement('autopause');
        $autopause->setLabel($this->view->translate("Pausar automaticamente um agente quando ele não atender uma chamada") )
                  ->setMultiOptions( $boolOptions )
                  ->setValue('no');
*/        
        $form->addSubForm($advanced, "advanced");

        $alerts = new Snep_Form_SubForm( $this->view->translate("Configuração de Alertas"));

        $checkMail = new Zend_Form_Element_Checkbox('checkMail');
        $checkMail->setLabel( $this->view->translate("Alerta de E-mail") )
                  ->setDescription('Habilitar');
        $valueMail = new Zend_Form_Element_Text('valueMail');
        $valueMail->removeDecorator("DtDdWrapper")
                  ->setLabel( $this->view->translate('E-mail') )
                  ->addValidator('NotEmpty')
                  ->addValidator('EmailAddress')
                  ->addFilter('StringToLower');
        $tmeMail = new Zend_Form_Element_Text('tmeMail');
        $tmeMail->removeDecorator("DtDdWrapper")
                ->setlabel( $this->view->translate('Tempo máximo de espera') )
                ->setDescription( '('. $this->view->translate('Em segundos') . ')');
        $nmlMail = new Zend_Form_Element_Text('nmlMail');
        $nmlMail->setlabel($this->view->translate('Número máximo em espera'));
                

        $checkSound = new Zend_Form_Element_Checkbox('checkSound');
        $checkSound->setLabel( $this->view->translate("Alerta Sonoro") )
                   ->setDescription('Habilitar');
        $tmeSound = new Zend_Form_Element_Text('tmeSound');
        $tmeSound->removeDecorator("DtDdWrapper")
                ->setlabel( $this->view->translate('Tempo máximo de espera') )
                ->setDescription( '('. $this->view->translate('Em segundos') . ')');
        $nmlSound = new Zend_Form_Element_Text('nmlSound');
        $nmlSound->setlabel($this->view->translate('Número máximo em espera'));
                 

        $checkVisual = new Zend_Form_Element_Checkbox('checkVisual');
        $checkVisual->setLabel( $this->view->translate("Alerta Visual") )
                    ->setDescription('Habilitar');
        $tmeVisual = new Zend_Form_Element_Text('tmeVisual');
        $tmeVisual->removeDecorator("DtDdWrapper")
                  ->setlabel( $this->view->translate('Tempo máximo de espera') )
                  ->setDescription( '('. $this->view->translate('Em segundos') . ')');
        $nmlVisual = new Zend_Form_Element_Text('nmlVisual');
        $nmlVisual->setlabel($this->view->translate('Número máximo em espera'));
        
        $alerts->addElements( array($checkMail, $valueMail, $tmeMail, $nmlMail));
        $alerts->addElements( array($checkSound, $tmeSound, $nmlSound));
        $alerts->addElements(   array($checkVisual, $tmeVisual, $nmlVisual)  );                
        $alerts->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element" . " $name"))
        ));

        $form->addSubForm($alerts, "alerts");
        
        if($this->_request->getPost()) {

            $dados = array('name'              => $_POST['essential']['name'],
                           'musiconhold'       => $_POST['essential']['musiconhold'],
                           'announce'          => $_POST['advanced']['announce'],
                           'context'           => $_POST['advanced']['context'],
                           'timeout'           => $_POST['essential']['timeout'],
                           'queue_youarenext'  => $_POST['advanced']['queue_youarenext'],
                           'queue_thereare'    => $_POST['advanced']['queue_thereare'],
                           'queue_callswaiting'=> $_POST['advanced']['queue_callswaiting'],
                           'queue_thankyou'    => $_POST['advanced']['queue_thankyou'],
                           'announce_frequency'=> $_POST['essential']['announce_frequency'],
                           'retry'             => $_POST['essential']['retry'],
                           'wrapuptime'        => $_POST['essential']['wrapuptime'],
                           'maxlen'            => $_POST['essential']['maxlen'],
                           'servicelevel'      => $_POST['essential']['servicelevel'],
                           'strategy'          => $_POST['essential']['strategy'],
                           'joinempty'         => $_POST['advanced']['joinempty'],
                           'leavewhenempty'    => $_POST['advanced']['leavewhenempty'],
                           'reportholdtime'    => $_POST['advanced']['reportholdtime'],
                           'memberdelay'       => $_POST['advanced']['memberdelay'],
                           'weight'            => $_POST['advanced']['weight'],
                            /*
                           'autofill'          => $_POST['advanced']['autofill'],
                           'autopause'         => $_POST['advanced']['autopause']
                            */
                );

            $form_isValid = $form->isValid($_POST);

            if( $form_isValid ) {

                Snep_Queues_Manager::add($dados);

                if( $_POST['alerts'] ) {

                    $addAlerts = $_POST['alerts'];
                    $queue = $_POST['essential']['name'];

                    if( $addAlerts['checkMail'] != 0) {
                    Snep_Alerts::setAlert(array('queue' => $queue,
                                                  'type' => 'mail',
                                                  'tme' => $addAlerts['tmeMail'],
                                                  'sla' => $addAlerts['nmlMail'],
                                                  'destino' => $addAlerts['valueMail'],
                                                  'check' => 1));
                    }
                    if( $addAlerts['checkSound'] != 0) {
                    Snep_Alerts::setAlert(array('queue' => $queue,
                                                  'type' => 'sound',
                                                  'tme' => $addAlerts['tmeSound'],
                                                  'sla' => $addAlerts['nmlSound'],
                                                  'destino' => 'sound',
                                                  'check' => 1));
                    }
                    if( $addAlerts['checkVisual'] != 0) {
                    Snep_Alerts::setAlert(array('queue' => $queue,
                                                  'type' => 'visual',
                                                  'tme' => $addAlerts['tmeVisual'],
                                                  'sla' => $addAlerts['nmlVisual'],
                                                  'destino' => 'visual',
                                                  'check' => 1));
                    }
                }
                $this->_redirect( $this->getRequest()->getControllerName() );
            }
        }
        $this->view->form = $form;

    }

    /**
     * Edit Queues
     */
    public function editAction() {

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $id = $this->_request->getParam("id");
        $this->view->breadcrumb = $this->view->translate("Filas » Editar » $id");

        $queue = Snep_Queues_Manager::get($id);

        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section = array_keys( $sections->toArray() );
        $section = array();
        foreach ($_section as $value) {
            $section[$value] = $value;
        }

        
        $files = scandir( APPLICATION_PATH . '/sounds/pt_BR' );
        $sounds=array("" => "");
        foreach($files as $i => $value) {
            if (substr($value, 0, 1) == '.') {
               unset($files[$i]);
               continue ;
            }
            if (is_dir(APPLICATION_PATH .'/sounds/pt_BR/'. $value)) {
               unset($files[$i]);
               continue ;
            }
            $sounds[$value] = $value;
        }

        $form = new Snep_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/edit/id/'.$id);

        $essentialData = new Zend_Config_Xml('./default/forms/queues.xml', 'essential', true);
        $essential = new Snep_Form_SubForm( $this->view->translate("Configurações Gerais"), $essentialData );

        $name = $essential->getElement('name');
        $name->setLabel( $this->view->translate("Nome da Fila"))
             ->setValue( $queue['name'] )
             ->setAttrib('readonly', true);

        $musiconhold = $essential->getElement('musiconhold');
        $musiconhold->setLabel( $this->view->translate("Classe da Música de Espera"))
                    ->setMultiOptions($section)
                    ->setValue( $queue['musiconhold'] );

        $timeout = $essential->getElement('timeout');
        $timeout->setLabel( $this->view->translate("Tempo de toque por agente"))
                ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                ->setValue( $queue['timeout'] );


        $announce_frequency  = $essential->getElement('announce_frequency');
        $announce_frequency->setLabel($this->view->translate("Intervalo de repetição das mensagens ao chamador") )
                ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                ->setValue( $queue['announce_frequency'] );

        $retry  = $essential->getElement('retry');
        $retry->setLabel($this->view->translate("Tempo de espera para tentar chamar todos os Agentes novamente") )
              ->setDescription( '('. $this->view->translate('Em segundos') . ')')
              ->setValue( $queue['retry'] );

        $wrapuptime  = $essential->getElement('wrapuptime');
        $wrapuptime->setLabel($this->view->translate("Tempo de descanso do agente entre uma chamada e outra") )
                   ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                   ->setValue( $queue['wrapuptime'] );

        $maxlen  = $essential->getElement('maxlen');
        $maxlen->setLabel($this->view->translate("Número máximo de chamadas em espera na fila") )
                ->setValue( $queue['maxlen']);

        $servicelevel  = $essential->getElement('servicelevel');
        $servicelevel->setLabel($this->view->translate("Nível de Serviço da Fila (Utilizado para Estatísticas e Monitoramento)") )
                     ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                     ->setValue( $queue['servicelevel'] );

        $strategy = $essential->getElement('strategy');
        $strategy->setLabel($this->view->translate("Estratégia de distribuição das chamadas") )
                 ->addMultiOptions( array('ringall' => $this->view->translate('Para todos agentes disponíveis (ringall)'),
                                          'roundrobin' => $this->view->translate('Procura por um agente disponível (roundrobin)'),
                                          'leastrecent' => $this->view->translate('Para o agente ocioso há mais tempo (leastrecent)'),
                                          'random'    => $this->view->translate('Aleatoriamente (random)'),
                                          'fewestcalls' => $this->view->translate('Para o agente que atendeu menos ligações (fewestcalls)'),
                                          'rrmemory' => $this->view->translate('Igualmente (rrmemory)') ))
                 ->setValue( $queue['strategy'] );


        $form->addSubForm($essential, "essential");

        $advancedData =  new Zend_Config_Xml('./default/forms/queues.xml', 'advanced', true);
        $advanced = new Snep_Form_SubForm( $this->view->translate("Configurações Avançadas"), $advancedData );

        $announce = $advanced->getElement('announce');
        $announce->setLabel( $this->view->translate("Áudio de Anúncio da Fila"))
                 ->setMultiOptions($sounds)
                 ->setValue( $queue['announce'] );

        $context = $advanced->getElement('context');
        $context->setLabel($this->view->translate("Desvio para Contexto"));
        $context->setDescription( $this->view->translate("Para qual contexto desviar a chamada quando o chamador digitar qualquer dígito enquanto espera") )
                ->setValue( $queue['context'] );

        $queue_youarenext = $advanced->getElement('queue_youarenext');
        $queue_youarenext->setLabel($this->view->translate("Áudio: Você é o próximo da fila") )
                         ->setMultiOptions($sounds)
                         ->setValue( $queue['queue_youarenext']);

        $queue_thereare = $advanced->getElement('queue_thereare');
        $queue_thereare->setLabel($this->view->translate("Áudio: Você está aqui") )
                       ->setMultiOptions($sounds)
                       ->setValue( $queue['queue_thereare'] );

        $queue_callswaiting = $advanced->getElement('queue_callswaiting');
        $queue_callswaiting->setLabel($this->view->translate("Áudio: Número de chamadas aguardando") )
                           ->setMultiOptions($sounds)
                           ->setValue( $queue['queue_callswaiting'] );

        $queue_thankyou  = $advanced->getElement('queue_thankyou');
        $queue_thankyou->setLabel($this->view->translate("Áudio: Obrigado por aguardar") )
                        ->setMultiOptions($sounds)
                        ->setValue( $queue['queue_thankyou'] );

        $boolOptions = array(1 => $this->view->translate('Sim'),
                             0 => $this->view->translate('Não') );

        $joinempty  = $advanced->getElement('joinempty');
        $joinempty->setLabel($this->view->translate("Usuários podem entrar na Fila mesmo sem Agentes presentes?") )
                  ->setMultiOptions( array('yes' => $this->view->translate('Sim') ,
                                           'no' => $this->view->translate('Não'),
                                           'strict' => $this->view->translate('Restrito')) )
                  ->setValue( $queue['joinempty']);

        $leavewhenempty  = $advanced->getElement('leavewhenempty');
        $leavewhenempty->setLabel($this->view->translate("Chamadas devem sair da fila quando os Agentes sairem?") )
                        ->setMultiOptions( $boolOptions )
                        ->setValue($queue['leavewhenempty']);

        $reportholdtime  = $advanced->getElement('reportholdtime');
        $reportholdtime->setLabel($this->view->translate("Avisar ao Agente o tempo que a chamada esta esperando na fila") )
                        ->setMultiOptions( $boolOptions )
                        ->setValue( $queue['reportholdtime']);

        $memberdelay  = $advanced->getElement('memberdelay');
        $memberdelay->setLabel($this->view->translate("Tempo de silêncio para o Agente antes de conectá-lo ao chamador") )
                    ->setDescription( '('. $this->view->translate('Em segundos') . ')')
                    ->setValue( $queue['memberdelay']);

        $weight  = $advanced->getElement('weight');
        $weight->setLabel($this->view->translate("Prioridade da fila") )
               ->setValue( $queue['weight']);
/*
        $autofill  = $advanced->getElement('autofill');
        $autofill->setLabel($this->view->translate("Distribuir chamadas simultaneamente na fila até que não existam mais agentes disponíveis ou chamadas na fila") )
                 ->setMultiOptions( $boolOptions )
                 ->setValue('no');

        $autopause  = $advanced->getElement('autopause');
        $autopause->setLabel($this->view->translate("Pausar automaticamente um agente quando ele não atender uma chamada") )
                  ->setMultiOptions( $boolOptions )
                  ->setValue('no');
*/

        $form->addSubForm($advanced, "advanced");

        $alerts = new Snep_Form_SubForm( $this->view->translate("Configurações de Alerta"));

        $checkMail = new Zend_Form_Element_Checkbox('checkMail');
        $checkMail->setLabel( $this->view->translate("Alerta de E-mail") )
                  ->setDescription('Habilitar');
        $valueMail = new Zend_Form_Element_Text('valueMail');
        $valueMail->removeDecorator("DtDdWrapper")
                  ->setLabel( $this->view->translate('E-mail') )
                  ->addValidator('NotEmpty')
                  ->addValidator('EmailAddress')
                  ->addFilter('StringToLower');
        $tmeMail = new Zend_Form_Element_Text('tmeMail');
        $tmeMail->removeDecorator("DtDdWrapper")
                ->setlabel( $this->view->translate('Tempo máximo de espera') )
                ->setDescription( '('. $this->view->translate('Em segundos') . ')');
        $nmlMail = new Zend_Form_Element_Text('nmlMail');
        $nmlMail->setlabel($this->view->translate('Número máximo em espera'));


        $checkSound = new Zend_Form_Element_Checkbox('checkSound');
        $checkSound->setLabel( $this->view->translate("Alerta Sonoro") )
                   ->setDescription('Habilitar');
        $tmeSound = new Zend_Form_Element_Text('tmeSound');
        $tmeSound->removeDecorator("DtDdWrapper")
                ->setlabel( $this->view->translate('Tempo máximo de espera') )
                ->setDescription( '('. $this->view->translate('Em segundos') . ')');
        $nmlSound = new Zend_Form_Element_Text('nmlSound');
        $nmlSound->setlabel($this->view->translate('Número máximo em espera'));

        $checkVisual = new Zend_Form_Element_Checkbox('checkVisual');
        $checkVisual->setLabel( $this->view->translate("Alerta Visual") )
                    ->setDescription('Habilitar');
        $tmeVisual = new Zend_Form_Element_Text('tmeVisual');
        $tmeVisual->removeDecorator("DtDdWrapper")
                  ->setlabel( $this->view->translate('Tempo máximo de espera') )
                  ->setDescription( '('. $this->view->translate('Em segundos') . ')');
        $nmlVisual = new Zend_Form_Element_Text('nmlVisual');
        $nmlVisual->setlabel($this->view->translate('Número máximo em espera'));

        $queueAlerts = Snep_Alerts::getAlert( $id );

        foreach($queueAlerts as $queueAlert) {

            switch ($queueAlert['tipo']) {
                case 'mail':
                    $checkMail->setValue( $queueAlert['ativo'] );
                    $valueMail->setValue( $queueAlert['destino'] );
                    $tmeMail->setvalue( $queueAlert['tme'] );
                    $nmlMail->setValue( $queueAlert['sla'] );
                    break;
                case 'sound';
                    $checkSound->setValue( $queueAlert['ativo'] );
                    $tmeSound->setValue( $queueAlert['tme'] );
                    $nmlSound->setValue( $queueAlert['sla'] );
                    break;
                case 'visual';
                    $checkVisual->setValue( $queueAlert['ativo'] );
                    $tmeVisual->setValue( $queueAlert['tme'] );
                    $nmlVisual->setValue( $queueAlert['sla'] );
                    break;
            }
        }
        
        $alerts->addElements( array($checkMail, $valueMail, $tmeMail, $nmlMail));
        $alerts->addElements( array($checkSound, $tmeSound, $nmlSound));
        $alerts->addElements(   array($checkVisual, $tmeVisual, $nmlVisual)  );
        $alerts->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element" . " $name"))
        ));

        $form->addSubForm($alerts, "alerts");

        if($this->_request->getPost()) {
            
            $dados = array('name'              => $_POST['essential']['name'],
                           'musiconhold'       => $_POST['essential']['musiconhold'],
                           'announce'          => $_POST['advanced']['announce'],
                           'context'           => $_POST['advanced']['context'],
                           'timeout'           => $_POST['essential']['timeout'],
                           'queue_youarenext'  => $_POST['advanced']['queue_youarenext'],
                           'queue_thereare'    => $_POST['advanced']['queue_thereare'],
                           'queue_callswaiting'=> $_POST['advanced']['queue_callswaiting'],
                           'queue_thankyou'    => $_POST['advanced']['queue_thankyou'],
                           'announce_frequency'=> $_POST['essential']['announce_frequency'],
                           'retry'             => $_POST['essential']['retry'],
                           'wrapuptime'        => $_POST['essential']['wrapuptime'],
                           'maxlen'            => $_POST['essential']['maxlen'],
                           'servicelevel'      => $_POST['essential']['servicelevel'],
                           'strategy'          => $_POST['essential']['strategy'],
                           'joinempty'         => $_POST['advanced']['joinempty'],
                           'leavewhenempty'    => $_POST['advanced']['leavewhenempty'],
                           'reportholdtime'    => $_POST['advanced']['reportholdtime'],
                           'memberdelay'       => $_POST['advanced']['memberdelay'],
                           'weight'            => $_POST['advanced']['weight'],
                            /*
                           'autofill'          => $_POST['advanced']['autofill'],
                           'autopause'         => $_POST['advanced']['autopause']
                            */
                );
            

            $form_isValid = $form->isValid($_POST);

            if( $form_isValid ) {

                Snep_Queues_Manager::edit($dados);

                if( $_POST['alerts'] ) {

                    $addAlerts = $_POST['alerts'];
                    $queue = $_POST['essential']['name'];

                    Snep_Alerts::resetAlert($queue);

                    if( $addAlerts['checkMail'] != 0) {
                    Snep_Alerts::setAlert(array('queue' => $queue,
                                                  'type' => 'mail',
                                                  'tme' => $addAlerts['tmeMail'],
                                                  'sla' => $addAlerts['nmlMail'],
                                                  'destino' => $addAlerts['valueMail'],
                                                  'check' => 1));
                    }
                    if( $addAlerts['checkSound'] != 0) {
                    Snep_Alerts::setAlert(array('queue' => $queue,
                                                  'type' => 'sound',
                                                  'tme' => $addAlerts['tmeSound'],
                                                  'sla' => $addAlerts['nmlSound'],
                                                  'destino' => 'sound',
                                                  'check' => 1));
                    }
                    if( $addAlerts['checkVisual'] != 0) {
                    Snep_Alerts::setAlert(array('queue' => $queue,
                                                  'type' => 'visual',
                                                  'tme' => $addAlerts['tmeVisual'],
                                                  'sla' => $addAlerts['nmlVisual'],
                                                  'destino' => 'visual',
                                                  'check' => 1));
                    }
                }
                

                $this->_redirect( $this->getRequest()->getControllerName() );
            }
            
        }
        $this->view->form = $form;
    }

    /**
     * Remove a queue
     */
    public function removeAction() {

       $this->view->breadcrumb = $this->view->translate("Filas » Remover");
       $id = $this->_request->getParam('id');

       Snep_Queues_Manager::remove($id);
       Snep_Queues_Manager::resetAlert($id);
       
       $this->_redirect( $this->getRequest()->getControllerName() );

    }

    /**
     * Set member queue
     * 
     */
    public function membersAction () {

        $db = Zend_Registry::get("db");
        $queue = $this->_request->getParam("id");

        $this->view->breadcrumb = $this->view->translate("Filas » Membros da Fila » ". $queue);

        $members = Snep_Queues_Manager::getMembers($queue);
        $mem = array();
        foreach ($members as $m) {
            $mem[$m['interface']] = $m['interface'];
        }
        
        $_allMembers = Snep_Queues_Manager::getAllMembers();
        $notMem = array();
        foreach ($_allMembers as $row) {
           $cd = explode(";",$row['canal']);
           foreach ($cd as $canal) {
              if (strlen($canal) > 0) {
                  if( ! array_key_exists($canal, $mem)) {
                    $notMem[$canal] = $row['callerid']." ($canal)({$row['group']})";
                  }
              }
            }
        }

        $form = new Snep_Form();

        $this->view->objSelectBox = 'members';
        $form->setSelectBox( $this->view->objSelectBox, $this->view->translate("Adicionar membro"), $notMem, $mem);

        $queueId = new Zend_Form_Element_hidden('id');
        $queueId->setvalue($queue);
        $form->addElement($queueId);

        $this->view->form = $form;

        if($this->_request->getPost()) {
             Snep_Queues_Manager::removeAllMembers($queue);
             
            if( isset($_POST['box_add']) ) {
                foreach ($_POST['box_add'] as $add) {
                    Snep_Queues_Manager::insertMember($queue, $add);
                }
            }

            $this->_redirect( $this->getRequest()->getControllerName() . '/' );

        }
        
        

        
    }

    /**
     * Depracated method
     * PALEATIVOS para adaptação da interface.     *
     */
    public function cidadeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $estado = isset($_POST['uf']) && $_POST['uf']!= "" ? $_POST['uf'] : display_error($LANG['msg_nostate'],true);
        $municipios = Snep_Cnl::get($estado);

        $options = '';
        if(count($municipios > 0)) {
            foreach($municipios as $cidades) {
                $options .= "<option  value='{$cidades['municipio']}' > {$cidades['municipio']} </option> " ;
            }
        }else{
                $options = "<option> {$LANG['select']} </option>";
        }

        echo $options;
    }
    
}