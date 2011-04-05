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

        $this->view->breadcrumb = $this->view->translate("Cadastro » Centro de Custos");

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from("ccustos", array("codigo", "tipo", "nome", "descricao"));

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $this->view->types = array('E' => $this->view->translate('Entrada'),
                                   'S' => $this->view->translate('Saída'),
                                   'O' => $this->view->translate('Outras'));

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->costcenter = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("codigo" => $this->view->translate("Código"),
                        "tipo" => $this->view->translate("Tipo"),
                        "nome" => $this->view->translate("Nome"),
                        "descricao" => $this->view->translate("Descrição")
        );
        
        $filter = new Snep_Form_Filter();
        $filter->setAction( $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue( $this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add",
                                          "display" => $this->view->translate("Incluir Centro de Custos"),
                                          "css" => "include"),
        );
    }

    /**
     * Add new Cost Center's
     */
    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Centro de Custos » Cadastro");

        $db = Zend_Registry::get('db');

        $xml = new Zend_Config_Xml( "default/forms/cost_center.xml" );

        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/add');

        $id = $form->getElement('id')->setLabel( $this->view->translate('Código') )
                                     ->setDescription('Somente Números');

        $name = $form->getElement('name')->setLabel( $this->view->translate('Nome') );

        $type = $form->getElement('type');
        $type->setRequired(true)
             ->setLabel($this->view->translate('Tipo'))
             ->setMultiOptions(array('E' => $this->view->translate('Entrada'),
                                     'S'=> $this->view->translate('Saída'),
                                     'O'=> $this->view->translate('Outras')) );

        $description = $form->getElement('description')->setLabel( $this->view->translate('Descrição') );

        $form->setButtom();

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid){

                    $dados = $this->_request->getParams();
                    Snep_CostCenter_Manager::add($dados);                    
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }        
        $this->view->form = $form;
    }

    /**
     * Remove Cost Center's
     */
    public function removeAction() {

        $this->view->breadcrumb = $this->view->translate("Centro de Custos » Remover");

        $db = Zend_Registry::get('db');
        $id = $this->_request->getParam('id');

        Snep_CostCenter_Manager::remove($id);

    }

    /**
    * Edit Cost Center's
    */
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Centro de Custos » Cadastro");

        $db = Zend_Registry::get('db');
        $id = $this->_request->getParam('id');

        $xml = new Zend_Config_Xml( "default/forms/cost_center.xml" );

        $costCenter = Snep_CostCenter_Manager::get($id);

        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/edit/id/'.$id);

        $id = $form->getElement('id')
                   ->setLabel( $this->view->translate('Código') )
                   ->setValue($costCenter['codigo'])                   
                   ->setAttrib('readonly', true);

        $name = $form->getElement('name')
                     ->setLabel( $this->view->translate('Nome') )
                     ->setValue($costCenter['nome']);

        $type = $form->getElement('type');

        $type->setRequired(true)
             ->setLabel($this->view->translate('Tipo'))
             ->setMultiOptions(array('E' => $this->view->translate('Entrada'),
                                     'S'=> $this->view->translate('Saída'),
                                     'O'=> $this->view->translate('Outras')) )
             ->setValue($costCenter['tipo']);

        $description = $form->getElement('description')
                            ->setLabel( $this->view->translate('Descrição') )
                            ->setValue( $costCenter['descricao']);

        $form->setButtom();

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid) {

                    Snep_CostCenter_Manager::edit($dados);
                    $this->_redirect( $this->getRequest()->getControllerName() );
                    
                }
        }

        $this->view->form = $form;
    }

}