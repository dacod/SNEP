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
 * Billing Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class BillingController extends Zend_Controller_Action {

    /**
     * List all Billing
     */
    public function indexAction() {
        
        $this->view->breadcrumb = $this->view->translate("Cadastro » Tarifas");

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from("tarifas_valores", array('DATE_FORMAT(data,\'%d/%m/%Y %T\') as data', 'vcel', 'vfix'))
                        ->from("tarifas")
                        ->from("operadoras", array('nome'))                        
                        ->where("operadoras.codigo = tarifas.operadora")
                        ->where("tarifas_valores.codigo = tarifas.codigo");
         

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("n.`$field` like '%$query%'");
        }
        
        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect( $select );
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->billing = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("nome"      => $this->view->translate("Código"),
                        "cidade"    => $this->view->translate("Cidade"),
                        "ddd"       => $this->view->translate("DDD") );

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Incluir Tarifa"),
                                          "css"     => "include"));
    }

    /**
     *  Add Billing
     */
    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Tarifas » Cadastro");

        $db = Zend_Registry::get('db');

        /*
        $xml = new Zend_Config_Xml( "default/forms/billing.xml" );
        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/add');
        */

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $carrier = $form->getElement('carrier');
        $carrier->setLabel( $this->view->translate('Operadora') );
        $_carriers = Snep_Carrier_Manager::getAll();
        foreach($_carriers as $_carrier) {
                $carriers[$_carrier['codigo']] = $_carrier['nome'];
        }
        $this->view->carriers = $carriers;

        $_states = Snep_Billing_Manager::getStates();
        foreach($_states as $state) {
            $states[$state['cod']] = $state['name'];
        }
        $this->view->states = $states;

        /* Formulário Snep_Form.
         *
         *
        if($_carriers) {
            foreach($_carriers as $_carrier) {
                $carriers[$_carrier['codigo']] = $_carrier['nome'];
            }
            $carrier->setMultiOptions( $carriers );
        }else{
            $carrier->setDescription( $this->view->translate('Não existem operadoras.') );
            $carrier->setAttrib('readonly', true);            
        }

        $country = $form->getElement('country');
        $country->setLabel( $this->view->translate('País') );

        $_states = Snep_Billing_Manager::getStates();

        foreach($_states as $state) {
            $states[$state['cod']] = $state['name'];
        }
        $state = $form->getElement('state');
        $state->setLabel( $this->view->translate('Estado') );
        $state->setMultiOptions($states);

        $city = $form->getElement('city');
        $city->setLabel( $this->view->translate('Cidade') );
        $city->setRegisterInArrayValidator(false);

        $country_code = $form->getElement('country_code');
        $country_code->setLabel( $this->view->translate('Código País') );

        $city_code = $form->getElement('city_code');
        $city_code->setLabel( $this->view->translate('Código Cidade') );

        $prefix = $form->getElement('prefix');
        $prefix->setLabel( $this->view->translate('Prefixo') );

        $tbf = $form->getElement('tbf');
        $tbf->setLabel( $this->view->translate('Tarifa Base Fixo') );

        $tbc = $form->getElement('tbc');
        $tbc->setLabel( $this->view->translate('Tarifa Base Celular') );
        
        $form->setButtom();
        */

        if($this->_request->getPost()) {

            $dados = array('data'         => $_POST['data'],
                           'carrier'      => $_POST['operadora'],
                           'country_code' => $_POST['ddi'],
                           'country'      => $_POST['pais'],
                           'city_code'    => $_POST['ddd'],
                           'city'         => $_POST['cidade'],
                           'state'        => $_POST['estado'],
                           'prefix'       => $_POST['prefixo'],
                           'tbf'          => $_POST['vfix'],
                           'tbc'          => $_POST['vcel'] );

            Snep_Billing_Manager::add( $dados );

            $this->_redirect( $this->getRequest()->getControllerName() );

            /*  Snep_form
             *
                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if( $form_isValid ) {
                    $idCarrier = Snep_Billing_Manager::add( $dados );
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }

             */
        }

        //$this->view->form = $form;

    }

    /**
     * Edit Billing
     */
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Tarifas » Editar");

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');

        $id = $this->_request->getParam("id");

        $_carriers = Snep_Carrier_Manager::getAll();
        foreach($_carriers as $_carrier) {
                $carriers[$_carrier['codigo']] = $_carrier['nome'];
        }
        $this->view->carriers = $carriers;

        $this->view->Carrier = Snep_Billing_Manager::get($id);

        $this->view->billingValues = Snep_Billing_Manager::getBillingValues($id);

        $_estado = Snep_Billing_Manager::getStates();
        foreach($_estado as $estado) {
            if($estado['cod'] == $this->view->Carrier['estado']) {
                $this->view->billingState = $estado;
            }            
        }

        /* Snep_Form
         *
        $this->view->objSelectBox = "billing";
        $xml = new Zend_Config_Xml( "default/forms/billing.xml" );
        $objCarrier = Snep_Billing_Manager::get($id);        
        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/edit');

        $carrier = $form->getElement('carrier');
        $carrier->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'leftForm', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND ));
        $carrier->setLabel( $this->view->translate('Operadora') );
        
        $_carriers = Snep_Carrier_Manager::getAll();
        if($_carriers) {
            foreach($_carriers as $_carrier) {
                $carriers[$_carrier['codigo']] = $_carrier['nome'];
            }
            $carrier->setMultiOptions( $carriers );
            $carrier->setAttrib('disable', true);
            $carrier->setValue($objCarrier['operadora']);
        }else{
            $carrier->setDescription( $this->view->translate('Não existem operadoras.') );
            $carrier->setAttrib('disable', true);
        }

        $country = $form->getElement('country');
        $country->setLabel( $this->view->translate('País') );
        $country->setValue($objCarrier['pais']);
        $country->setAttrib('disable', true);

        $_states = Snep_Billing_Manager::getStates();
        foreach($_states as $state) {
            $states[$state['cod']] = $state['name'];
        }        
        $state = $form->getElement('state');
        $state->setLabel( $this->view->translate('Estado') );
        $state->setMultiOptions($states);
        $state->setValue($objCarrier['estado']);
        $state->setAttrib('disable', true);

        $city = $form->getElement('city');
        $city->setLabel( $this->view->translate('Cidade') );
        $city->setMultiOptions( Snep_Billing_Manager::getCity($objCarrier['estado']) );
        $city->setValue($objCarrier['cidade']);
        $city->setAttrib('disable', true);

        $country_code = $form->getElement('country_code');
        $country_code->setLabel( $this->view->translate('Código País') );
        $country_code->setValue($objCarrier['ddi']);
        $country_code->setAttrib('readonly', true);

        $city_code = $form->getElement('city_code');
        $city_code->setLabel( $this->view->translate('Código Cidade') );
        $city_code->setValue($objCarrier['ddd']);
        $city_code->setAttrib('readonly', true);

        $prefix = $form->getElement('prefix');
        $prefix->setLabel( $this->view->translate('Prefixo') );
        $prefix->setValue($objCarrier['prefixo']);
        $prefix->addDecorator('HtmlTag', array('tag' => 'div', 'id' => 'selectActions', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $prefix->setAttrib('readonly', true);

        $form->removeElement('tbf');
        $form->removeElement('tbc');
        $formId = new Zend_Form_Element_Hidden('id');
        $formId->setValue($id);

        $billingValues = Snep_Billing_Manager::getBillingValues($id);

        $form->BillingValues($billingValues);
        
        $form->addElement($formId);
        $form->setButtom();
         *
         */

        if($this->_request->getPost()) {

                //$form_isValid = $form->isValid($_POST);
                //$dados = $this->_request->getParams();

                if( isset($_POST['action']) ) {
                    foreach( $_POST['action'] as $ida => $num ) {
                        if($num < count( $this->view->billingValues ) ) {
                            
                            $values = array('data' => $_POST['data'][$num],
                                            'vcel' => $_POST['vcel'][$num],
                                            'vfix' => $_POST['vfix'][$num] );
                  
                            Snep_Billing_Manager::editBilling($id, $values);


                        }else{
                            
                            $values = array('data' => $_POST['data'][$num],
                                            'vcel' => $_POST['vcel'][$num],
                                            'vfix' => $_POST['vfix'][$num] );
                       
                            Snep_Billing_Manager::addBilling( $id, $values );
                        }

                    }
                }

                $this->_redirect( $this->getRequest()->getControllerName() . '/edit/id/'. $id );

                /*
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
                * */
     
        }
        //$this->view->form = $form;
    }

    /**
     * Remove a Billing
     */
    public function removeAction() {

       $this->view->breadcrumb = $this->view->translate("Tarifas » Remover");
       $id = $this->_request->getParam('id');

       Snep_Billing_Manager::remove($id);
       
       $this->_redirect( $this->getRequest()->getControllerName() );

    }

    /**
     * Get cities from state
     * POST Array state
     */
    public function dataAction () {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $data = $_POST;
        
        if( isset( $data['state'] ) ) {
            $_states = Snep_Billing_Manager::getCity( $data['state'] );

            $states = array();
            foreach($_states as $state) {
                $states[] = $state['name'];              
            }
        }
        
        echo Zend_Json::encode($states);
    }

    /**
     * METODOS PALEATIVOS para adaptação da interface.
     */
    public function cidadeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $estado = isset($_POST['uf']) && $_POST['uf']!= "" ? $_POST['uf'] : display_error($LANG['msg_nostate'],true);
        $municipios = Snep_Cnl::get($estado);

        $options = '';
        if(count($municipios > 0)) {
            foreach($municipios as $cidades) {
                $options .= "<option  value='{$cidades['municipio']}' > {$cidades['municipio']} </option> " ;
            }
        }else{
                $options = "<option> {$LANG['select']} </option>";
        }

        echo $options;
    }
    
}