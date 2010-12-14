<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigController
 *
 * @author guax
 */
class FieldController extends Zend_Controller_Action {

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos » Campos");

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $contactsFilter = Snep_Field_Manager::getFilter($field, $query);
        } else {
            $contactsFilter = Snep_Field_Manager::getFilter(null, null);
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($contactsFilter);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->field = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = $this->getFrontController()->getBaseUrl() . "/field/index/";
        $this->view->BaseUrl = $this->getFrontController()->getBaseUrl();

        // Array de opcoes para o filtro.
        $opcoes = array("name" => $this->view->translate('Nome'));

        // Formulário de filtro.
        $config_file = "default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);

        $form = new Zend_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/field/index');
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
        $this->view->filter = array( array( "url" => $this->getFrontController()->getBaseUrl() . "/field/add",
                                            "display" => $this->view->translate("Incluir Campo"),
                                            "css" => "include") );
    }
        
    public function addAction() {
        
    	$this->view->breadcrumb = $this->view->translate("Cadastro » Contatos » Campos");
   		
    	$form_xml = new Zend_Config_Xml("default/forms/contact_field.xml");

        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . "/field/add");

        $tipo = $form->getElement('type');
        $tipo->setMultiOptions(array('Text' => 'Textbox',
                                     'Checkbox' => 'Checkbox' ) );

        $form->setButtom();
        
        if ($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if ($form_isValid) {

                $fields = array('name' => $dados['name'],
                                'type' => $dados['type'],
                                'required' => $dados['required']
                );                
                $this->view->contacts = Snep_Contact_Manager::add_field($fields);
                $this->_redirect("/field/");

            }else{

                $errors = $form->getErrors();
                if(in_array('regexNotMatch', array_values($errors['name']))) {
                    $name = $subForm->getElement('name');                    
                    $name->setErrorMessages( array( $this->view->translate("Nome do campo não pode conter caracteres acentuados e espaços em branco.") ) );

                }
            }
        }

        $this->view->form = $form;
    }
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos » Campos");

        $id = $this->_request->getParam('id');

        $dados = Snep_Field_Manager::get($id);

        $form_xml = new Zend_Config_Xml("default/forms/contact_field.xml");
        $form = new Snep_Form( $form_xml->general );
        $form->setAction($this->getFrontController()->getBaseUrl() . "/field/edit/id/$id");

        $form->getElement('name')->setValue( $dados['name'] ) ;
        $form->getElement('required')->setValue( $dados['required'] );
        $form->getElement('type')->setMultiOptions( array('Text' => 'Textbox',
                                                          'Checkbox' => 'Checkbox' ))->setValue($dados['type']);

        $form->setButtom();

        if ($this->getRequest()->isPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();
			
            if ($form_isValid) {
            	
                $fields = $dados;
                $fields["id"] = $id;
                $this->view->contacts = Snep_Field_Manager::edit($fields);
                $this->_redirect("/field/");
            }else{

                $errors = $form->getErrors();
                if(in_array('regexNotMatch', array_values($errors['name']))) {
                    $name = $subForm->getElement('name');
                    $name->setErrorMessages( array( $this->view->translate("Nome do campo não pode conter caracteres acentuados e espaços em branco.") ) );

                }

            }
        }
        $this->view->form = $form;
    }
    
    
    public function delAction() {
        $id = $this->_request->getParam('id');
        $this->view->contacts = Snep_Field_Manager::del($id);
        $this->_redirect("/field/");
    }
    
}
