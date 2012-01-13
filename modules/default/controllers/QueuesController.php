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

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Queues")
        ));

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'.
                           $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from("queue");

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("$field LIKE '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->queues = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("name" => $this->view->translate("Name"),
            "musiconhold" => $this->view->translate("Audio Class"),
            "strategy" => $this->view->translate("Strategy"),
            "servicelevel" => $this->view->translate("SLA"),
            "timeout" => $this->view->translate("Timeout"));


        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                "display" => $this->view->translate("Add Queue"),
                "css" => "include"));
    }

    /**
     *  Add Queue
     */
    public function addAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Queues"),
            $this->view->translate("Add Queues")
        ));

        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section = array_keys($sections->toArray());
        $section = array();
        foreach ($_section as $value) {
            $section[$value] = $value;
        }

        $files = '/var/lib/asterisk/sounds/';
        if (file_exists($files)) {

            $files = scandir($files);
            $sounds = array("" => "");

            foreach ($files as $i => $value) {
                if (substr($value, 0, 1) == '.') {
                    unset($files[$i]);
                    continue;
                }
                if (is_dir($files . '/' . $value)) {
                    unset($files[$i]);
                    continue;
                }
                $sounds[$value] = $value;
            }
        }

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');

        $form = new Snep_Form();
        $this->view->url = $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName();

        $essential = new Snep_Form_SubForm($this->view->translate("General Configuration"),
                                new Zend_Config_Xml('./modules/default/forms/queues.xml', 'essential', true));

        $essential->getElement('musiconhold')->setMultiOptions($section);
        
        $essential->getElement('strategy')->setMultiOptions(
                array('ringall' => $this->view->translate('For all agents available (ringall)'),
                      'roundrobin' => $this->view->translate('Search for a available agent (roundrobin)'),
                      'leastrecent' => $this->view->translate('For the agent idle for the most time (leastrecent)'),
                      'random' => $this->view->translate('Randomly (random)'),
                      'fewestcalls' => $this->view->translate('For the agent that answered less calls (fewestcalls)'),
                      'rrmemory' => $this->view->translate('Equally (rrmemory)')) );

        $form->addSubForm($essential, "essential");

        $advancedData = new Zend_Config_Xml('./modules/default/forms/queues.xml', 'advanced', true);
        $advanced = new Snep_Form_SubForm($this->view->translate("Advanced Configuration"), $advancedData);

        $boolOptions = array(1 => $this->view->translate('Yes'),
                             0 => $this->view->translate('No'));

        $advanced->getElement('announce')->setMultiOptions($sounds);
        $advanced->getElement('queue_youarenext')->setMultiOptions($sounds);
        $advanced->getElement('queue_thereare')->setMultiOptions($sounds);
        $advanced->getElement('queue_callswaiting')->setMultiOptions($sounds);
        $advanced->getElement('queue_thankyou')->setMultiOptions($sounds);
        $advanced->getElement('leavewhenempty')->setMultiOptions($boolOptions)->setValue(0);
        $advanced->getElement('reportholdtime')->setMultiOptions($boolOptions)->setValue(0);
        

        $advanced->getElement('joinempty')
                ->setMultiOptions(array('yes' => $this->view->translate('Yes'),
                                        'no' => $this->view->translate('No'),
                                        'strict' => $this->view->translate('Restrict')))
                ->setValue('no');

        $form->addSubForm($advanced, "advanced");

        if ($this->_request->getPost()) {

            $dados = array('name' =>        $_POST['essential']['name'],
                           'musiconhold' => $_POST['essential']['musiconhold'],
                           'announce' =>    $_POST['advanced']['announce'],
                           'context' =>     $_POST['advanced']['context'],
                           'timeout' =>     $_POST['essential']['timeout'],
                           'queue_youarenext' =>    $_POST['advanced']['queue_youarenext'],
                           'queue_thereare' =>      $_POST['advanced']['queue_thereare'],
                           'queue_callswaiting' =>  $_POST['advanced']['queue_callswaiting'],
                           'queue_thankyou' =>      $_POST['advanced']['queue_thankyou'],
                           'announce_frequency' =>  $_POST['essential']['announce_frequency'],
                           'announce_round_seconds' => 0,
                           'retry' =>        $_POST['essential']['retry'],
                           'wrapuptime' =>   $_POST['essential']['wrapuptime'],
                           'maxlen' =>       $_POST['essential']['maxlen'],
                           'servicelevel' => $_POST['essential']['servicelevel'],
                           'strategy' =>     $_POST['essential']['strategy'],
                           'joinempty' =>    $_POST['advanced']['joinempty'],
                           'leavewhenempty' => $_POST['advanced']['leavewhenempty'],
                           'reportholdtime' => $_POST['advanced']['reportholdtime'],
                           'memberdelay' =>    $_POST['advanced']['memberdelay'],
                           'weight' =>         $_POST['advanced']['weight'],
                           'autopause' =>      $_POST['advanced']['autopause'],
                           'autofill' =>       $_POST['advanced']['autofill'],
                           'monitor_join' => NULL,
                           'monitor_format' => NULL,
                           'queue_holdtime' => NULL,
                           'queue_minutes' => NULL,
                           'queue_seconds' => NULL,
                           'queue_lessthan' => NULL,
                           'queue_reporthold' => NULL,
                           'announce_holdtime' => NULL,
                           'eventmemberstatus' => NULL,
                           'eventwhencalled' => NULL,
                           'timeoutrestart' => NULL,
                           'queue_name' => NULL,
                           'interface' => NULL);

            $form_isValid = $form->isValid($dados);

            if ($form_isValid) {

                $queue = new Snep_Queues_Manager();
                $queue->insert($dados);                
                $this->_redirect($this->getRequest()->getControllerName());
            }

        }
        $this->view->form = $form;
    }

    /**
     * Edit Queues
     */
    public function editAction() {

        $this->view->url = $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $id = $this->_request->getParam("id");

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Queues"),
            $this->view->translate("Edit $id")
        ));
        
        $obj = new Snep_Queues_Manager();
        $select = $obj->select()->where("id_queue = ?", $id);
        $queue = $obj->fetchRow($select)->toArray();


        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section = array_keys($sections->toArray());
        $section = array();
        foreach ($_section as $value) {
            $section[$value] = $value;
        }

        $files = '/var/lib/asterisk/sounds/';
        if (file_exists($files)) {

            $files = scandir($files);
            $sounds = array("" => "");

            foreach ($files as $i => $value) {
                if (substr($value, 0, 1) == '.') {
                    unset($files[$i]);
                    continue;
                }
                if (is_dir($files . '/' . $value)) {
                    unset($files[$i]);
                    continue;
                }
                $sounds[$value] = $value;
            }
        }

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');

        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() .'/'.
                         $this->getRequest()->getControllerName() . '/edit/id/'.$id);

        $essentialData = new Zend_Config_Xml('./modules/default/forms/queues.xml', 'essential', true);
        $essential = new Snep_Form_SubForm($this->view->translate("General Configuration"), $essentialData);

        $essential->getElement('name')->setValue($queue['name'])->setAttrib('readonly', true);
        $essential->getElement('musiconhold')->setMultiOptions($section)->setValue($queue['musiconhold']);
        $essential->getElement('timeout')->setValue($queue['timeout']);
        $essential->getElement('announce_frequency')->setValue($queue['announce_frequency']);
        $essential->getElement('retry')->setValue($queue['retry']);
        $essential->getElement('wrapuptime')->setValue($queue['wrapuptime']);
        $essential->getElement('maxlen')->setValue($queue['maxlen']);
        $essential->getElement('servicelevel')->setValue($queue['servicelevel']);
        $essential->getElement('strategy')
                ->addMultiOptions(array('ringall' => $this->view->translate('For all agents available (ringall)'),
                    'roundrobin' => $this->view->translate('Search for a available agent (roundrobin)'),
                    'leastrecent' => $this->view->translate('For the agent idle for the most time (leastrecent)'),
                    'random' => $this->view->translate('Randomly (random)'),
                    'fewestcalls' => $this->view->translate('For the agent that answerd less calls (fewestcalls)'),
                    'rrmemory' => $this->view->translate('Equally (rrmemory)')))
                ->setValue($queue['strategy']);


        $form->addSubForm($essential, "essential");

        $advancedData = new Zend_Config_Xml('./modules/default/forms/queues.xml', 'advanced', true);
        $advanced = new Snep_Form_SubForm($this->view->translate("Advanced Configuration"), $advancedData);

        $boolOptions = array(1 => $this->view->translate('Yes'),
                             0 => $this->view->translate('No'));

        $advanced->getElement('announce')->setMultiOptions($sounds)->setValue($queue['announce']);
        $advanced->getElement('context')->setValue($queue['context']);
        $advanced->getElement('queue_youarenext')->setMultiOptions($sounds)->setValue($queue['queue_youarenext']);
        $advanced->getElement('queue_thereare')->setMultiOptions($sounds)->setValue($queue['queue_thereare']);
        $advanced->getElement('queue_callswaiting')->setMultiOptions($sounds)->setValue($queue['queue_callswaiting']);
        $advanced->getElement('queue_thankyou')->setMultiOptions($sounds)->setValue($queue['queue_thankyou']);
        $advanced->getElement('joinempty')
                ->setMultiOptions(array('yes' => $this->view->translate('Yes'),
                    'no' => $this->view->translate('No'),
                    'strict' => $this->view->translate('Restrict')))
                ->setValue($queue['joinempty']);
        $advanced->getElement('leavewhenempty')->setMultiOptions($boolOptions)->setValue($queue['leavewhenempty']);
        $advanced->getElement('reportholdtime')->setMultiOptions($boolOptions)->setValue($queue['reportholdtime']);
        $advanced->getElement('memberdelay')->setValue($queue['memberdelay']);
        $advanced->getElement('weight')->setValue($queue['weight']);


        $advanced->getElement('autopause')->setValue($queue['autopause']);
        $advanced->getElement('autofill')->setValue($queue['autofill']);

        $id_queue = new Zend_Form_Element_Hidden('id_queue');
        $id_queue->setValue($queue['id_queue']);
        $advanced->addElement($id_queue);

        $form->addSubForm($advanced, "advanced");


        if ($this->_request->getPost()) {

            $dados = array('name' =>        $_POST['essential']['name'],
                           'musiconhold' => $_POST['essential']['musiconhold'],
                           'announce' =>    $_POST['advanced']['announce'],
                           'context' =>     $_POST['advanced']['context'],
                           'timeout' =>     $_POST['essential']['timeout'],
                           'queue_youarenext' =>    $_POST['advanced']['queue_youarenext'],
                           'queue_thereare' =>      $_POST['advanced']['queue_thereare'],
                           'queue_callswaiting' =>  $_POST['advanced']['queue_callswaiting'],
                           'queue_thankyou' =>      $_POST['advanced']['queue_thankyou'],
                           'announce_frequency' =>  $_POST['essential']['announce_frequency'],
                           'announce_round_seconds' => 0,
                           'retry' =>        $_POST['essential']['retry'],
                           'wrapuptime' =>   $_POST['essential']['wrapuptime'],
                           'maxlen' =>       $_POST['essential']['maxlen'],
                           'servicelevel' => $_POST['essential']['servicelevel'],
                           'strategy' =>     $_POST['essential']['strategy'],
                           'joinempty' =>    $_POST['advanced']['joinempty'],
                           'leavewhenempty' => $_POST['advanced']['leavewhenempty'],
                           'reportholdtime' => $_POST['advanced']['reportholdtime'],
                           'memberdelay' =>    $_POST['advanced']['memberdelay'],
                           'weight' =>         $_POST['advanced']['weight'],
                           'autopause' =>      $_POST['advanced']['autopause'],
                           'autofill' =>       $_POST['advanced']['autofill'],
                           'monitor_join' => NULL,
                           'monitor_format' => NULL,
                           'queue_holdtime' => NULL,
                           'queue_minutes' => NULL,
                           'queue_seconds' => NULL,
                           'queue_lessthan' => NULL,
                           'queue_reporthold' => NULL,
                           'announce_holdtime' => NULL,
                           'eventmemberstatus' => NULL,
                           'eventwhencalled' => NULL,
                           'timeoutrestart' => NULL,
                           'queue_name' => NULL,
                           'interface' => NULL);


            $form_isValid = $form->isValid($_POST);

            if ($form_isValid) {

                $obj = new Snep_Queues_Manager();
                $obj->update($dados, "id_queue = {$_POST['advanced']['id_queue']}");
                $this->_redirect($this->getRequest()->getControllerName());
            }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a queue
     */
    public function removeAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Queues"),
            $this->view->translate("Delete")
        ));

        $id = $this->_request->getParam('id');

        $obj = new Snep_Queues_Manager();
        $obj->delete("id_queue = $id");
        
        
        $this->_redirect($this->getRequest()->getControllerName());
    }

    /**
     * Set member queue
     * 
     */
    public function membersAction() {

        $queue = $this->_request->getParam("id");
        
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Queues"),
            $this->view->translate("Members $queue")
        ));

        $members = Snep_Queues_Manager::getMembers($queue);
        $mem = array();
        foreach ($members as $m) {
            $mem[$m['interface']] = $m['interface'];
        }

        $_allMembers = Snep_Queues_Manager::getAllMembers();
        $notMem = array();
        foreach ($_allMembers as $row) {
            $cd = explode(";", $row['canal']);
            foreach ($cd as $canal) {
                if (strlen($canal) > 0) {
                    if (!array_key_exists($canal, $mem)) {
                        $notMem[$canal] = $row['callerid'] . " ($canal)({$row['group']})";
                    }
                }
            }
        }

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form();

        $this->view->objSelectBox = 'members';
        $form->setSelectBox($this->view->objSelectBox, $this->view->translate("Add Member"), $notMem, $mem);

        $queueId = new Zend_Form_Element_hidden('id');
        $queueId->setvalue($queue);
        $form->addElement($queueId);

        $this->view->form = $form;

        if ($this->_request->getPost()) {
            Snep_Queues_Manager::removeAllMembers($queue);

            if (isset($_POST['box_add'])) {
                foreach ($_POST['box_add'] as $add) {
                    Snep_Queues_Manager::insertMember($queue, $add);
                }
            }

            $this->_redirect($this->getRequest()->getControllerName() . '/');
        }
    }

    /**
     * Depracated method
     * PALEATIVOS para adaptação da interface.     *
     */
    public function cidadeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $estado = isset($_POST['uf']) && $_POST['uf'] != "" ? $_POST['uf'] : display_error($LANG['msg_nostate'], true);
        $municipios = Snep_Cnl::get($estado);

        $options = '';
        if (count($municipios > 0)) {
            foreach ($municipios as $cidades) {
                $options .= "<option  value='{$cidades['municipio']}' > {$cidades['municipio']} </option> ";
            }
        } else {
            $options = "<option> {$LANG['select']} </option>";
        }

        echo $options;
    }

}