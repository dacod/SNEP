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

        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section = array_keys( $sections->toArray() );
        $section = array();
        foreach ($_section as $value) {
            $section[$value] = $value;
        }

        $files = '/var/lib/asterisk/sounds/';
        if( file_exists( $files ) ) {
            
            $files = scandir( $files );
            $sounds = array("" => "");

            foreach($files as $i => $value) {
                if (substr($value, 0, 1) == '.') {
                   unset($files[$i]);
                   continue ;
                }
                if (is_dir( $files .'/'. $value)) {
                   unset($files[$i]);
                   continue ;
                }
               $sounds[$value] = $value;
            }
        }
        
        $form = new Snep_Form();       
        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $essentialData = new Zend_Config_Xml('./default/forms/queues.xml', 'essential', true);
        $essential = new Snep_Form_SubForm( $this->view->translate("Configurações Gerais"), $essentialData );

        $essential->getElement('musiconhold')->setMultiOptions($section);
        $essential->getElement('timeout')->setValue(0);                
        $essential->getElement('announce_frequency')->setValue(0);
        $essential->getElement('retry')->setValue(0);
        $essential->getElement('wrapuptime')->setValue(0);
        $essential->getElement('servicelevel')->setValue(0);
        $essential->getElement('strategy')->setMultiOptions( array('ringall' => $this->view->translate('Para todos agentes disponíveis (ringall)'),
                                                                   'roundrobin' => $this->view->translate('Procura por um agente disponível (roundrobin)'),
                                                                   'leastrecent' => $this->view->translate('Para o agente ocioso há mais tempo (leastrecent)'),
                                                                   'random'    => $this->view->translate('Aleatoriamente (random)'),
                                                                   'fewestcalls' => $this->view->translate('Para o agente que atendeu menos ligações (fewestcalls)'),
                                                                   'rrmemory' => $this->view->translate('Igualmente (rrmemory)') ));

        $form->addSubForm($essential, "essential");
        
        $advancedData =  new Zend_Config_Xml('./default/forms/queues.xml', 'advanced', true);
        $advanced = new Snep_Form_SubForm( $this->view->translate("Configurações Avançadas"), $advancedData );
        
        $boolOptions = array(1 => $this->view->translate('Sim'),
                             0 => $this->view->translate('Não') );

        $advanced->getElement('announce')->setMultiOptions($sounds);
        $advanced->getElement('queue_youarenext')->setMultiOptions($sounds);
        $advanced->getElement('queue_thereare')->setMultiOptions($sounds);
        $advanced->getElement('queue_callswaiting')->setMultiOptions($sounds);
        $advanced->getElement('queue_thankyou')->setMultiOptions($sounds);
        $advanced->getElement('leavewhenempty')->setMultiOptions( $boolOptions )->setValue(0);
        $advanced->getElement('reportholdtime')->setMultiOptions( $boolOptions )->setValue(0);
        $advanced->getElement('memberdelay')->setValue(0);
        $advanced->getElement('joinempty')
                 ->setMultiOptions( array('yes' => $this->view->translate('Sim') ,
                                          'no' => $this->view->translate('Não'),
                                          'strict' => $this->view->translate('Restrito')) )
                 ->setValue('no');         
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

        $alertsData =  new Zend_Config_Xml('./default/forms/queues.xml', 'alerts', true);
        $alerts = new Snep_Form_SubForm( $this->view->translate("Configuração de Alertas"), $alertsData);
        
        $alerts->getElement('valueMail')
               ->addValidator('NotEmpty')
               ->addValidator('EmailAddress')
               ->addFilter('StringToLower');

        $alerts->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element" /*. " $name " */))
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

        
        $files = '/var/lib/asterisk/sounds/';
        if( file_exists( $files ) ) {

            $files = scandir( $files );
            $sounds = array("" => "");

            foreach($files as $i => $value) {
                if (substr($value, 0, 1) == '.') {
                   unset($files[$i]);
                   continue ;
                }
                if (is_dir( $files .'/'. $value)) {
                   unset($files[$i]);
                   continue ;
                }
               $sounds[$value] = $value;
            }
        }

        $form = new Snep_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/edit/id/'.$id);

        $essentialData = new Zend_Config_Xml('./default/forms/queues.xml', 'essential', true);
        $essential = new Snep_Form_SubForm( $this->view->translate("Configurações Gerais"), $essentialData );

        $essential->getElement('name')->setValue( $queue['name'] )->setAttrib('readonly', true);
        $essential->getElement('musiconhold')->setMultiOptions($section)->setValue( $queue['musiconhold'] );
        $essential->getElement('timeout')->setValue( $queue['timeout'] );
        $essential->getElement('announce_frequency')->setValue( $queue['announce_frequency'] );
        $essential->getElement('retry')->setValue( $queue['retry'] );
        $essential->getElement('wrapuptime')->setValue( $queue['wrapuptime'] );
        $essential->getElement('maxlen')->setValue( $queue['maxlen']);
        $essential->getElement('servicelevel')->setValue( $queue['servicelevel'] );
        $essential->getElement('strategy')
                  ->addMultiOptions( array('ringall' => $this->view->translate('Para todos agentes disponíveis (ringall)'),
                                           'roundrobin' => $this->view->translate('Procura por um agente disponível (roundrobin)'),
                                           'leastrecent' => $this->view->translate('Para o agente ocioso há mais tempo (leastrecent)'),
                                           'random'    => $this->view->translate('Aleatoriamente (random)'),
                                           'fewestcalls' => $this->view->translate('Para o agente que atendeu menos ligações (fewestcalls)'),
                                           'rrmemory' => $this->view->translate('Igualmente (rrmemory)') ) )
                  ->setValue( $queue['strategy'] );
                 

        $form->addSubForm($essential, "essential");

        $advancedData =  new Zend_Config_Xml('./default/forms/queues.xml', 'advanced', true);
        $advanced = new Snep_Form_SubForm( $this->view->translate("Configurações Avançadas"), $advancedData );

        $boolOptions = array(1 => $this->view->translate('Sim'),
                             0 => $this->view->translate('Não') );

        $advanced->getElement('announce')->setMultiOptions($sounds)->setValue( $queue['announce'] );
        $advanced->getElement('context')->setValue( $queue['context'] );
        $advanced->getElement('queue_youarenext')->setMultiOptions($sounds)->setValue( $queue['queue_youarenext']);
        $advanced->getElement('queue_thereare')->setMultiOptions($sounds)->setValue( $queue['queue_thereare'] );
        $advanced->getElement('queue_callswaiting')->setMultiOptions($sounds)->setValue( $queue['queue_callswaiting'] );
        $advanced->getElement('queue_thankyou')->setMultiOptions($sounds)->setValue( $queue['queue_thankyou'] );
        $advanced->getElement('joinempty')
                 ->setMultiOptions( array('yes' => $this->view->translate('Sim') ,
                                          'no' => $this->view->translate('Não'),
                                          'strict' => $this->view->translate('Restrito')) )
                 ->setValue( $queue['joinempty']);
        $advanced->getElement('leavewhenempty')->setMultiOptions( $boolOptions )->setValue($queue['leavewhenempty']);
        $advanced->getElement('reportholdtime')->setMultiOptions( $boolOptions )->setValue( $queue['reportholdtime']);
        $advanced->getElement('memberdelay')->setValue( $queue['memberdelay']);
        $advanced->getElement('weight')->setValue( $queue['weight']);
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

        $alertsData =  new Zend_Config_Xml('./default/forms/queues.xml', 'alerts', true);
        $alerts = new Snep_Form_SubForm( $this->view->translate("Configurações de Alerta"), $alertsData);
        $queueAlerts = Snep_Alerts::getAlert( $id );

        foreach($queueAlerts as $queueAlert) {

            switch ($queueAlert['tipo']) {
                case 'mail':
                        $alerts->getElement('checkMail')->setValue( $queueAlert['ativo'] );
                        $alerts->getElement('valueMail')->addValidator('NotEmpty')
                                                        ->addValidator('EmailAddress')
                                                        ->addFilter('StringToLower')
                                                        ->setValue($queueAlert['destino']);
                        $alerts->getElement('tmeMail')->setValue( $queueAlert['tme'] );
                        $alerts->getElement('nmlMail')->setValue( $queueAlert['sla']  );
                    break;
                case 'sound';
                        $alerts->getElement('checkSound')->setValue( $queueAlert['ativo'] );
                        $alerts->getElement('tmeSound')->setValue( $queueAlert['tme'] );
                        $alerts->getElement('nmlSound')->setValue( $queueAlert['sla'] );
                    break;
                case 'visual';
                        $alerts->getElement('checkVisual')->setValue( $queueAlert['ativo'] );
                        $alerts->getElement('tmeVisual')->setValue( $queueAlert['tme'] );
                        $alerts->getElement('nmlVisual')->setValue( $queueAlert['sla'] );
                    break;
            }
        }

        $alerts->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element" /*. " $name"*/))
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