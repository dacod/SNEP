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
 * Contacts Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */
class ContactsController extends Zend_Controller_Action {

    /**
     * List all Contacts
     */
    public function indexAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts")
                ));
        $this->view->url = $this->getFrontController()->getBaseUrl() . "/" . $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array("n" => "contacts_names"), array("id as ide", "name as nome", "city", "state", "cep", "phone_1", "cell_1"))
                ->join(array("g" => "contacts_group"), 'n.group = g.id')
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
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("name" => $this->view->translate("Name"),
                        "city" => $this->view->translate("City"),
                        "state" => $this->view->translate("State"),
                        "cep" => $this->view->translate("ZIP Code"),
                        "phone_1" => $this->view->translate("Phone"),
                        "cell_1" => $this->view->translate("Cellphone"));

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Add Contact"),
                                          "css" => "include"),
                                    array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/multi-remove/",
                                          "display" => $this->view->translate("Remove Multiple"),
                                          "css" => "exclude"),
                                    array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/import/",
                                          "display" => $this->view->translate("Import CSV"),
                                          "css" => "import"),
                                    array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/export/",
                                          "display" => $this->view->translate("Export CSV"),
                                          "css" => "export") );
    }

    /**
     *  Add Contact
     */
    public function addAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts"),
                    $this->view->translate("Add")
                ));

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form(new Zend_Config_Xml("default/forms/contacts.xml"));

        $form->getElement('id')->setValue(Snep_Contacts_Manager::getLastId());

        $_allGroups = Snep_ContactGroups_Manager::getAll();
        foreach ($_allGroups as $group) {
            $allGroups[$group['id']] = $group['name'];
        }

        if (count($_allGroups)) {
            $form->getElement('group')->setMultiOptions($allGroups);
        }

        if ($this->_request->getPost()) {

            if (empty($_POST['cell'])) {
                $form->getElement('phone')->setRequired(true);
            }
            if (empty($_POST['phone'])) {
                $form->getElement('cell')->setRequired(true);
            }

            $form_isValid = $form->isValid($_POST);

            if (empty($_POST['group'])) {
                $form->getElement('group')->addError($this->view->translate('No group selected'));
                $form_isValid = false;
            }
            if (Snep_Contacts_Manager::get($_POST['id'])) {
                $form->getElement('id')->addError($this->view->translate('Code already exists'));
                $form_isValid = false;
            }

            $dados = $this->_request->getParams();


            if ($form_isValid) {
                Snep_Contacts_Manager::add($dados);
                $this->_redirect($this->getRequest()->getControllerName());
            }
        }

        $this->view->form = $form;
    }

    /**
     * Edit Contact
     */
    public function editAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts"),
                    $this->view->translate("Edit")
                ));

        $id = $this->_request->getParam('id');

        $contact = Snep_Contacts_Manager::get($id);

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form(new Zend_Config_Xml("default/forms/contacts.xml"));

        $form->getElement('id')->setValue($contact['id'])->setAttrib('readonly', true);
        $form->getElement('name')->setValue($contact['name']);

        $_allGroups = Snep_ContactGroups_Manager::getAll();
        foreach ($_allGroups as $group) {
            $allGroups[$group['id']] = $group['name'];
        }

        $group = $form->getElement('group')->setMultiOptions($allGroups);
        ( isset($contact['group']) ? $group->setValue($contact['group']) : null );

        $address = $form->getElement('address');
        ( isset($contact['address']) ? $address->setValue($contact['address']) : null );

        $city = $form->getElement('city');
        ( isset($contact['city']) ? $city->setValue($contact['city']) : null );

        $state = $form->getElement('state');
        ( isset($contact['state']) ? $state->setValue($contact['state']) : null );

        $zipcode = $form->getElement('zipcode');
        ( isset($contact['cep']) ? $zipcode->setValue($contact['cep']) : null );

        $phone = $form->getElement('phone');
        ( isset($contact['phone_1']) ? $phone->setValue($contact['phone_1']) : null );

        $cell = $form->getElement('cell');
        ( isset($contact['cell_1']) ? $cell->setValue($contact['cell_1']) : null );

        if ($this->_request->getPost()) {
            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if ($form_isValid) {

                Snep_Contacts_Manager::edit($dados);
                $this->_redirect($this->getRequest()->getControllerName());
            }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a Contact
     */
    public function removeAction() {
        $id = $this->_request->getParam('id');

        Snep_Contacts_Manager::remove($id);
        $this->_redirect($this->getRequest()->getControllerName());
    }

    /**
     * Remove various contacts
     */
    public function multiRemoveAction() {

            if($this->_request->getPost()) {

                if( $_POST['group'] == 'all' ) {
                    $groups = Snep_ContactGroups_Manager::getAll();

                }else{
                    $groups = Snep_ContactGroups_Manager::get($_POST['group']);
                }
                
                foreach($groups as $group ) {
                    Snep_Contacts_Manager::removeByGroupId($group['id']);                    
                }

                $this->_redirect($this->getRequest()->getControllerName());

            }else{

                $this->view->message = $this->view->translate('Select a contact group to remove your contacts.');
                $_contactGroups = Snep_ContactGroups_Manager::getAll();
                $contactGroups = array('all' => $this->view->translate('Todos Grupos'));
                foreach($_contactGroups as $contactGroup) {
                    $contactGroups[$contactGroup['id']] = $contactGroup['name'] ;
                }

                $form = new Snep_Form();

                $select = new Zend_Form_Element_Select('group');
                $select->addMultiOptions( $contactGroups );

                $form->addElement( $select );
                $this->view->form = $form;

                $this->renderScript("contacts/select-multi-remove.phtml");

            }

    }

    /**
     * Import contacts from CSV file
     */
    public function importAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts"),
                    $this->view->translate("Import CSV")
                ));
        $this->view->message = $this->view->translate("The file must be separated by commas. Header is optional and columns can be associated in the next screen.");

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form();
        $form->addSubForm(new Snep_Form_SubForm(NULL, NULL, "file"),'file');
        $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/csv/');
        $form->getSubForm('file')->addElement(new Zend_Form_Element_File('file'));

        $this->view->form = $form;
    }

    /**
     * Export contacts for CSV file.
     */
    public function exportAction() {

        if($this->_request->getPost()) {

            $db = Zend_Registry::get('db');
            $select = $db->select()
                    ->from(array("n" => "contacts_names"), array("name as nome", "city", "state", "cep", "phone_1", "cell_1"))
                    ->join(array("g" => "contacts_group"), 'n.group = g.id')
                    ->order('g.id');

            if($_POST['group'] != 'all') {
                $select->where('g.id = ?',$_POST['group']);
            }

            $stmt = $db->query($select);
            $contacts = $stmt->fetchAll();

            $headers = array('nome' => $this->view->translate('Name'),
                             'city' => $this->view->translate('City'),
                             'state' => $this->view->translate('State'),
                             'cep' => $this->view->translate('ZipCode'),
                             'phone_1' => $this->view->translate('Phone'),
                             'cell_1' => $this->view->translate('Mobile'),
                             'name' => $this->view->translate('Grupo') );


            $csv = new Snep_Csv();
            $csv_data = $csv->generate($contacts, $headers);

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();

            $dateNow = new Zend_Date();
            $fileName = $this->view->translate('Contacts_csv_') . $dateNow->toString($this->view->translate(" dd-MM-yyyy_hh'h'mm'm' ")) . '.csv';

            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            
            echo $csv_data;
            

        }else{

            $this->view->message = $this->view->translate('Select a contact group to export.');
            $_contactGroups = Snep_ContactGroups_Manager::getAll();
            $contactGroups = array('all' => $this->view->translate('Todos Grupos'));
            foreach($_contactGroups as $contactGroup) {
                $contactGroups[$contactGroup['id']] = $contactGroup['name'] ;
            }

            $form = new Snep_Form();

            $select = new Zend_Form_Element_Select('group');
            $select->addMultiOptions( $contactGroups );

            $form->addElement( $select );
            $this->view->form = $form;
            
            $this->renderScript("contacts/export.phtml");
        }

    }

    /**
     * Associate fields between database and csv file
     */
    public function csvAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts"),
                    $this->view->translate("Import CSV"),
                    $this->view->translate("Column Association"),
                ));
        $adapter = new Zend_File_Transfer_Adapter_Http();

        $this->view->url = $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName();

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
                        throw new ErrorException($this->view->translate("Invalid column count on line %d", $row_number));
                    }
                    $csv[] = $row;
                    $row_number++;
                }
            }
            fclose($handle);

            $standard_fields = array("discard" => $this->view->translate("Discard"),
                "name" => $this->view->translate("Name"),
                "address" => $this->view->translate("Address"),
                "city" => $this->view->translate("City"),
                "state" => $this->view->translate("State"),
                "zipcode" => $this->view->translate("Zip Code"),
                "phone" => $this->view->translate("Phone"),
                "cell" => $this->view->translate("Cellphone"));

            $session = new Zend_Session_Namespace('csv');
            $session->data = $csv;

            $_groups = Snep_ContactGroups_Manager::getAll();
            foreach ($_groups as $group) {
                $groups[$group['id']] = $group['name'];
            }

            if (!count($_groups) > 0) {
                $this->view->error = $this->view->translate('There is no contacts group registered.');
            }

            $this->view->csvprocess = array_slice($csv, 0, 10);
            $this->view->fields = $standard_fields;
            ( isset($groups) ? $this->view->group = $groups : $this->view->group = false);
        }
    }

    /**
     * Process a csv file
     */
    public function csvprocessAction() {
        if ($this->getRequest()->isPost()) {
            $session = new Zend_Session_Namespace('csv');
            $fields = $_POST['field'];
            $skipped = false;
            $validateEmpty = new Zend_Validate_NotEmpty();
            $validateAlnum = new Zend_Validate_Alnum();
            $error = array();

            foreach ($session->data as $contact) {

                if (isset($_POST['discard_first_row']) && $_POST['discard_first_row'] == "on" && $skipped == false) {
                    $skipped = true;
                    continue;
                }
                $contactData = array("discard" => "",
                    "name" => "",
                    "address" => "",
                    "city" => "",
                    "state" => "",
                    "zipcode" => "",
                    "phone" => "",
                    "cell" => "");
               
                $addEntry = true;
                foreach ($contact as $column => $data) {
                    if ($fields[$column] != "discard") {
                        $contactData[$fields[$column]] = $data;
                    }
                }

                $contactData['group'] = $_POST['group'];
                $contactData['id'] = Snep_Contacts_Manager::getLastId();
                
                if (!array_key_exists('name', $contactData) || !$validateEmpty->isValid($contactData['name'])){
                    $addEntry = false;
                    $error[] = $contactData;
                }
                else if ((!array_key_exists('phone', $contactData) || !$validateEmpty->isValid($contactData['phone']))&&
                        (!array_key_exists('cell', $contactData) || !$validateEmpty->isValid($contactData['cell']))){
                     $addEntry = false;
                    $error[] = $contactData;
                }
                
                if ($addEntry){
                     Snep_Contacts_Manager::add($contactData);
                }      
            }
            if (count($error)>0){
                $errorString = $this->view->translate('The following entries of the CSV file have null data:<br/>');
                foreach ($error as $value) {
                    $errorString.= implode(',',$value).'<br/>';
                }
                throw new ErrorException($errorString);
            }
        }
        $this->_redirect($this->getRequest()->getControllerName());
    }

}
