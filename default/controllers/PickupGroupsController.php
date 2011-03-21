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
 * controller  pickup groups.
 */
class PickupGroupsController extends Zend_Controller_Action {

    /**
     * Add/Edit form for routes
     *
     * @var Zend_Form
     */
    protected $form;
    /**
     * Sub-form for Action Rules
     *
     * @var array
     */
    protected $forms;

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Captura");

        $db = Zend_Registry::get('db');


        $select = $db->select()
                        ->from("grupos");

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

        $this->view->pickupgroups = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/";
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("cod_grupo" => $this->view->translate("Código"),
            "nome" => $this->view->translate("Nome"));

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add",
                "display" => $this->view->translate("Incluir Grupos de Captura"),
                "css" => "include")
        );

    }

    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Captura » Incluir Grupos de Captura");
        $form_xml = new Zend_Config_Xml("default/forms/pickupGroup.xml");
        $form = new Snep_Form($form_xml->general);
        $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName().'/add');
        $name = $form->getElement('name')->setLabel($this->view->translate("Nome"));        

        if ($this->getRequest()->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if ($form_isValid) {
                $pickupgroup = array('nome' => $dados['name']);

                $this->view->group = Snep_PickupGroups_Manager::add($pickupgroup);

                $this->_redirect("/".$this->getRequest()->getControllerName()."/");
            }
        }

        $form->setButtom();
        $this->view->form = $form;

    }

    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Captura » Alterar Grupos de Captura");

        $id = $this->_request->getParam('id');
        $pickupgroup = Snep_PickupGroups_Manager::get($id);

        $form_xml = new Zend_Config_Xml("default/forms/pickupGroup.xml");
        $form = new Snep_Form($form_xml->general);
        $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName().'/edit');

        $name = $form->getElement('name')->setValue($pickupgroup['nome']);
        $id = $form->getElement('id')->setValue($pickupgroup['cod_grupo']);
        $name = $form->getElement('name')->setLabel($this->view->translate("Nome"));

        $form->setButtom();

        if ($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if ($form_isValid) {

                $pickupgroup = array('id' => $dados['id'],
                    'name' => $dados['name']
                );

                $this->view->Group = Snep_PickupGroups_Manager::edit($pickupgroup);
                $this->_redirect($this->getRequest()->getControllerName());
            }
        }
        $this->view->form = $form;

    }

    public function deleteAction() {

        $id = mysql_escape_string($this->getRequest()->getParam('id'));

        try {
            $pickugroups = Snep_PickupGroups_Manager::get($id);
        }
        catch(PBX_Exception_NotFound $ex) {
            throw new Zend_Controller_Action_Exception('Page not found.', 404);
        }

        Snep_PickupGroups_Manager::delete($id);

        $this->_redirect($this->getRequest()->getControllerName());
    }

}
