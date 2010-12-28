<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */

/**
 * Controlador Contatos
 *
 * @see 
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author guax
 *
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

    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Contatos");
        $this->view->pathweb = Zend_Registry::get('config')->system->path->web;        

        // Campos padrão do formulário
        $form_xml = new Zend_Config_Xml("default/forms/contacts.xml");

        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/contacts/add' );

        // Adiciona os campos customizados ao form.
        Snep_Field_Manager::insertDynElements($form, null);

        // Pega grupos de contatos
        $all_groups = Snep_Group_Manager::getAll();
        $groups = array();
        foreach($all_groups as $one_group) {
            $groups[$one_group['id']] = $one_group['name'];
        }
        // Seta valores para grupos
        $form->getElement('group')->setMultiOptions( $groups );

        // Cria elemento com lista ordenada dinamicamente
        $phone = new Snep_Form_Element_Phones("Phones", array('label' => 'Phone' ));
        // Insere elemento ao form
        $form->addElement($phone);

        // Seta barra de menu padrão dos formulários.
        $form->setButtom();

        if ($this->_request->getPost()) {
            
            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            // Filtra numeros de telefones, remove letras e caracteries especiais
            $phones = array();
            $valid_number = new Zend_Validate_Digits();

            foreach( $_POST['phones'] as $k => $phone ) {

                if($valid_number->isValid( $phone ) ) {
                    $phones[] = filter_var($phone,FILTER_SANITIZE_NUMBER_INT);

                }else{
                    $form_isValid = false;
                    $this->view->number_error = $this->view->translate("Telefones devem conter somente números.");
                }                
            }
            $dados['phones'] = $phones;

            // Se válido, adiciona o mesmo.
            if ($form_isValid) {
                Snep_Contact_Manager::add($dados);
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
                $standard_fields[$fields['id']] = $fields['name'];
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

                        if($fields[$column] == "phone") {
                            $contactData['phones'][] = $data;
                        }else{
                            $contactData[$fields[$column]] = $data;

                        }                        
                    }
                }
                $contactData['group'] = $_POST['group'];
                Snep_Contact_Manager::add($contactData);
                print_r($contactData);
            }
            
        }

        $this->_redirect("contacts");
    }
    
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate( "Cadastro » Contatos" );
        $this->view->pathweb = Zend_Registry::get('config')->system->path->web;

        $id = $this->_request->getParam('id');

        $dados = Snep_Contact_Manager::get( $id );

        // Pega telefones do id referido.
        $this->view->phones = Snep_Contact_Manager::getPhones( $id );

        // Formulario padrao da rotina
        $form_xml = new Zend_Config_Xml( "default/forms/contacts.xml" );
        $form = new Snep_Form( $form_xml->general );
        $form->setAction( $this->getFrontController()->getBaseUrl() . "/contacts/edit/id/$id" );

        // Insere no form campos customizados
        Snep_Field_Manager::insertDynElements($form, $id);

        $all_groups = Snep_Group_Manager::getAll();
        $groups = array();
        foreach($all_groups as $one_group) {
            $groups[$one_group['id']] = $one_group['name'];
        }

        $form->getElement('nameCont')->setValue( $dados['nameCont'] );
        $form->getElement('group')->setMultiOptions( $groups )->setValue( $dados['group'] );

        // Cria elemento com lista ordenada dinamicamente
        $phone = new Snep_Form_Element_Phones("Phones", array('label' => 'Phone' ));

        // Insere elemento no form
        $form->addElement($phone);

        // Insere botões padrão do form
        $form->setButtom();

        if ($this->getRequest()->isPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();
			
            if ($form_isValid) {
                $contact = $dados;
                $contact["id"] = $id;

                // Filtra numeros de telefones, remove letras e caracteries especiais
                $phones = array();
                $valid_number = new Zend_Validate_Digits();

                foreach( $_POST['phones'] as $k => $phone ) {

                    if($valid_number->isValid( $phone ) ) {
                        $phones[] = filter_var($phone,FILTER_SANITIZE_NUMBER_INT);

                    }else{
                        $form_isValid = false;
                        $this->view->number_error = $this->view->translate("Telefones devem conter somente números.");
                    }
                }
                $dados['phones'] = $phones;

                // Se válido, adiciona o mesmo.
                if ($form_isValid) {
                    $this->view->contacts = Snep_Contact_Manager::edit($contact);
                    $this->_redirect("/contacts/");
                }
                
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
