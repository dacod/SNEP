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
 * @author    Rafael Pereira Bozzetti
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
                        ->from("contacts_group");

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

        $this->view->contactgroups = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("id" => $this->view->translate("Code"),
            "name" => $this->view->translate("Name"));

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
        
        $form = new Snep_Form( new Zend_Config_Xml("default/forms/contact_groups.xml") );
        $db = Zend_Registry::get('db');

        try {
            $sql = "SELECT c.id as id, c.name as name, g.name as `group` FROM contacts_names as c, contacts_group as g  WHERE (c.group = g.id) ";
            $contacts_result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            display_error($LANG['error'] . $e->getMessage(), true);
        }
        if (count($contacts_result) > 0) {
            $contact = array();
            foreach ($contacts_result as $key => $val) {
                $contact[$val['id']] = $val['name'] . " (" . $val['group'] . ")";
            }
            $this->view->objSelectBox = "contacts";
            $form->setSelectBox($this->view->objSelectBox, $this->view->translate('Contacts'), $contact, false);
        }

        if ($this->_request->getPost()) {
            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            $groupId = Snep_ContactGroups_Manager::add(array('group' => $dados['group']));

            if ($dados['box_add']) {
                foreach ($dados['box_add'] as $id => $idContact) {
                    Snep_ContactGroups_Manager::insertContactOnGroup($groupId, $idContact);
                }
            }
            $this->_redirect($this->getRequest()->getControllerName());
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

        $form = new Snep_Form( new Zend_Config_Xml("default/forms/contact_groups.xml") );

        $group = Snep_ContactGroups_Manager::get($id);
        $form->getElement('group')->setValue($group['name']);

        $groupContacts = array();
        foreach (Snep_ContactGroups_Manager::getGroupContacts($id) as $contact) {
            $groupContacts[$contact['id']] = "{$contact['name']} ({$contact['group']})";
        }
        if (count($groupContacts) > 0) {

            $noGroupContacts = array();
            foreach (Snep_Contacts_Manager::getAll() as $contact) {
                if (!isset($groupContacts[$contact['id']])) {
                    $noGroupContacts[$contact['id']] = "{$contact['name']} ({$contact['groupName']})";
                }
            }
            $this->view->objSelectBox = "contacts";
            $form->setSelectBox($this->view->objSelectBox, $this->view->translate('Contacts'), $noGroupContacts, $groupContacts);
        }

        $hiddenId = new Zend_Form_Element_Hidden('id');
        $hiddenId->setValue($id);
        $form->addElement($hiddenId);

        if ($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            $groupId = Snep_ContactGroups_Manager::edit(array('group' => $dados['group'], 'id' => $dados['id']));

            if ($dados['box_add']) {
                foreach ($dados['box_add'] as $id => $idContact) {
                    Snep_ContactGroups_Manager::insertContactOnGroup($dados['id'], $idContact);
                }
            }
            $this->_redirect($this->getRequest()->getControllerName());
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

        $confirm = $this->_request->getParam('confirm');

        if ($confirm == 1) {
            Snep_ContactGroups_Manager::remove($id);
            $this->_redirect('default/contact-groups/');
        }

        $contacts = Snep_ContactGroups_Manager::getGroupContacts($id);

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

        $_allGroups = Snep_ContactGroups_Manager::getAll();
        foreach ($_allGroups as $group) {
            if ($group['id'] != $id) {
                $allGroups[$group['id']] = $group['name'];
            }
        }

        $form = new Snep_Form( new Zend_Config_Xml("default/forms/contact_groups_migration.xml") );
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

                $contacts = Snep_ContactGroups_Manager::getGroupContacts($id);
                foreach ($contacts as $contact) {
                    Snep_ContactGroups_Manager::insertContactOnGroup($_POST['group'], $contact['id']);
                }

                Snep_ContactGroups_Manager::remove($_POST['id']);

            }
            elseif($_POST['option'] == 'remove') {
                $contacts = Snep_ContactGroups_Manager::getGroupContacts($id);
                foreach ($contacts as $contact) {
                    Snep_Contacts_Manager::remove($contact['id']);
                }

                Snep_ContactGroups_Manager::remove($_POST['id']);                
            }

            $this->_redirect($this->getRequest()->getControllerName());
        }

        $this->view->form = $form;
    }

}