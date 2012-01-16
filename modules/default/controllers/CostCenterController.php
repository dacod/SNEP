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
 * Cost Center Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class CostCenterController extends Zend_Controller_Action {

    /**
     * List all Cost Center's
     */
    public function indexAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Cost Center")
        ));
        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from("cost_center", array("id_costcenter", "cd_code", "cd_type", "ds_name", "ds_description"));

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string( $this->_request->getPost('campo') );
            $query = mysql_escape_string( $this->_request->getPost('filtro') );

            if($field == 'cd_type') {
                $types = array($this->view->translate('Incoming') => 'E',
                               $this->view->translate('Outgoing') => 'S',
                               $this->view->translate('Others') => 'O');
                $query = $types[$query];
            }

            $select->where("$field LIKE '%$query%'");
        }

        $this->view->types = array('E' => $this->view->translate('Incoming'),
                                   'S' => $this->view->translate('Outgoing'),
                                   'O' => $this->view->translate('Others'));

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect( $select );
        $paginator = new Zend_Paginator( $paginatorAdapter );
        $paginator->setCurrentPageNumber( $this->view->page );
        $paginator->setItemCountPerPage( Zend_Registry::get('config')->ambiente->linelimit );

        $this->view->costcenter = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("cd_code" => $this->view->translate("Code"),
                        "ds_name" => $this->view->translate("Name"),
                        "ds_description" => $this->view->translate("Description") );
        
        $filter = new Snep_Form_Filter();
        $filter->setAction( $this->getFrontController()->getBaseUrl() .'/'. 
                            $this->getRequest()->getControllerName() .'/index');

        $filter->setValue( $this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add",
                                          "display" => $this->view->translate("Add Cost Center"),
                                          "css" => "include") );
    }

    /**
     * Add new Cost Center's
     */

    // @todo validação javascript do campo
    
    public function addAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Cost Center"),
            $this->view->translate("Add")
        ));

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form( new Zend_Config_Xml( "modules/default/forms//cost_center.xml" ) );
        
        if($this->_request->getPost()) {
            
            $form_isValid = $form->isValid($_POST);

            $newId = new Snep_CostCenter_Manager();
            $select = $newId->select()->where('cd_code = ?', $_POST['id']);
            $cost_center = $newId->fetchRow($select);

            if( count( $cost_center ) > 1) {
                $form_isValid = false;
                $form->getElement('id')->addError( $this->view->translate('Code already exists.') );
            }

            if( $form_isValid ) {
                $data = array('cd_code' => $_POST['id'],
                              'ds_name' => $_POST['name'],
                              'cd_type' => $_POST['type'],
                              'ds_description' => $_POST['description'],
                              'id_carrier' => null);

                $newId->insert($data);
                $this->_redirect( $this->getRequest()->getControllerName() );
            }
        }

        $this->view->form = $form;
    }

    /**
     * Remove Cost Center's
     */
    public function removeAction() {
        
        $id = $this->_request->getParam('id');
        $cost_center = new Snep_CostCenter_Manager();
        $cost_center->delete("id_costcenter = $id");
    }

    /**
    * Edit Cost Center's
    */
    public function editAction() {

        $id = $this->_request->getParam('id');
        
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Cost Center"),
            $this->view->translate("Edit")
        ));

        $obj = new Snep_CostCenter_Manager();
        $select = $obj->select()->where('id_costcenter = ?', $id);
        $cost_center = $obj->fetchRow($select)->toArray();

        Zend_Registry::set('cancel_url', 
                           $this->getFrontController()->getBaseUrl() . '/' .
                           $this->getRequest()->getControllerName() . '/index');

        $form = new Snep_Form( new Zend_Config_Xml( "modules/default/forms/cost_center.xml" ) );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. 
                          $this->getRequest()->getControllerName() . '/edit/id/'.$id );

        $idcc = new Zend_Form_Element_Hidden('id_costcenter');
        $idcc->setValue($cost_center['id_costcenter']);

        $form->getElement('id')->setValue( $cost_center['cd_code'] )->setAttrib('readonly', true);
        
        $form->getElement('name')->setValue( $cost_center['ds_name'] );
        $form->getElement('description')->setValue( $cost_center['ds_description'] );
        $form->getElement('type')->setValue( $cost_center['cd_type'] );
        
        $form->addElement($idcc);

        if($this->_request->getPost()) {

                $_POST['id'] = trim($_POST['id']);
            
                $form_isValid = $form->isValid($_POST);

                $data = array('cd_code' => trim($_POST['id']),
                              'ds_name' => $_POST['name'],
                              'cd_type' => $_POST['type'],
                              'ds_description' => $_POST['description'],
                              'id_carrier' => null);

                if($form_isValid) {

                    $costCenter = new Snep_CostCenter_Manager();
                    $costCenter->update($data, "id_costcenter = {$_POST['id_costcenter']}");
                    
                    $this->_redirect( $this->getRequest()->getControllerName() );
                    
                }
        }

        $this->view->form = $form;
    }
}