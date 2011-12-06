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
 * Carrier Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class CarrierController extends Zend_Controller_Action {

    /**
     * List all Carrier
     */
    public function indexAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Carrier")
        ));

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from("operadoras")
                        ->order('nome');
                 
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

        $this->view->carrier = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("codigo"      => $this->view->translate("Code"),
                        "nome"        => $this->view->translate("Name"));

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Add Carrier"),
                                          "css"     => "include"));
    }

    /**
     *  Add Carrier
     */
    public function addAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Add")
        ));

        $this->view->objSelectBox = "carrier";

        $xml = new Zend_Config_Xml( "modules/default/forms/carrier.xml" );
        $form = new Snep_Form( $xml );

        $_idleCostCenter = Snep_Carrier_Manager::getIdleCostCenter();
        $idleCostCenter = array();
        foreach($_idleCostCenter as $idle) {
            $idleCostCenter[$idle['codigo']] = $idle['codigo'] ." : ". $idle['tipo'] ." - ". $idle['nome'];
        }
        if($idleCostCenter) {
            $form->setSelectBox( $this->view->objSelectBox, $this->view->translate('Cost Center'), $idleCostCenter);
        }

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if( $form_isValid ) {
                    $idCarrier = Snep_Carrier_Manager::add( $dados );

                    foreach($dados['box_add'] as $costCenter) {
                        Snep_Carrier_Manager::setCostCenter( $idCarrier, $costCenter );
                    }                    
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }
        $this->view->form = $form;

    }

    /**
     * Edit Carrier
     */
    public function editAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Edit")
        ));

        $this->view->objSelectBox = "carrier";
        $id = $this->_request->getParam("id");

        $xml = new Zend_Config_Xml( "modules/default/forms/carrier.xml" );
        $carrier = Snep_Carrier_Manager::get($id);

        $form = new Snep_Form( $xml );        
        $form->getElement('name')->setValue($carrier['nome']);
        $form->getElement('ta')->setValue($carrier['tpm']);
        $form->getElement('tf')->setValue($carrier['tdm']);
        $form->getElement('tbf')->setValue($carrier['tbf']);
        $form->getElement('tbc')->setValue($carrier['tbc']);

        $_idleCostCenter = Snep_Carrier_Manager::getIdleCostCenter();
        $idleCostCenter = array();
        foreach($_idleCostCenter as $idle) {
            $idleCostCenter[$idle['codigo']] = $idle['codigo'] ." : ". $idle['tipo'] ." - ". $idle['nome'];
        }

        if( isset( $id )) {
            $_selectedCostCenter = Snep_Carrier_Manager::getCarrierCostCenter( $id );
            $selectedCostCenter = array();
            foreach($_selectedCostCenter as $selected) {
                $selectedCostCenter[$selected['codigo']] = $selected['codigo'] ." : ". $selected['tipo'] ." - ". $selected['nome'];
            }            
        }

        $form->setSelectBox( $this->view->objSelectBox,
                             $this->view->translate('Cost Center'),
                             $idleCostCenter,                             
                             $selectedCostCenter );

        $formId = new Zend_Form_Element_Hidden('id');
        $formId->setValue($id);
        
        $form->addElement($formId);

        if($this->_request->getPost()) {
                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid) {

                    Snep_Carrier_Manager::edit($dados);
                    if($dados['box_add']) {                        
                        Snep_Carrier_Manager::clearCostCenter($dados['id']);
                        foreach($dados['box_add'] as $costCenter) {
                            Snep_Carrier_Manager::setCostCenter( $dados['id'], $costCenter );
                        }
                    }
                    
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a Carrier
     */
    public function removeAction() {

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Delete")
       ));

       $id = $this->_request->getParam('id');

       Snep_Carrier_Manager::remove($id);
       
       $this->_redirect( $this->getRequest()->getControllerName() );

    }
    
}
