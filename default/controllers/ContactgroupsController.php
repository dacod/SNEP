<?php
class ContactgroupsController extends Zend_Controller_Action {

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Contato");

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $groupFilter = Snep_Group_Manager::getFilter($field, $query);
        } else {
            $groupFilter = Snep_Group_Manager::getFilter(null, null);
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($groupFilter);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage( Zend_Registry::get('config')->ambiente->linelimit );

        $this->view->group = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = $this->getFrontController()->getBaseUrl() . "/contactgroups/index/";
        $this->view->BaseUrl = $this->getFrontController()->getBaseUrl();

        // Array de opcoes para o filtro.
        $opcoes = array("name" => $this->view->translate('Nome'));

        // Formulário de filtro.
        $config_file = "default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);

        $form = new Zend_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/contactgroups/index');
        $form->setAttrib('id', 'filtro');

        $filter = new Zend_Form($config->filter);
        $filter->addElement(new Zend_Form_Element_Submit("submit", array("label" => $this->view->translate("Enviar"))));

        // Captura elemento campo select
        $campo = $filter->getElement('campo');
        $campo->setMultiOptions($opcoes);

        $filtro = $filter->getElement('filtro');
        $filtro->setValue($this->_request->getPost('filtro'));

        $form->addSubForm($filter, "filter");
        $this->view->form_filter = $form;
        $this->view->filter = array( array("url" => $this->getFrontController()->getBaseUrl() . "/contactgroups/add",
                                           "display" => $this->view->translate("Incluir Grupo"),
                                           "css" => "includes"),
        );
    }

    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Contato");

        $form_xml = new Zend_Config_Xml("default/forms/group.xml");
        
        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/default/contactgroups/add');
        $form->setButtom();
        
        if ($this->getRequest()->getPost()) {

            $form_isValid = $form->isValid( $_POST );
            $dados = $this->_request->getParams();

            if ($form_isValid) {
                $group = array('name' => $dados['name']);

                $this->view->group = Snep_Group_Manager::add($group);

                $this->_redirect("/contactgroups/");
            }
        }


        $this->view->form = $form;
    }

    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Contato");

        $id = $this->_request->getParam('id');
        $dados = Snep_Group_Manager::get($id);

        $form_xml = new Zend_Config_Xml("default/forms/group.xml");
        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/contactgroups/edit');

        $form->getElement('name')->setValue( $dados['name'] );
        $form->getElement('id')->setValue( $dados['id'] );
        
        $form->setButtom();

        if ($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if ($form_isValid) {

                $group = array('id' => $dados['id'],
                               'name' => $dados['name']
                );

                $this->view->Group = Snep_Group_Manager::edit($group);
                $this->_redirect("/contactgroups/");
            }
        }
        $this->view->form = $form;
    }

    public function delAction() {

        $id = $this->_request->getParam('id');
        Snep_Group_Manager::del( $id );
        $this->_redirect("/contactgroups/");
    }

}