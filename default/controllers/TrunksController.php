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
 * Trunk management
 */
class TrunksController extends Zend_Controller_Action {

    protected $form;
    protected $boardData;

    public function indexAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Trunks")
                ));

        $this->view->url = $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $select = "SELECT t.id, t.callerid, t.name, t.type, t.trunktype, t.time_chargeby, t.time_total,
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

            if (!is_null($val['time_total'])) {
                $ligacao = $val['changed'];
                $anoLigacao = substr($ligacao, 6, 4);
                $mesLigacao = substr($ligacao, 3, 2);
                $diaLigacao = substr($ligacao, 0, 2);

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
                $trunks[$id]['saldo'] = $saldo;
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
            "callerid" => $this->view->translate("Nome"));

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                "display" => $this->view->translate("Incluir Tronco"),
                "css" => "include"));
    }

    /**
     * @return Snep_Form
     */
    protected function getForm() {

        $this->form = null;

        if ($this->form === Null) {

            $form_xml = new Zend_Config_Xml(Zend_Registry::get("config")->system->path->base . "/default/forms/trunks.xml");

            $form = new Snep_Form();
            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Trunk"), $form_xml->trunks), "trunks");
            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Interface Technology"), $form_xml->technology), "technology");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->ip, "sip"), "sip");

            $ip = new Snep_Form_SubForm(null, $form_xml->ip, "iax2");
            $iax = new Snep_Form_SubForm(null, $form_xml->iax2, "iax2");
            foreach ($iax as $_iax) {
                $ip->addElement($_iax);
            }
            $form->addSubForm($ip, "iax2");

            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->snepsip, "snepsip"), "snepsip");

            $snepsip = new Snep_Form_SubForm(null, $form_xml->snepsip, 'snepiax2');
            $snep_iax = new Snep_Form_SubForm(null, $form_xml->snepiax2, "snepiax2");
            foreach ($snepsip as $_snepsip) {
                $snep_iax->addElement($_snepsip);
            }
            $form->addSubForm($snep_iax, "snepiax2");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->virtual, "virtual"), "virtual");

            $subFormKhomp = new Snep_Form_SubForm(null, $form_xml->khomp, "khomp");
            // Informações de placas khomp
            $khomp_info = new PBX_Khomp_Info();
            $khomp_boards = array();
            if ($khomp_info->hasWorkingBoards()) {
                foreach ($khomp_info->boardInfo() as $board) {
                    if (!preg_match("/FXS/", $board['model'])) {
                        $khomp_boards["b" . $board['id']] = "{$board['id']} - " . $this->view->translate("Board") . " {$board['model']}";
                        $id = "b" . $board['id'];
                        if (preg_match("/E1/", $board['model'])) {
                            for ($i = 0; $i < $board['links']; $i++)
                                $khomp_boards["b" . $board['id'] . "l$i"] = $board['model'] . " - " . $this->view->translate("Link") . " $i";
                        } else {
                            for ($i = 0; $i < $board['channels']; $i++)
                                $khomp_boards["b" . $board['id'] . "c$i"] = $board['model'] . " - " . $this->view->translate("Channel") . " $i";
                        }
                    }
                }
                $subFormKhomp->getElement('board')->setMultiOptions($khomp_boards);
            }
            
            if(count($khomp_boards) == 0) {
                $subFormKhomp->removeElement('board');
                $subFormKhomp->addElement(new Snep_Form_Element_Html("extensions/khomp_error.phtml", "err", false, null, "khomp"));
            }

            $form->addSubForm($subFormKhomp, "khomp");

            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Advanced"), $form_xml->advanced), "advanced");

            $this->form = $form;
        }

        return $this->form;
    }

    protected function preparePost($post = null) {
        $post = $post === null ? $_POST : $post;
        $tech = $post['technology']['type'];
        $trunktype = $post['technology']['type'] = strtoupper($tech);
        $static_sections = array("trunks", "technology", "advanced", $tech);
        $ip_trunks = array("sip", "iax2", "snepsip", "snepiax2");
        $trunk_fields = array(// Only allowed fields for trunks table
            "callerid",
            "type",
            "username",
            "secret",
            "host",
            "dtmfmode",
            "reverse_auth",
            "domain",
            "insecure",
            "map_extensions",
            "dtmf_dial",
            "dtmf_dial_number",
            "time_total",
            "time_chargeby",
            "dialmethod",
            "trunktype",
            "context",
            "name",
            "allow",
            "id_regex",
            "channel"
        );

        $ip_fields = array(// Only allowed fields for peers table
            "name",
            "callerid",
            "context",
            "secret",
            "type",
            "allow",
            "username",
            "dtmfmode",
            "fromdomain",
            "fromuser",
            "canal",
            "host",
            "peer_type",
            "trunk",
            "qualify",
            "nat",
            "call-limit",
            "port"
        );

        $sql = "SELECT name FROM trunks ORDER BY CAST(name as DECIMAL) DESC LIMIT 1";
        $row = Snep_Db::getInstance()->query($sql)->fetch();

        $trunk_data = array(
            "name" => trim($row['name'] + 1),
            "context" => "default",
            "trunktype" => (in_array($tech, $ip_trunks) ? "I" : "T"),
        );

        foreach ($post as $section_name => $section) {
            if (in_array($section_name, $static_sections)) {
                $trunk_data = array_merge($trunk_data, $section);
            }
        }

        if ($trunktype == "SIP" || $trunktype == "IAX2") {
            $trunk_data['dialmethod'] = strtoupper($trunk_data['dialmethod']);

            if ($trunk_data['dialmethod'] == 'NOAUTH') {
                $trunk_data['channel'] = $trunktype . "/@" . $trunk_data['host'];
            } else {
                $trunk_data['channel'] = $trunktype . "/" . $trunk_data['username'];
            }

            $trunk_data['id_regex'] = $trunktype . "/" . $trunk_data['username'];
            $trunk_data['allow'] = trim(sprintf("%s;%s;%s", $trunk_data['codec'], $trunk_data['codec1'], $trunk_data['codec2']), ";");
        } else if ($trunktype == "SNEPSIP" || $trunktype == "SNEPIAX2") {
            $trunk_data['peer_type'] = $trunktype == "SNEPSIP" ? "peer" : "friend";
            $trunk_data['username'] = $trunktype == "SNEPSIP" ? $trunk_data['host'] : $trunk_data['username'];
            $trunk_data['channel'] = $trunk_data['id_regex'] = substr($trunktype, 4) . "/" . $trunk_data['username'];
        } else if ($trunktype == "KHOMP") {
            $khomp_board = $trunk_data['board'];
            $trunk_data['channel'] = 'KHOMP/' . $khomp_board;
            $b = substr($khomp_board, 1, 1);
            if (substr($khomp_board, 2, 1) == 'c') {
                $config = array(
                    "board" => $b,
                    "channel" => substr($khomp_board, 3)
                );
            } else if (substr($khomp_board, 2, 1) == 'l') {
                $config = array(
                    "board" => $b,
                    "link" => substr($khomp_board, 3)
                );
            } else {
                $config = array(
                    "board" => $b
                );
            }
            $trunk = new PBX_Asterisk_Interface_KHOMP($config);
            $trunk_data['id_regex'] = $trunk->getIncomingChannel();
        } else { // VIRTUAL
            $trunk_data['id_regex'] = $trunk_data['id_regex'] == "" ? $trunk_data['channel'] : $trunk_data['id_regex'];
        }

        // Filter data and fields to allowed types
        $ip_data = array(
            "canal" => $trunk_data['channel'],
            "type" => $trunk_data['peer_type'],
        );
        foreach ($trunk_data as $field => $value) {
            if (in_array($field, $ip_fields) && $field != "type") {
                $ip_data[$field] = $value;
            }

            if (!in_array($field, $trunk_fields)) {
                unset($trunk_data[$field]);
            }
        }
        $ip_data["peer_type"] = "T";

        return array("trunk" => $trunk_data, "ip" => $ip_data);
    }

    public function addAction() {
        $this->view->breadcrumb = $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Trunks"),
                    $this->view->translate("Add")
                ));

        $form = $this->getForm();

        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($_POST)) {
                $trunk_data = $this->preparePost();

                $db = Snep_Db::getInstance();
                $db->beginTransaction();
                try {
                    $db->insert("trunks", $trunk_data['trunk']);
                    if ($trunk_data['trunk']['trunktype'] == "I") {
                        $db->insert("peers", $trunk_data['ip']);
                    }
                    $db->commit();
                } catch (Exception $ex) {
                    $db->rollBack();
                    throw $ex;
                }
                Snep_InterfaceConf::loadConfFromDb();
                $this->_redirect("trunks");
            }
        }

        $this->view->form = $form;
        $this->renderScript("trunks/add_edit.phtml");
    }

    protected function populateFromTrunk(Snep_Form $form, $trunk_id) {
        $db = Snep_Db::getInstance();
        $info = $db->query("select * from trunks where id='$trunk_id'")->fetch();
        $form->getSubForm("trunks")->getElement("callerid")->setValue($info['callerid']);
        $form->getSubForm("technology")->getElement("type")->setValue(strtolower($info['type']));

        foreach ($form->getSubForm("advanced")->getElements() as $element) {
            if (key_exists($element->getName(), $info)) {
                $element->setValue($info[$element->getName()]);
            }
        }

        foreach ($form->getSubForm(strtolower($info['type']))->getElements() as $element) {
            if (key_exists($element->getName(), $info)) {
                $element->setValue($info[$element->getName()]);
            }
        }

        if ($info['trunktype'] == "I") {
            $ip_info = $db->query("select * from peers where name='{$info['name']}'")->fetch();
            foreach ($form->getSubForm(strtolower($info['type']))->getElements() as $element) {
                if (key_exists($element->getName(), $ip_info)) {
                    $element->setValue($ip_info[$element->getName()]);
                }
            }
            if ($info['type'] == "SIP" || $info['type'] == "IAX2") {
                $form->getSubForm(strtolower($info['type']))->getElement("dialmethod")->setValue(strtolower($info['dialmethod']));
                $form->getSubForm(strtolower($info['type']))->getElement("peer_type")->setValue($ip_info['type']);
            }
        }
        else if ($info['type'] == "KHOMP") {
            $form->getSubForm(strtolower("KHOMP"))->getElement("board")->setValue(substr($info['channel'], 6));
        }

    }

    public function editAction() {
        $id = mysql_escape_string($this->getRequest()->getParam("trunk"));
        $this->view->breadcrumb = $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Trunks"),
                    $this->view->translate("Edit trunk %s", $id)
                ));

        $form = $this->getForm();
        $form->setAction($this->view->baseUrl() . "/index.php/trunks/edit/trunk/$id");

        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($_POST)) {
                $trunk_data = $this->preparePost();

                $sql = "SELECT name FROM trunks WHERE id='{$id}' LIMIT 1";
                $name_data = Snep_Db::getInstance()->query($sql)->fetch();
                $trunk_data['trunk']['name'] = $trunk_data['ip']['name'] = $name_data['name'];

                $db = Snep_Db::getInstance();
                $db->beginTransaction();
                try {
                    $db->update("trunks", $trunk_data['trunk'], "id='$id'");
                    if ($trunk_data['trunk']['trunktype'] == "I") {
                        $db->update("peers", $trunk_data['ip'], "name='{$trunk_data['trunk']['name']}' and peer_type='T'");
                    }
                    $db->commit();
                } catch (Exception $ex) {
                    $db->rollBack();
                    throw $ex;
                }
                Snep_InterfaceConf::loadConfFromDb();
                $this->_redirect("trunks");
            }
        }

        $this->populateFromTrunk($form, $id);
        $this->view->form = $form;
        $this->renderScript("trunks/add_edit.phtml");
    }

    public function removeAction() {
        $db = Zend_Registry::get('db');
        $id = $this->_request->getParam("id");
        $name = $this->_request->getParam("name");

        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%T:$id,%' OR destino LIKE '%T:$id,%'";
        $regras = $db->query($rules_query)->fetchAll();

        $rules_query = "SELECT rule.id, rule.desc FROM regras_negocio as rule, regras_negocio_actions_config as rconf WHERE (rconf.regra_id = rule.id AND rconf.value = '$id' AND (rconf.key = 'tronco' OR rconf.key = 'trunk'))";
        foreach ($db->query($rules_query)->fetchAll() as $rule) {
            if (!in_array($rule, $regras)) {
                $regras[] = $rule;
            }
        }

        if (count($regras) > 0) {

            $this->view->error = $this->view->translate("As seguintes Rotas fazem uso deste tronco, modifique entes de excluir: ") . "<br />";
            foreach ($regras as $regra) {
                $this->view->error .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }

            $this->_helper->viewRenderer('error');
        } else {
            $db->beginTransaction();
            $sql = "DELETE FROM trunks WHERE id='$id'";
            $db->exec($sql);
            $sql = "DELETE FROM peers WHERE name='$name'";
            $db->exec($sql);
            $db->commit();

            Snep_InterfaceConf::loadConfFromDb();
            $this->_redirect("trunks");
        }
    }

}
