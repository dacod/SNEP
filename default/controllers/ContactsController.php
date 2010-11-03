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
class ContactsController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos");

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from("contacts_names", array("id", "name", "city", "state", "cep", "phone_1", "cell_1"))
                        ->from("contacts_group", array("name as grupo"))
                        ->where("contacts_names.group = contacts_group.id");

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

        $this->view->contacts = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "/snep/index.php/contacts/index/";

        $opcoes = array("id" => $this->view->translate("Código"),
            "grupo" => $this->view->translate("Tipo"),
            "grupo" => $this->view->translate("Nome"),
            "city" => $this->view->translate("Cidade"),
            "state" => $this->view->translate("Estado"),
            "cep" => $this->view->translate("CEP"),
            "phone_1" => $this->view->translate("Telefone"),
            "cell_1" => $this->view->translate("Celular"));

        // Formulário de filtro.
        $config_file = "./default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);

        $form = new Zend_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');

        $filter = new Zend_Form($config->filter);

        $filter_value = $filter->getElement('filtro');
        $filter_value->setValue($this->_request->getPost('filtro'));

        $submit = new Zend_Form_Element_Submit("submit", array("label" => $this->view->translate("Procurar")));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator('HtmlTag', array('tag' => 'dd'));

        // Botão Lista Completa
        $reset = new Zend_Form_Element_Button("buttom", array("label" => $this->view->translate("Lista Completa")));
        $reset->setAttrib("onclick", "location.href='{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page'");
        $reset->removeDecorator('DtDdWrapper');
        $reset->addDecorator('HtmlTag', array('tag' => 'dd'));

        // Define elementos do 'campo' select
        $campo = $filter->getElement('campo');
        $campo->setMultiOptions($opcoes);

        $filtro = $filter->getElement('filtro');
        $filtro->setValue($this->_request->getParam('filtro'));

        $filter->addElement($submit);
        $filter->addElement($reset);

        $form->addSubForm($filter, "filter");
        $this->view->form_filter = $form;
        $this->view->filter = array(array("url" => "/snep/src/cont_names.php",
                "display" => $this->view->translate("Incluir Contato"),
                "css" => "include"),
            array("url" => "/snep/src/import_contacts_csv.php",
                "display" => $this->view->translate("Importar CSV"),
                "css" => "includes")
        );
    }
}
