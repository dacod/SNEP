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
 * Controller for extension management
 */
class ExtensionsController extends Zend_Controller_Action {

    protected $form;

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Manage » Extensions");

        $db = Zend_Registry::get('db');
        $select = $db->select()->from("peers", array(
                    "id" => "id",
                    "exten" => "name",
                    "name" => "callerid",
                    "channel" => "canal",
                    "group"
                ));
        $select->where("peer_type='R'");

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );

        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->extensions = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "/snep/index.php/extensions/index/";

        $options = array("name" => $this->view->translate("Extension"),
            "callerid" => $this->view->translate("Name"),
            "group" => $this->view->translate("Group")
        );

        $baseUrl = $this->getFrontController()->getBaseUrl();

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($baseUrl . '/extensions/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($options);
        $filter->setFieldValue($this->_request->getParam('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "/snep/src/extensions.php?action=multiadd",
                "display" => $this->view->translate("Add Multiple Extensions"),
                "css" => "includes"),
            array("url" => $baseUrl . "/extensions/add",
                "display" => $this->view->translate("Add Extension"),
                "css" => "include"));
    }

    public function addAction() {
        $this->view->breadcrumb = $this->view->translate("Manage » Extensions » Add Extension");
        $this->view->form = $this->getForm();
        $this->renderScript("extensions/add_edit.phtml");
    }

    public function multiAddAction() {
        $this->__redirect("./ramais_varios.php");
    }

    /**
     * Redireciona a edição do ramal para a tela antiga de edição.
     *
     * A remoção da tela antiga se dará no momento que o formulário de edição
     * e adição de ramais for simplificado.
     *
     * @TODO usar Zend_Form para criação do formulário de cadastro e ediçao de
     * ramais.
     */
    public function editAction() {

        $exten = isset($_GET['id']) ? $_GET['id'] : null;

        // Código legado espera id da table peers e não numero do ramal.
        $db = Zend_Registry::get("db");
        $select = $db->select()->from("peers", array("id"))->where("name='$exten' AND peer_type='R'");
        $result = $select->query()->fetchObject();

        $this->__redirect("./ramais.php?acao=alterar&id=$result->id");
    }

    public function deleteAction() {

        $LANG = Zend_Registry::get('lang');
        $db = Zend_Registry::get('db');

        $id = isset($_GET['id']) ? $_GET['id'] : false;
        if (!$id) {
            display_error($LANG['msg_notselect'], true);
            exit;
        }

        // Fazendo procura por referencia a esse ramal em regras de negócio.
        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%R:$id%' OR destino LIKE '%R:$id%'";
        $regras = $db->query($rules_query)->fetchAll();

        $rules_query = "SELECT rule.id, rule.desc FROM regras_negocio as rule, regras_negocio_actions_config as rconf WHERE (rconf.regra_id = rule.id AND rconf.value = '$id')";
        $regras = array_merge($regras, $db->query($rules_query)->fetchAll());

        if (count($regras) > 0) {
            $msg = $LANG['extension_conflict_in_rules'] . ":<br />\n";
            foreach ($regras as $regra) {
                $msg .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }
            display_error($msg, true);
            exit(1);
        }
        $sql = "DELETE FROM peers WHERE name='" . $id . "'";

        $db->beginTransaction();

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $sql = "delete from voicemail_users where customer_id='$id'";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        try {
            $db->commit();
            grava_conf();
        } catch (PDOException $e) {
            $db->rollBack();
            display_error($LANG['error'] . $e->getMessage(), true);
        }

        $this->_redirect("default/extensions");
    }

    protected function getForm() {
        if ($this->form === Null) {
            $form_xml = new Zend_Config_Xml(Zend_Registry::get("config")->system->path->base . "/default/forms/extensions.xml");
            $form = new Snep_Form();
            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Extension"), $form_xml->extension), "extension");
            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Interface Technology"), $form_xml->technology), "technology");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->ip, "sip"), "sip");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->ip, "iax2"), "iax2");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->manual, "manual"), "manual");
            $form->addSubForm(new Snep_Form_SubForm(null, $form_xml->virtual, "virtual"), "virtual");
            $form->addSubForm(new Snep_Form_SubForm($this->view->translate("Advanced"), $form_xml->advanced), "advanced");
            $this->form = $form;
        }

        return $this->form;
    }

}
