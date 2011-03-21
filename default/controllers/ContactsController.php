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

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from( array("n" => "contacts_names"), array("id as ide", "name as nome", "city", "state", "cep", "phone_1", "cell_1"))
                        ->join( array("g" => "contacts_group"), 'n.group = g.id')
                        ->order('nome');
                 
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("n.`$field` like '%$query%'");
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

        $opcoes = array("name"      => $this->view->translate("Nome"),
                        "city"      => $this->view->translate("Cidade"),
                        "state"     => $this->view->translate("Estado"),
                        "cep"       => $this->view->translate("CEP"),
                        "phone_1"   => $this->view->translate("Telefone"),
                        "cell_1"    => $this->view->translate("Celular"));

	// Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Incluir Contato"),
                                          "css" => "include"),
                                    array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/import/",
                                          "display" => $this->view->translate("Importar CSV"),
                                          "css" => "includes")
        );
    }

    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Contatos » Cadastro");

        $db = Zend_Registry::get('db');

        $xml = new Zend_Config_Xml( "default/forms/contacts.xml" );

        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/add');

        $name = $form->getElement('name');
        $name->setLabel( $this->view->translate('Nome') );

        $_allGroups = Snep_ContactGroups_Manager::getAll();
        foreach($_allGroups as $group) {            
                $allGroups[$group['id']] = $group['name'];            
        }
        $group = $form->getElement('group');
        $group->setLabel( $this->view->translate('Grupo') );
        $group->setMultiOptions( $allGroups );

        $address = $form->getElement('address');
        $address->setLabel( $this->view->translate('Endereço') );

        $city = $form->getElement('city');
        $city->setLabel( $this->view->translate('Cidade') );

        $state = $form->getElement('state');
        $state->setLabel( $this->view->translate('Estado') );

        $zipcode = $form->getElement('zipcode');
        $zipcode->setLabel( $this->view->translate('CEP') );

        $phone = $form->getElement('phone');
        $phone->setLabel( $this->view->translate('Fone') );

        $cell = $form->getElement('cell');
        $cell->setLabel( $this->view->translate('Celular') );

        $form->setButtom();

        if($this->_request->getPost()) {

                if( empty( $_POST['cell']) ) {
                    $phone->setRequired(true);
                }

                if( empty( $_POST['phone']) ) {
                    $cell->setRequired(true);
                }

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid){
                    Snep_Contacts_Manager::add($dados);
                    $this->_redirect('default/contacts/');
                }
        }
        $this->view->form = $form;

    }

    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Contatos » Editar");

        $db = Zend_Registry::get('db');
        $id = $this->_request->getParam('id');

        $contact = Snep_Contacts_Manager::get($id);

        $xml = new Zend_Config_Xml( "default/forms/contacts.xml" );

        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/edit/id/'.$id);

        $name = $form->getElement('name');
        $name->setLabel( $this->view->translate('Nome') );
        $name->setValue($contact['name']);

        $_allGroups = Snep_ContactGroups_Manager::getAll();
        foreach($_allGroups as $group) {
                $allGroups[$group['id']] = $group['name'];
        }

        $group = $form->getElement('group');
        $group->setLabel( $this->view->translate('Grupo') );
        $group->setMultiOptions( $allGroups );
        ( isset($contact['group']) ? $group->setValue($contact['group']) : null );
        
        $address = $form->getElement('address');
        $address->setLabel( $this->view->translate('Endereço') );        
        ( isset($contact['address']) ? $address->setValue($contact['address']) : null );

        $city = $form->getElement('city');
        $city->setLabel( $this->view->translate('Cidade') );        
        ( isset($contact['city']) ? $city->setValue($contact['city']) : null );

        $state = $form->getElement('state');
        $state->setLabel( $this->view->translate('Estado') );        
        ( isset($contact['state']) ? $state->setValue($contact['state']) : null );

        $zipcode = $form->getElement('zipcode');
        $zipcode->setLabel( $this->view->translate('CEP') );

        ( isset($contact['cep']) ? $zipcode->setValue($contact['cep']) : null );

        $phone = $form->getElement('phone');
        $phone->setLabel( $this->view->translate('Fone') );
        ( isset($contact['phone_1']) ? $phone->setValue($contact['phone_1']) : null );

        $cell = $form->getElement('cell');
        $cell->setLabel( $this->view->translate('Celular') );
        ( isset($contact['cell_1']) ? $cell->setValue($contact['cell_1']) : null );

        $form->setButtom();

        if($this->_request->getPost()) {
                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid) {
                    
                    Snep_Contacts_Manager::edit($dados);
                    $this->_redirect('default/contacts/');
                }
        }
        $this->view->form = $form;
    }

    public function removeAction() {

       $this->view->breadcrumb = $this->view->translate("Contatos » Remover");
       $id = $this->_request->getParam('id');

       Snep_Contacts_Manager::remove($id);
       $this->_redirect('default/contacts/');

    }

    public function importAction() {

       $this->view->breadcrumb = $this->view->translate("Contatos » Importar CSV");
       $this->view->message = $this->view->translate("O arquivo deverá conter dados separados por virgula . O cabeçalho é opcional e pode ser removido na próxima tela.");

       $form = new Snep_Form();
       $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/csv/');

       $form->addElement( new Zend_Form_Element_File('file') );
       $form->setButtom();

       $this->view->form = $form;

    }

    public function csvAction() {

        $this->view->breadcrumb = $this->view->translate("Contatos » Associação de campos do CSV");

        $adapter = new Zend_File_Transfer_Adapter_Http();

        if (!$adapter->isValid()) {
            $this->view->invalid = true;

        } else {

            $this->view->invalid = false;
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
                    $row = explode(",", preg_replace("/[^a-zA-Z0-9,\._\*#]/", "", $line));

                    if (count($row) != $column_count) {
                        throw new ErrorException("Número inválido de colunas na linha: $row_number");
                    }
                    $csv[] = $row;
                    $row_number++;
                }
            }
            fclose($handle);

            $standard_fields = array(
                "discard" => $this->view->translate("Descartar"),
                "name"    => $this->view->translate("Nome"),
                "address" => $this->view->translate("Endereço"),
                "city"    => $this->view->translate("Cidade"),
                "state"   => $this->view->translate("Estado"),
                "zipcode" => $this->view->translate("CEP"),
                "phone" => $this->view->translate("Telefone"),
                "cell"  => $this->view->translate("Celular")
            );

            $session = new Zend_Session_Namespace('csv');
            $session->data = $csv;

            $_groups = Snep_ContactGroups_Manager::getAll();
            foreach($_groups as $group) {
                $groups[$group['id']] = $group['name'];
            }

            $this->view->csvprocess = array_slice($csv, 0, 10);
            $this->view->fields = $standard_fields;
            $this->view->group = $groups;
        }

    }

    public function csvprocessAction() {

        if ($this->getRequest()->isPost()) {
            $session = new Zend_Session_Namespace('csv');
            $fields = $_POST['field'];
            $skipped = false;
            foreach ($session->data as $contact) {
                if(isset($_POST['discard_first_row']) && $_POST['discard_first_row'] == "on" && $skipped == false) {
                    $skipped = true;
                    continue;
                }
                $contactData = array("discard" => "",
                                     "name"    => "",
                                     "address" => "",
                                     "city"    => "",
                                     "state"   => "",
                                     "zipcode" => "",
                                     "phone"   => "",
                                     "cell"    => "");
                
                foreach ($contact as $column => $data) {
                    if ($fields[$column] != "discard") {
                            $contactData[$fields[$column]] = $data;
                    }
                }
                $contactData['group'] = $_POST['group'];

                Snep_Contacts_Manager::add($contactData);
            }
        }

        $this->_redirect("contacts");

    }
}
