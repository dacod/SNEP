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
 * @author    Andrey Abreu
 * 
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
                ->from(array("n" => "contact"), array("n.id_contact as id", "n.ds_name as name", "n.ds_city as city", "n.ds_state as state", "n.ds_cep as cep", "n.ds_phone as phone", "n.ds_cell_phone as cellphone", "g.ds_name as group"))
                ->join(array("g" => "contact_group"), 'n.id_contact_group = g.id_contact_group', array())
                ->order('n.id_contact');
        
        if ($this->_request->getPost('filtro')) {
            $field = pg_escape_string($this->_request->getPost('campo'));
            $query = pg_escape_string($this->_request->getPost('filtro'));
            $select->where("$field like '%$query%'");
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

        /*Opcoes do Filtro*/
        $opcoes = array("n.ds_name" => $this->view->translate("Name"),
                        "n.ds_city" => $this->view->translate("City"),
                        "n.ds_state" => $this->view->translate("State"),
                        "n.ds_cep" => $this->view->translate("ZIP Code"),
                        "n.ds_phone" => $this->view->translate("Phone"),
                        "n.ds_cell_phone" => $this->view->translate("Cellphone"));

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
        $form = new Snep_Form(new Zend_Config_Xml("modules/default/forms/contacts.xml"));
        
        $contact = new Snep_Contacts_Manager();
        
        /*
         * Combo de Grupos
         */
        $group = new Snep_ContactGroups_Manager();
        $_allGroups = $group->fetchAll();
        
        foreach ($_allGroups as $group) {
            $allGroups[$group['id_contact_group']] = $group['ds_name'];
        }

        if (count($_allGroups)) {
            $form->getElement('group')->setMultiOptions($allGroups);
        }

        /*
         * Combo de Cidades
         */
        $city = new Snep_City_Manager();
        $_allCities = $city->fetchAll();
       
        foreach ($_allCities as $city) {
            $allCities[$city['ds_name']] = $city['ds_name'];
        }

        if (count($_allCities)) {
            $form->getElement('city')->setMultiOptions($allCities);
        }
        
         /*
         * Combo de Estados
         */
        $state = new Snep_State_Manager();
        $_allStates = $state->fetchAll();
       
        foreach ($_allStates as $state) {
            $allStates[$state['ds_name']] = $state['ds_name'];
        }

        if (count($_allStates)) {
            $form->getElement('state')->setMultiOptions($allStates);
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
           
            $dados = array('ds_name'=> $_POST['name'], 
                           'id_contact_group' => $_POST['group'],
                            'ds_address' => $_POST['address'], 
                            'ds_city'=>$_POST['city'],
                            'ds_state'=>$_POST['state'], 
                            'ds_cep'=>$_POST['zipcode'], 
                            'ds_phone'=>$_POST['phone'], 'ds_cell_phone'=>$_POST['cell']);
                    

            if ($form_isValid) {
                $contact->insert($dados);
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
        $obj = new Snep_Contacts_Manager();
        $select = $obj->select()->where('id_contact = ?', $id);
        $contact = $obj->fetchRow($select)->toArray();
       
        Zend_Registry::set('cancel_url', 
                           $this->getFrontController()->getBaseUrl() . '/' . 
                           $this->getRequest()->getControllerName() . '/index');
        
        $form = new Snep_Form(new Zend_Config_Xml("modules/default/forms/contacts.xml"));

        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. 
                          $this->getRequest()->getControllerName() . '/edit/id/'.$id );

        $idco = new Zend_Form_Element_Hidden('id_contact');
        $idco->setValue($contact['id_contact']);
        
        $form->getElement('id')->setValue($contact['id_contact'])->setAttrib('readonly', true);
        $form->getElement('name')->setValue($contact['ds_name']);
        $form->getElement('address')->setValue($contact['ds_address']);
        
        /*
         * Combo de Grupos
         */
        $group = new Snep_ContactGroups_Manager();
        $_allGroups = $group->fetchAll();
        
        foreach ($_allGroups as $group) {
            $allGroups[$group['id_contact_group']] = $group['ds_name'];
        }

        if (count($_allGroups)) {
            $form->getElement('group')->setMultiOptions($allGroups);
            $form->getElement('group')->setValue($contact['id_contact_group']); 
        }
        
        isset($contact['group']) ? $group
            ->setValue($contact['id_contact_group']) : null;
        
		/*
		 * Combo de Cidades
		 * */
        $city = new Snep_City_Manager();
        $_allCities = $city->fetchAll();
       
        foreach ($_allCities as $city) {
            $allCities[$city['ds_name']] = $city['ds_name'];
        }

        if (count($_allCities)) {
            $form->getElement('city')
            		  ->setMultiOptions($allCities)
	                  ->addMultiOption($contact['ds_city'],$contact['ds_city'])
            		  ->setValue($contact['ds_city']); 
        }
        
        /*
         * Combo de Estado
         * */
        $state = new Snep_State_Manager();
        $_allStates = $state->fetchAll();
       
        foreach ($_allStates as $state) {
            $allStates[$state['ds_name']] = $state['ds_name'];
        }

        if (count($_allStates)) {
            $form->getElement('state')
            		->setMultiOptions($allStates)
           		    ->addMultiOption($contact['ds_state'],$contact['ds_state'])
            		->setValue($contact['ds_state']); 
        }
      
        $form->getElement('zipcode')->setValue($contact['ds_cep']);
        $form->getElement('phone')->setValue($contact['ds_phone']);
        $form->getElement('cell')->setValue($contact['ds_cell_phone']);

        if ($this->_request->getPost()) {
            $_POST['id'] = trim($_POST['id']);            
            $form_isValid = $form->isValid($_POST);
            
            $dados = array('ds_name'=> $_POST['name'], 
                           'id_contact_group' => $_POST['group'],
                            'ds_address' => $_POST['address'], 
                            'ds_city'=>$_POST['city'],
                            'ds_state'=>$_POST['state'], 
                            'ds_cep'=>$_POST['zipcode'], 
                            'ds_phone'=>$_POST['phone'], 'ds_cell_phone'=>$_POST['cell']);
              
            if ($form_isValid) {

                $contact = new Snep_Contacts_Manager();
                $contact->update($dados, "id_contact = {$_POST['id']}");
          
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
        $contact = new Snep_Contacts_Manager();
        $contact->delete("id_contact = $id");
    }

    /**
     * Remove various contacts
     */
    public function multiRemoveAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts"),
                    $this->view->translate("Remove Multiple")
                ));
                
         $contact = new Snep_Contacts_Manager();
        
         if($this->_request->getPost()) {
			if($_POST['group'] != '0') {
				$idGroup = $_POST['group'];
    	 		$contact->delete("id_contact_group = $idGroup");
            }else{
				$contact->delete();
            }
                
            $this->_redirect($this->getRequest()->getControllerName());
		 }else{
			$this->view->message = $this->view->translate('Select a contact group to remove your contacts.');

		    $group = new Snep_ContactGroups_Manager();
		    $_allGroups = $group->fetchAll();
		        
			foreach ($_allGroups as $group) {
		    	$allGroups[$group['id_contact_group']] = $group['ds_name'];
		    }
		
		    $form = new Snep_Form();
            $select = new Zend_Form_Element_Select('group');
		   	$select ->addMultiOption("0","All");
	         
		    if (count($_allGroups)) {
		    	$select->addMultiOptions($allGroups);
		    }
			$form->addElement($select);		        
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

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contacts"),
                    $this->view->translate("Export CSV")
                ));
        
        
        if($this->_request->getPost()) {

            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->from(array("n" => "contact"), array("n.id_contact as id", "n.ds_name as name", "n.ds_city as city", "n.ds_state as state", "n.ds_cep as cep", "n.ds_phone as phone", "n.ds_cell_phone as cellphone", "g.ds_name as group"))
                ->join(array("g" => "contact_group"), 'n.id_contact_group = g.id_contact_group', array())
                ->order('n.id_contact');
        
            if($_POST['group'] != '0') {
                $select->where('g.id_contact_group = ?',$_POST['group']);
            }
            
            $stmt = $db->query($select);
            $contacts = $stmt->fetchAll();

            $headers = array('id_contact' => $this->view->translate('Code'),
                             'ds_nome' => $this->view->translate('Name'),
                             'ds_city' => $this->view->translate('City'),
                             'ds_state' => $this->view->translate('State'),
                             'ds_cep' => $this->view->translate('ZipCode'),
                             'ds_phone' => $this->view->translate('Phone'),
                             'ds_cell_phone' => $this->view->translate('Mobile'),
                             'id_contact_group' => $this->view->translate('Grupo') );

            
            
            if (count($contacts) > 0){
                
                $csv = new Snep_Csv();

                $csv_data = $csv->generate($contacts, $headers);

                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();

                $dateNow = new Zend_Date();
                $fileName = $this->view->translate('Contacts_csv_') . $dateNow->toString($this->view->translate(" dd-MM-yyyy_hh'h'mm'm' ")) . '.csv';

                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');

                echo $csv_data;
            
            }
            else {
                 $this->view->message = $this->view->translate('A Selecao nao retornou resultados');
            }
        }else{

            $this->view->message = $this->view->translate('Select a contact group to export.');
            
            $group = new Snep_ContactGroups_Manager();
            $_allGroups = $group->fetchAll();

            foreach ($_allGroups as $group) {
                $allGroups[$group['id_contact_group']] = $group['ds_name'];
            }

            $form = new Snep_Form();
            $select = new Zend_Form_Element_Select('group');
	        $select ->addMultiOption("0","All");
	            
	        if (count($allGroups >0)){
	           	$select	->addMultiOptions( $allGroups );
	        }
     				
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
                $row = explode(",", $line);

                    if (count($row) != $column_count) {
                        throw new ErrorException($this->view->translate("Invalid column count on line %d", $row_number));
                    }
                    $csv[] = $row;
                    $row_number++;
                }
            }
            fclose($handle);

            $standard_fields = array("discard" => $this->view->translate("Discard"),
                "ds_name" => $this->view->translate("Name"),
                "ds_address" => $this->view->translate("Address"),
                "ds_city" => $this->view->translate("City"),
                "ds_state" => $this->view->translate("State"),
                "ds_cep" => $this->view->translate("Zip Code"),
                "ds_phone" => $this->view->translate("Phone"),
                "ds_cell_phone" => $this->view->translate("Cellphone"),);

            $session = new Zend_Session_Namespace('csv');
            $session->data = $csv;

            $group = new Snep_ContactGroups_Manager();
            $_allGroups = $group->fetchAll();

            foreach ($_allGroups as $group) {
                $allGroups[$group['id_contact_group']] = $group['ds_name'];
            }
        
            if (!count($_allGroups) > 0) {
                $this->view->error = $this->view->translate('There is no contacts group registered.');
            }
            $this->view->csvprocess = array_slice($csv, 0, 10);
            $this->view->fields = $standard_fields;
            ( isset($allGroups) ? $this->view->group = $allGroups : $this->view->group = false);
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
                
                $contactData = array(
                    "ds_name" => "",
                    "ds_address" => "",
                    "ds_city" => "",
                    "ds_state" => "",
                    "ds_cep" => "",
                    "ds_phone" => "",
                    "ds_cell_phone" => "",
                    "id_contact_group" => "",);
               
                $addEntry = true;
                foreach ($contact as $column => $data) {
                    if ($fields[$column] != "discard") {
                        $contactData[$fields[$column]] = $data;
                    }
                }
                
                $contactData['id_contact_group'] = $_POST['group'];
                
                if (!array_key_exists('ds_name', $contactData) || !$validateEmpty->isValid($contactData['ds_name'])){
                    $addEntry = false;
                    $error[] = $contactData;
                }
                else if ((!array_key_exists('ds_phone', $contactData) || !$validateEmpty->isValid($contactData['ds_phone']))&&
                        (!array_key_exists('ds_cell_phone', $contactData) || !$validateEmpty->isValid($contactData['ds_cell_phone']))){
                     $addEntry = false;
                    $error[] = $contactData;
                }
                
                if ($addEntry){
                     $contact = new Snep_Contacts_Manager();
                     $contact->insert($contactData);
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
