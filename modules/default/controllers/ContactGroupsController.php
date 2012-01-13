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
 * ContactGroups Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Andrey Abreu
 */
class ContactGroupsController extends Zend_Controller_Action {

    /**
     * List all Contact Groups
     */
    public function indexAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contact Group")
                ));
        $this->view->url = $this->getFrontController()->getBaseUrl() . "/" . $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from("contact_group");
        if ($this->_request->getPost('campo') == 'id_contact_group') {
            $field = pg_escape_string($this->_request->getPost('campo'));
            $query = pg_escape_string($this->_request->getPost('filtro'));
            
            if (preg_match('/^[0-9]+$/', $query)){
                $select->where("$field = '$query'");
            }
        }else if($this->_request->getPost('campo') == 'ds_name'){
            $field = pg_escape_string($this->_request->getPost('campo'));
            $query = pg_escape_string($this->_request->getPost('filtro'));
            $select->where("$field like '%$query%'");          
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = pg_escape_string($this->_request->getParam('filtro'));

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->contactgroups = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("id_contact_group" => $this->view->translate("Code"),
            "ds_name" => $this->view->translate("Name"));

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                "display" => $this->view->translate("Add Contact Group"),
                "css" => "include"),
        );
    }

    /**
     * Add a new Contact Group
     */
    public function addAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contact Group"),
                    $this->view->translate("Add")
                ));

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');

        $form = new Snep_Form( new Zend_Config_Xml("modules/default/forms/contact_groups.xml") );
        $db = Zend_Registry::get('db');

        try {
            $sql = "SELECT c.id_contact as id, c.ds_name as name, g.ds_name as group FROM contact as c, contact_group as g  WHERE (c.id_contact_group = g.id_contact_group) ";
            $contacts_result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
        }
            $contact = array();
            foreach ($contacts_result as $key => $val) {
                $contact[$val['id']] = $val['name'] . " (" . $val['group'] . ")";
            }
            $this->view->objSelectBox = "contacts";
            $form->setSelectBox($this->view->objSelectBox, $this->view->translate('Contacts'), $contact, false);

			$group = new Snep_ContactGroups_Manager ();
                            
        if ($this->_request->getPost()) {
            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();
            $dadosGroup = array('ds_name'=> $_POST['group']);
            if($form_isValid){
            	$groupId = $group->insert($dadosGroup);
	            $dadosUpdate = array('id_contact_group' => $groupId);
	            if ($dados['box_add']) {
    	            foreach ($dados['box_add'] as $id => $idContact) {
    		            $contact = new Snep_Contacts_Manager();
	                	$contact->update($dadosUpdate, "id_contact = $idContact");
                	}
            	}
            	$this->_redirect($this->getRequest()->getControllerName());
             }
        }
        $this->view->form = $form;
    }

    /**
     * Edit a Contact Group
     */
    public function editAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contact Group"),
                    $this->view->translate("Edit")
                ));

        $id = $this->_request->getParam('id');

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');

        $form = new Snep_Form( new Zend_Config_Xml("modules/default/forms/contact_groups.xml") );

        $id = $this->_request->getParam('id');
        $obj = new Snep_Contacts_Manager();
        $select = $obj->select()->where('id_contact_group = ?', $id);
        $group = $obj->fetchAll($select)->toArray();
        
        $groupContacts = array();

        foreach ($group as $contact) {
            $groupContacts[$contact['id_contact']] = "{$contact['ds_name']}";
        }
        
        $objGroup = new Snep_ContactGroups_Manager();
        $selectGroup = $objGroup->select()->where('id_contact_group = ?', $id);
        $groups = $objGroup->fetchrow($selectGroup)->toArray();
        
		$form->getElement('group')->setValue(trim($groups['ds_name']));
		
        $selectno = $obj->select()->where('id_contact_group <> ?', $id);
        $groupno = $obj->fetchAll($selectno)->toArray();
        
        $groupContactsno = array();

        foreach ($groupno as $contactsno) {
      	   $selectGroup = $objGroup->select()->where('id_contact_group =  ?', $contactsno['id_contact_group']);
           $groups = $objGroup->fetchRow($selectGroup)->toArray();
           $groupContactsno[$contactsno['id_contact']] = "{$contactsno['ds_name']} ({$groups['ds_name']})";
        }
        
        $this->view->objSelectBox = "contacts";
        $form->setSelectBox($this->view->objSelectBox, $this->view->translate('Contacts'), $groupContactsno, $groupContacts);

        $hiddenId = new Zend_Form_Element_Hidden('id');
        $hiddenId->setValue($id);
        $form->addElement($hiddenId);
        
        if ($this->_request->getPost()) {
            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();
            
            $dadosUpdate = array('ds_name'=> $_POST['group']); 
                            
            if ($form_isValid){
            	$contact = new Snep_ContactGroups_Manager();
	            $contact->update($dadosUpdate, "id_contact_group = $id");
	                	
           		if ($dados['box_add']) {
            	 	$dadosUpdate = array('id_contact_group' => $id);
                	foreach ($dados['box_add'] as $id => $idContact) {
                    	$contact = new Snep_Contacts_Manager();
	                	$contact->update($dadosUpdate, "id_contact = $idContact");
                	}
            	}
            	$this->_redirect($this->getRequest()->getControllerName());
            }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a Contact Group
     */
    public function removeAction() {
    	
       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contact Group"),
                    $this->view->translate("Delete")
                ));

    	$id = $this->_request->getParam('id');
        $contactGroup = new Snep_ContactGroups_Manager();
        $confirm = $this->_request->getParam('confirm');

        if ($confirm == 1) {
             $contactGroup->delete("id_contact_group = $id");
     		 $this->_redirect('default/contact-groups/');
        }

       $obj = new Snep_Contacts_Manager();
	   $select = $obj->select()->where('id_contact_group = ?', $id);
       $contacts = $obj->fetchAll($select)->toarray();
       
        if (count($contacts) > 0) {
            $this->_redirect('default/contact-groups/migration/id/' . $id);
        } else {
            $this->view->message = $this->view->translate("The group will be removed. After that you can't go back.");
            $form = new Snep_Form();
            $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/remove/id/' . $id . '/confirm/1');
            $this->view->form = $form;
        }
    }

    /**
     * Migrate contacts to other Contact Group
     */
    public function migrationAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Manage"),
                    $this->view->translate("Contact Group"),
                    $this->view->translate("Migrate Contacts")
                ));

        $id = $this->_request->getParam('id');
        $groupAll = new Snep_ContactGroups_Manager();
        $_allGroups = $groupAll->fetchAll();
        
        foreach ($_allGroups as $group) {
        	if ($group['id_contact_group'] != $id){
            	$allGroups[$group['id_contact_group']] = $group['ds_name'];
            }
        }

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form( new Zend_Config_Xml("modules/default/forms/contact_groups_migration.xml") );
        $form->setAction( $this->getFrontController()->getBaseUrl() . '/contact-groups/migration/stage/2' );

        if (isset($allGroups)) {
            $form->getElement('group')->setMultiOptions($allGroups);
            $form->getElement('option')->setMultiOptions( array('migrate' => 'migrate contacts to group',
                                                                'remove'  => 'remove all' ) )->setValue('migrate');
        } else {
            $form->removeElement('group');
            $form->getElement('option')->setMultiOptions( array('remove'  => 'remove all' ) );
        }

        $this->view->message = $this->view->translate("The excluded group has associated contacts.");
        $form->getElement('id')->setValue($id);

        $stage = $this->_request->getParam('stage');

        if (isset($stage['stage']) && $id) {
        	if($_POST['option'] == 'migrate') {
		        $obj = new Snep_Contacts_Manager();
		        $select = $obj->select()->where('id_contact_group = ?', $id);
		        $contacts = $obj->fetchAll($select)->toarray();
                
                $idGroup = $_POST['group'];
                $dadosUpdate = array('id_contact_group' => $idGroup);
                	
                foreach ($contacts as $contactselect) {
                	$idContact = $contactselect['id_contact'];
	                $obj->update($dadosUpdate, "id_contact = $idContact");
                }
                $groupAll->delete ("id_contact_group =$id");
            }
            elseif($_POST['option'] == 'remove') {
            	$obj = new Snep_Contacts_Manager();
		    	$select = $obj->select()->where('id_contact_group = ?', $id);
		        $contacts = $obj->fetchAll($select)->toArray();
            
            	foreach ($contacts as $contact) {
            		$idContact = $contact['id_contact'];
              		$obj->delete("id_contact = $idContact");
            	}
				$groupAll->delete ("id_contact_group =$id");
            }
            $this->_redirect($this->getRequest()->getControllerName());
        }
        $this->view->form = $form;
    }
}