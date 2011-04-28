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
class TrunksController extends Zend_Controller_Action {

    protected $form;
    protected $boardData;

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Troncos");
        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $select =   "SELECT t.id, t.callerid, t.name, t.type, t.trunktype, t.time_chargeby, t.time_total,
                            (
                                SELECT th.used
                                FROM time_history AS th
                                WHERE th.owner = t.id AND th.owner_type='T'
                                ORDER BY th.changed DESC limit 1
                            ) as used,
                            (
                                SELECT th.changed
                                FROM time_history AS th
                                WHERE th.owner = t.id AND th.owner_type='T'
                                ORDER BY th.changed DESC limit 1
                            ) as changed
                     FROM trunks as t ";
        
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $datasql = $db->query($select);
        $trunks = $datasql->fetchAll();

        foreach ($trunks as $id => $val) {

            $trunks[$id]['saldo'] = null;

            if( ! is_null($val['time_total']) ) {
                $ligacao = $val['changed'];
                $anoLigacao = substr($ligacao,6,4);
                $mesLigacao = substr($ligacao,3,2);
                $diaLigacao = substr($ligacao,0,2);

                switch ($val['time_chargeby']) {
                    case 'Y':
                        if ($anoLigacao == date('Y')) {
                            $saldo = $val['time_total'] - $val['used'];
                            if ($val['used'] >= $val['time_total']) {
                                $saldo = 0;
                            }
                        } else {                            
                            $saldo = $val['time_total'];                            
                        }
                        break;
                    case 'M':
                        if ($anoLigacao == date('Y') && $mesLigacao == date('m')) {
                            $saldo = $val['time_total'] - $val['used'];
                            if ($val['used'] >= $val['time_total']) {
                                $saldo = 0;
                            }
                        } else {
                            $saldo = $val['time_total'];
                        }
                        break;
                    case 'D':
                        if ($anoLigacao == date('Y') && $mesLigacao == date('m') && $diaLigacao == date('d')) {
                            $saldo = $val['time_total'] - $val['used'];
                        } else {
                            $saldo = $val['time_total'];
                        }
                        break;
                }
                $trunks[$id]['saldo'] = $saldo; //sprintf("%d:%02d",floor($saldo/60), $saldo%60);
            }
        }

        $paginatorAdapter = new Zend_Paginator_Adapter_Array($trunks);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->trunks = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("name" => $this->view->translate("Código"),
                        "callerid" => $this->view->translate("Nome") );

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Incluir Tronco"),
                                          "css"     => "include"));


    }

    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Troncos » Cadastro");

        $form = $this->getForm();

        if ($this->getRequest()->isPost()) {

            if ($this->form->isValid($_POST)) {
                $postData = $this->_request->getParams();
                $ret = $this->execAdd($postData);
                
                if (!is_string($ret)) {
                    $this->_redirect('/trunks/');
                } else {
                    $this->view->error = $ret;
                    $this->view->form->valid(false);
                }
            }
        }
        
        $this->view->form = $form;
        $this->renderScript("trunks/add_edit.phtml");
    }

    public function editAction() {

        $id = $this->_request->getParam("id");
        $this->view->breadcrumb = $this->view->translate("Manage » Trunks » Edit » $id");

        $form = $this->getForm();
        if(!$this->view->all_writable) {
         //   $form->getElement("submit")->setAttrib("disabled", "disabled");
        }
        $this->view->form = $form;
        $this->view->boardData = $this->boardData;

        $this->view->url = $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        
        $select = $db->select()
                     ->from("trunks")        
                     ->where("trunks.id = '{$id}'");
        $stmt = $db->query($select);
        $trunk = $stmt->fetch();

        $select = $db->select()
                     ->from("peers")
                     ->where("peers.name = '{$trunk['name']}'");
        $stmt = $db->query($select);
        $peer = $stmt->fetch();

        if($peer && $trunk) {
            $trunk = array_merge( $peer, $trunk );
        }

        echo "<pre>";
        print_r( $trunk );

        switch ($trunk['type']) {
            case 'SIP':
                $form_sip = $form->getSubForm('sip');
                $form_sip->getElement('dial_method')
                         ->setValue( strtolower( $trunk['method'] ) );
                $form_sip->getElement('username')
                         ->setValue( $trunk['username'] );
                $form_sip->getElement('secret')
                         ->setValue( $trunk['secret'] );
                $form_sip->getElement('host_trunk')
                         ->setValue( $trunk['host'] );
                $form_sip->getElement('fromuser')
                         ->setValue( $trunk['fromuser'] );
                $form_sip->getElement('fromdomain')
                         ->setValue( $trunk['fromdomain'] );
                $form_sip->getElement('fromdomain')
                         ->setValue( $trunk['fromdomain'] );
                $form_sip->getElement('dtmfmode')
                         ->setValue( $trunk['dtmf_mode'] );
                $form_sip->getElement('qualify')
                         ->setValue( $trunk['qualify'] );
                if($trunk['qualify'] == 'spacify') {
                    $form_sip->getElement('qualify_value')
                             ->setValue( $trunk['qualify_value'] );
                }
                $form_sip->getElement('peer_type')
                         ->setValue( $trunk['type'] );
                $form_sip->getElement('reverseAuth')
                         ->setValue( $trunk['reverse_auth'] );
                $form_sip->getElement('nat')
                         ->setValue( $trunk['nat'] );
                $form_sip->getElement('domain')
                         ->setValue( $trunk['domain'] );
                $form_sip->getElement('insecure')
                         ->setValue( $trunk['insecure'] );
                $form_sip->getElement('port')
                         ->setValue( $trunk['port'] );
                break;
            case 'IAX2':
                $form_iax2 = $form->getSubForm('iax2');
                $form_iax2->getElement('dial_method')
                         ->setValue( strtolower( $trunk['method'] ) );
                $form_iax2->getElement('username')
                         ->setValue( $trunk['username'] );
                $form_iax2->getElement('secret')
                         ->setValue( $trunk['secret'] );
                $form_iax2->getElement('host_trunk')
                         ->setValue( $trunk['host'] );
                $form_iax2->getElement('fromuser')
                         ->setValue( $trunk['fromuser'] );
                $form_iax2->getElement('fromdomain')
                         ->setValue( $trunk['fromdomain'] );
                $form_iax2->getElement('fromdomain')
                         ->setValue( $trunk['fromdomain'] );
                $form_iax2->getElement('dtmfmode')
                         ->setValue( $trunk['dtmf_mode'] );
                $form_iax2->getElement('qualify')
                         ->setValue( $trunk['qualify'] );
                if($trunk['qualify'] == 'spacify') {
                    $form_iax2->getElement('qualify_value')
                              ->setValue( $trunk['qualify_value'] );
                }
                $form_iax2->getElement('peer_type')
                         ->setValue( $trunk['type'] );
                $form_iax2->getElement('reverseAuth')
                         ->setValue( $trunk['reverse_auth'] );
                $form_iax2->getElement('nat')
                         ->setValue( $trunk['nat'] );
                $form_iax2->getElement('domain')
                         ->setValue( $trunk['domain'] );
                $form_iax2->getElement('insecure')
                         ->setValue( $trunk['insecure'] );
                $form_iax2->getElement('port')
                         ->setValue( $trunk['port'] );
                $form_iax2->getElement('istrunk')
                          ->setValue( $trunk['trunk'] );
                break;
            case 'KHOMP':

                break;
            case 'SNEPSIP':
                $form_snepsip = $form->getSubForm('snep_sip');
                $form_snepsip->getElement('snep_host')
                             ->setValue($trunk['host']);
                $form_snepsip->getElement('snep_dtmf')
                             ->setValue($trunk['dtmfmode']);
                break;
            case 'SNEPIAX2':
                $form_snepiax2 = $form->getSubForm('snep_iax2');
                $form_snepiax2->getElement('snep_username')
                              ->setValue($trunk['username']);
                $form_snepiax2->getElement('snep_nat')
                              ->setValue($trunk['nat']);
                $form_snepiax2->getElement('snep_host')
                              ->setValue($trunk['host']);
                $form_snepiax2->getElement('snep_dtmf')
                              ->setValue($trunk['dtmfmode']);
                break;
            case 'VIRTUAL':
                $form_virtual = $form->getSubForm('virtual');
                $form_virtual->getElement('channel')
                             ->setValue($trunk['channel']);
                $form_virtual->getElement('trunk_regex')
                             ->setValue($trunk['id_regex']);
                break;
        }

        // Fill a advanced subForm
        $form_advanced = $form->getSubForm('advanced');
        $form_advanced->getElement('extensionMapping')
                      ->setValue($trunk['map_extensions']);
        $form_advanced->getElement('dtmf_dial')
                      ->setValue($trunk['dtmf_dial']);
        if($trunk['dtmf_dial']) {
            $form_advanced->getElement('dtmf_dial_number')
                          ->setValue($trunk['dtmf_dial_number']);
        }
        if($trunk['time_total']) {
            $form_advanced->getElement('tempo')
                          ->setValue(1);
            $form_advanced->getElement('time_total')
                    ->setValue($trunk['time_total']);
            $form_advanced->getElement('time_chargeby')
                    ->setValue($trunk['time_chargeby']);
        }else{
            $form_advanced->getElement('tempo')
                          ->setValue(0);
        }
        $form->getSubForm('trunks')
             ->getElement('name')
             ->setValue($trunk['callerid'])
             ->setAttrib('readonly', true)
             ->setAttrib('disabled', true);
        $form->getSubForm('technology')
             ->getElement('type')
             ->setValue( strtolower( $trunk['type'] ) );


        if ($this->getRequest()->isPost()) {

            if ($this->form->isValid($_POST)) {

                $postData = $this->_request->getParams();
                $ret = $this->execAdd($postData, true);

                if (!is_string($ret)) {
                    $this->_redirect('/trunks/');
                } else {
                    $this->view->error = $ret;
                    $this->view->form->valid(false);
                }
            }
        }
        
        $this->renderScript("trunks/add_edit.phtml");
    }


    public function removeAction() {

        $db = Zend_Registry::get('db');
        $id = $this->_request->getParam("id");

        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%T:$id,%' OR destino LIKE '%T:$id,%'";
        $regras = $db->query($rules_query)->fetchAll();

        $rules_query = "SELECT rule.id, rule.desc FROM regras_negocio as rule, regras_negocio_actions_config as rconf WHERE (rconf.regra_id = rule.id AND rconf.value = '$id' AND (rconf.key = 'tronco' OR rconf.key = 'trunk'))";
        foreach ($db->query($rules_query)->fetchAll() as $rule) {
            if(!in_array($rule, $regras)) {
                $regras[] = $rule;
            }
        }

        if(count($regras) > 0) {

            $this->view->error = $this->view->translate("As seguintes Rotas fazem uso deste tronco, modifique entes de excluir: ") . "<br />";
            foreach ($regras as $regra) {
                $this->view->error .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }

            $this->_helper->viewRenderer('error');
        }else{

            try{
                $sql = "DELETE FROM trunks WHERE id='$id'" ;
                $db->beginTransaction() ;
                $db->exec($sql);

                $sql = "DELETE FROM peers WHERE name='$name'" ;
                $db->exec($sql);
                $db->commit();

                Snep_InterfaceConf::loadConfFromDb();

                $this->_redirect("default/trunks");


            }catch(Exception $e) {
                echo "erro";

            }
        }
        
    }

    protected function getForm() {

        $this->form = null;

        if ($this->form === Null) {

            $form_xml = new Zend_Config_Xml(Zend_Registry::get("config")->system->path->base . "/default/forms/trunks.xml");

            $form = new Snep_Form();
            $form->addSubForm( new Snep_Form_SubForm( null, $form_xml->trunks ), "trunks");
            $form->addSubForm( new Snep_Form_SubForm( null, $form_xml->technology ), "technology");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->ip, "sip"), "sip");

            $ip = new Snep_Form_SubForm(null, $form_xml->ip, "iax2");
            $iax = new Snep_Form_SubForm(null, $form_xml->iax2, "iax2");
            foreach($iax as $_iax) {
                $ip->addElement($_iax);
            }            
            $form->addSubForm( $ip, "iax2");

            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->snep_sip, "snep_sip"), "snep_sip");

            $snep_sip = new Snep_Form_SubForm(null, $form_xml->snep_sip, 'snep_iax2');
            $snep_iax = new Snep_Form_SubForm(null, $form_xml->snep_iax2, "snep_iax2");
            foreach($snep_sip as $_snep_sip) {
                $snep_iax->addElement($_snep_sip);
            }
            $form->addSubForm($snep_iax, "snep_iax2");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->virtual, "virtual"), "virtual");

           // $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Opções Avançadas"), $form_xml->advanced, 'advanced'), 'advanced');

            
            $subFormKhomp = new Snep_Form_SubForm(null, $form_xml->khomp, "khomp");
            $selectFill = $subFormKhomp->getElement('board');
            $selectFill->addMultiOption(null, ' ');


            // Monta informações para placas khomp
            $boardList = array();

            try {
                $khompInfo = new PBX_Khomp_Info();
            } catch (Asterisk_Exception_CantConnect $ex) {
                Zend_Debug::Dump($ex->getMessage());
                exit;
            }

            if ($khompInfo->hasWorkingBoards()) {
                foreach ($khompInfo->boardInfo() as $board) {
                    if (preg_match("/KFXS/", $board['model'])) {
                        $channels = range(0, $board['channels']);
                        $selectFill->addMultiOption($board['id'], $board['id']);
                        $boardList[$board['id']] = $channels;
                    }
                }
            } else {
                $subFormKhomp->removeElement('board');
                $subFormKhomp->removeElement('channel');
                $noKhompText = new Zend_Form_Element_Hidden('textKhomp');
                $noKhompText->setDescription($this->view->translate('Você não possui placas da Khomp.'));
                $subFormKhomp->addElement($noKhompText);
            }
 
            $form->addSubForm($subFormKhomp, "khomp");
            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Advanced"), $form_xml->advanced), "advanced");
            
 
            $this->form = $form;
        }

        return $this->form;
    }


    protected function execAdd($trunk, $update = false) {

        $def_campos_troncos = array("accountcode" => "''", "amaflags" => "''", 
                                    "defaultip"   => "''", "language" => "'pt_BR'",
                                    "deny"        => "''", "permit"   => "''",
                                    "mask"        => "''", "restrictcid" => "''",
                                    "rtptimeout"  => "''", "rtpholdtimeout" => "''",
                                    "musiconhold" => "'cliente'", "regseconds" => 0,
                                    "ipaddr"      => "''", "regexten" => "''",
                                    "cancallforward" => "'yes'", "setvar" => "''",
                                    "disallow"    => "'all'", "mailbox" => "''",
                                    "email"       => "''", "vinculo" => "''",
                                    "incominglimit" => 0, "outgoinglimit" => 0,
                                    "usa_vc"=>"'no'","canreinvite" => "'no'",
                                    "mailbox"=>"''","fullcontact"=>"''",
                                    "authenticate"=>"''", "subscribecontext"=>"''",
                                    "incominglimit"=>0,"outgoinglimit"=>0,
                                    "usa_vc"=>"'no'", "email"=>"''",
                                    "vinculo"=>"''");

        
        $db = Zend_Registry::get('db');
/*      if($trunk['technology']['type'] == "snep_sip" ||
           $trunk['technology']['type'] == "snep_iax2") {

           $nat = "no";
        } */

        if ($trunk['advanced']['tempo'] == 1) {
            $time_chargeby = $trunk['advanced']['time_total'] > 0 ? "'{$trunk['advanced']['time_chargeby']}'": "NULL";
            $time_total = $trunk['advanced']['time_total'] * 60;
            $time_total = $trunk['advanced']['time_total'] == 0? "NULL": "'{$trunk['advanced']['time_total']}'";
        } else {
            $time_chargeby = "NULL";
            $time_total = "NULL";
        }

        try {
            $sql = "SELECT name FROM trunks " ;
            $sql.= " ORDER BY CAST(name as DECIMAL) DESC LIMIT 1" ;
            $row = $db->query($sql)->fetch();
        } catch (PDOException $e) {
            display_error($LANG['error'].$e->getMessage(),true) ;            
        }

        $name = trim($row['name'] + 1) ;
        $type = $trunk['technology']['type'];

        if ($trunk['technology']['type'] == "sip"  ||
            $trunk['technology']['type'] == "iax2")  {

            $peer_type = "peer";
            $tech = trim( $trunk['technology']['type'] );
            $fromdomain = $trunk[$tech]['fromdomain'];
            $fromuser = $trunk[$tech]['fromuser'];

            if($trunk[$tech]['dial_method'] == 'noauth') {
                $host_trunk = $trunk[$tech]['host_trunk'];
                $channel = $trunk['technology']['type'] . "/@" . $trunk[$tech]['host_trunk'];
            }
            else {
                $channel= $trunk['technology']['type'] . "/" . $trunk[$tech]['username'];
            }

            $dmfmode = $trunk[$tech]['dtmfmode'];
            $domain = ( is_null(trim($trunk[$tech]['domain'])) ? null : $trunk[$tech]['domain']); //trunk
            $insecure = ( is_null(trim($trunk[$tech]['insecure'])) ? null : $trunk[$tech]['insecure']); //trunk
            $calllimit = ( is_null(trim($trunk[$tech]['calllimit'])) ? null : $trunk[$tech]['calllimit']); //peer
            $port = ( is_null(trim($trunk[$tech]['port'])) ? null : $trunk[$tech]['port']); //peer
            $id_regex = $trunk['technology']['type'] . "/" . $trunk[$tech]['username'];
            $dtmfmode = $snep_dtmf;
            $secret = $trunk[$tech]['secret'];
            $username = $trunk[$tech]['username'];
            $host_trunk = $trunk[$tech]['host_trunk'];
            $dialmethod = $trunk[$tech]['dial_method'];
            $reverseAuth = $trunk[$tech]['reverseAuth'] ? "True": "False";

            if( $trunk[$tech]['qualify'] ) {
                if( $trunk[$tech]['qualify']  == 'specify' ) {
                    $qualify = trim( $trunk[$tech]['qualify_value'] );
                }else{
                    $qualify = $trunk[$tech]['qualify'];
                }
            }
            
            if( $tech  == "iax2" ) {
                $istrunk = $trunk[$tech]['istrunk'];
            }else{
                $istrunk = 'yes';
            }

            $sql_fields_default = "";
            $sql_values_default = "";

            if( $fromdomain != "" ) {
                $sql_fields_default = ",fromdomain";
                $sql_values_default = ",'$fromdomain'";
            }
            if( $fromuser != "" ) {
                $sql_fields_default = ",fromuser";
                $sql_values_default = ",'$fromuser'";
            }
            if( $trunk[$tech]['nat'] == 1 ) {
                $nat = 'yes';
            }else{
                $nat = 'no';
            }

            foreach( $def_campos_troncos as $key => $value ) {
                $sql_fields_default .= ",$key";
                $sql_values_default .= ",$value";
            }
            $trunktype = "I";
        }
        else if( $trunk['technology']['type'] == "snep_sip" ) {
            $trunktype  = 'SIP';
            $peer_type  = "peer";
            $type       = "peer";
            $username   = $trunk['snep_sip']['snep_host'];
            $host_trunk = $trunk['snep_sip']['snep_host'];
            $channel    = $trunk['technology']['type'] . "/" . $trunk['snep_sip']['snep_host'];
            $id_regex   = $trunk['technology']['type'] . "/" . $trunk['snep_sip']['snep_host'];
            $nat = "no";
            $dtmfmode = $snep_dtmf;
            $trunktype  = "I";
        }
        else if( $trunk['technology']['type'] == "snep_iax2" ) {
            $trunktype  = 'IAX2';
            $peer_type  = "peer";
            $type       = "peer";
            $username   = $trunk['snep_iax2']['snep_username'];
            $host_trunk = $trunk['snep_iax2']['snep_host'];
            $channel    = $trunk['technology']['type'] . "/" . $trunk['snep_iax2']['snep_username'];
            $id_regex   = $trunk['technology']['type'] . "/" . $trunk['snep_iax2']['snep_username'];
            $nat = "no";
            $dtmfmode = $snep_dtmf;
            $trunktype  = "I";
        }
        else if($trunk['technology']['type'] == "khomp") {

            $channel= 'KHOMP/' . $trunk['khomp']['khomp_board'];
            $b = substr($trunk['khomp']['khomp_board'], 1, 1);
            if(substr($trunk['khomp']['khomp_board'], 2, 1) == 'c') {
                $config = array(
                        "board" => $b,
                        "channel" => substr($trunk['khomp']['khomp_board'], 3)
                );
            }
            else if( substr($trunk['khomp']['khomp_board'], 2, 1) == 'l' ) {
                $config = array(
                        "board" => $b,
                        "link" => substr($trunk['khomp']['khomp_board'], 3)
                );
            }
            else {
                $config = array(
                        "board" => $b
                );
            }
            $_trunk = new PBX_Asterisk_Interface_KHOMP($config);
            $id_regex = $_trunk->getIncomingChannel();
            $trunktype = "T";
        }
        else { // VIRTUAL
            $trunktype = "T";
            $id_regex = $trunk['virtual']['trunk_regex'] == "" ? $channel : $trunk['virtual']['trunk_regex'];
            $domain = null;
            $secret = null;
            $username = null;
            $channel = null;
            $dialmethod = null;
            $reverseAuth = 0;
            $insecure = null;
            $dtmfmode = null;
        }

        $dtmf_dial = $trunk['advanced']['dtmf_dial'] ? 'TRUE' : 'FALSE';
        $context = "default";
        $extensionMapping = $trunk['advanced']['extensionMapping'] ? 'True': 'False';

        /*
        if($trunk['sip']['reverseAuth']) {
            $reverseAuth = $trunk['sip']['reverseAuth'] ? "True": "False";
        }elseif($trunk['iax2']['reverseAuth']){
            $reverseAuth = $trunk['sip']['reverseAuth'] ? "True": "False";
        }
         * 
         */

        $allow = "xxx;sss;www;eee";
        $callerid = $trunk['trunks']['name'];
        
        if($trunk['advanced']['dtmf_dial']) {
            $dtmf_dial_number = $trunk['advanced']['dtmf_dial_number'];
        }else{
            $dtmf_dial_number = null;
        }

        try {
            $db->beginTransaction();            
            $sql = "INSERT INTO trunks (" ;
            $sql.= "name, type, callerid, context, dtmfmode, insecure, domain, secret,id_regex,";
            $sql.= "username, allow, channel, trunktype, host, trunk_redund, time_total,";
            $sql.= "time_chargeby, dialmethod, map_extensions, reverse_auth, dtmf_dial, dtmf_dial_number) values (";
            $sql.= "'$name','$type','$callerid','$context','$dtmfmode','$insecure', '$domain', ";
            $sql.= "'$secret','$id_regex','$username','$allow','$channel','$trunktype'," ;
            $sql.= "'$host_trunk','', $time_total, $time_chargeby, '$dialmethod',";
            $sql.= "$extensionMapping, $reverseAuth, $dtmf_dial, '$dtmf_dial_number')" ;

            $db->exec($sql) ;
            
            if ($trunktype == "I") {
                $sql = "INSERT INTO peers (" ;
                $sql.= "name,callerid,context,secret,type,allow,username,";
                $sql.= "dtmfmode,canal,host,peer_type, trunk, qualify, nat,`call-limit`,port ". $sql_fields_default ;
                $sql.= ") values (";
                $sql.=  "'$name','$callerid','$context','$secret','$peer_type','$allow',";
                $sql.= "'$username','$dtmfmode','$channel','$host_trunk', 'T', '$istrunk', '$qualify', '$nat' ";
                $sql.= ",'$calllimit', '$port' ". $sql_values_default.")" ;

                $db->exec($sql) ;
            }
            
            $db->commit();

            

        } catch (Exception $ex) {

            $db->rollBack();
        }


        Snep_InterfaceConf::loadConfFromDb();

    }



}
