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
class ContactsController extends Zend_Controller_Action {

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos");

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $contactsFilter = Snep_Contact_Manager::getFilter($field, $query);
        } else {
            $contactsFilter = Snep_Contact_Manager::getFilter(null, null);
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );


        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($contactsFilter);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->contacts = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = $this->getFrontController()->getBaseUrl() . "/contacts/index/";
        $this->view->BaseUrl = $this->getFrontController()->getBaseUrl();

        // Array de opcoes para o filtro.
        $opcoes = array("c.name" => $this->view->translate('Nome'));

        // Formulário de filtro.
        $config_file = "default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);

        $form = new Zend_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . '/contacts/index');
        $form->setAttrib('idCont', 'filtro');

        $filter = new Zend_Form($config->filter);
        $filter->addElement(new Zend_Form_Element_Submit("submit", array("label" => $this->view->translate("Enviar"))));

        // Captura elemento campo select
        $campo = $filter->getElement('campo');
        $campo->setMultiOptions($opcoes);

        $filtro = $filter->getElement('filtro');
        $filtro->setValue($this->_request->getPost('filtro'));

        $form->addSubForm($filter, "filter");
        $this->view->form_filter = $form;
        $this->view->filter = array( array("url" => $this->getFrontController()->getBaseUrl() . "/contacts/add",
                                           "display" => "Incluir Contato",
                                           "css" => "include"),
                                     array("url" => $this->getFrontController()->getBaseUrl() . "/contacts/csv",
                                           "display" => "Importar CSV",
                                           "css" => "includes") );
    }

    private function insertDynElements($subForm, $id)
    {
    	$value = true;
    	if ( is_null( $id ) ) {
    		$value = false;
    	} else {
    		$fields2 = Snep_Field_Manager::getFields($value, $id);
    	}
    	
    	$fields = Snep_Field_Manager::getFields(false, null);
    	
        foreach ($fields as $f) {
        	
        	$element = $subForm->createElement($f['type'], $f['id'])
						 ->setLabel($f['name'])
						 ->setDecorators(array(
            			 	'ViewHelper',
            				'Description',
            				'Errors',
            				array(array('dd' => 'HtmlTag'), array('tag' => 'dd')),
            				array('Label', array('tag' => 'dt')),
            				array(array('elementDiv' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_element'))
        				));
			$element->addValidators(array(
    					array('NotEmpty', true)
						));

			if ($f['required']) {
				$element->setRequired(true);
			}
			if ($value) {
				foreach ($fields2 as $f2) {
					if ($f2['name'] == $f['name']) {
						$element->setValue($f2['value']);
					}
				}
			}
			        					  
       		$subForm->addElement($element);
        }
        
    }
    
    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos");

        $form_xml = new Zend_Config_Xml("default/forms/contacts.xml");

        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/contacts/add' );

        Snep_Field_Manager::insertDynElements($form, null);

        $form->getElement('group')->setMultiOptions( Snep_Group_Manager::getAll() );
        
        $form->setButtom();

        if ($this->_request->getPost()) {
            
            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();
            
            if ($form_isValid) {
                $this->view->contacts = Snep_Contact_Manager::add($dados);
                $this->_redirect("/contacts/");
            }
        }
        
        $this->view->form = $form;
    }

    public function csvAction() {
        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos");

        $form = new Snep_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/contacts/csvprocess');
        $form->addElement(new Zend_Form_Element_File("contacts_csv", array("label" => $this->view->translate("Arquivo CSV"))));
        $form->setButtom();

        $this->view->form = $form;
    }

    public function csvprocessAction() {
        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos");

        $adapter = new Zend_File_Transfer_Adapter_Http();

        if (!$adapter->isValid()) {
            return new ErrorException("Formato de arquivo invalido");
            //echo "Formato de arquivo invalido";
            exit;
        } else {
            $adapter->receive();

            $fileName = $adapter->getFileName();

            $handle = fopen($fileName, "r");
            $csv = array();
            $row_number = 2;
            $first_row = explode(",", str_replace("\n", "", fgets($handle, 4096)));
            $column_count = count($first_row);
            $csv[] = $first_row;

            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if (strpos($line, ",")) {
                    $row = explode(",", str_replace("\n", "", $line));
                    if (count($row) != $column_count) {
                        throw ErrorException("Número inválido de colunas na linha: $row_number");
                    }
                    $csv[] = $row;
                    $row_number++;
                }
            }
            fclose($handle);

            $standard_fields = array(
                "discard" => $this->view->translate("Descartar"),
                "nameCont" => $this->view->translate("Nome"),
                "phone" => $this->view->translate("Telefone")
            );

            $custom_fields = Snep_Field_Manager::getFields(false, null);
            foreach( $custom_fields as $fields ) {
                $standard_fields[$fields['name']] = $fields['name'];
            }

            $session = new Zend_Session_Namespace('ad_csv');
            $session->data = $csv;

            $all_groups = Snep_Group_Manager::getAll();

            $groups = array();
            foreach($all_groups as $one_group) {
                $groups[$one_group['id']] = $one_group['name'];
            }

            $this->view->BaseUrl = $this->getFrontController()->getBaseUrl();
            $this->view->csvprocess = array_slice($csv,0,10);
            $this->view->fields = $standard_fields;
            $this->view->group = $groups;
        }
    }

    public function csvfinishAction() {
        if($this->getRequest()->isPost()) {
            $session = new Zend_Session_Namespace('ad_csv');
            $fields = $_POST['field'];

            foreach ($session->data as $contact) {
                $contactData = array();
                foreach ($contact as $column => $data) {
                    if($fields[$column] != "discard") {
                        $contactData[$fields[$column]] = $data;
                    }
                }
                $contactData['group'] = $_POST['group'];
                Snep_Contact_Manager::add($contactData);

            }
        }

        $this->_redirect("contacts");
    }
    
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate( "Cadastro » Contatos" );

        $id = $this->_request->getParam('id');

        $dados = Snep_Contact_Manager::get( $id );

        $form_xml = new Zend_Config_Xml( "default/forms/contacts.xml" );
        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . "/contacts/edit/id/$id" );

        $this->insertDynElements($form, $id);

        $form->getElement('nameCont')->setValue( $dados['nameCont'] );
        $form->getElement('phone')->setValue( $dados['phone'] );
        $form->getElement('group')->setMultiOptions( Snep_Group_Manager::getAll() )->setValue( $dados['group'] );
 
        $form->setButtom();

        if ($this->getRequest()->isPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();
			
            if ($form_isValid) {            	
                $contact = $dados;
                $contact["id"] = $id;

                $this->view->contacts = Snep_Contact_Manager::edit($contact);
                $this->_redirect("/contacts/");
            }
        }
        $this->view->form = $form;
    }

    public function delAction() {
        $id = $this->_request->getParam('id');
        $this->view->contacts = Snep_Contact_Manager::del($id);
        $this->_redirect("/contacts/");
    }

}
