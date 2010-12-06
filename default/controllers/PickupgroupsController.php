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



class PickupgroupsController extends Zend_Controller_Action {

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Captura");
        
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from("grupos");
                        
        if( $this->_request->getPost('filtro') ) {
            $field = mysql_escape_string( $this->_request->getPost('campo') );
            $query = mysql_escape_string( $this->_request->getPost('filtro') );
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page)  ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');
        
        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber( $this->view->page );
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->pickupgroups = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "/snep/index.php/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("cod_grupo"      => $this->view->translate("Código"),
                        "nome"        => $this->view->translate("Nome") );

        // Formulário de filtro.
        $config_file = "./default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);

        $filter = new Zend_Form( $config->filter );
        $filter->setAction( $this->getFrontController()->getBaseUrl() . '/'. $this->getRequest()->getControllerName() .'/index' );

        $filter_value = $filter->getElement('filtro');
        $filter_value->setValue( $this->_request->getPost('filtro') );

        $submit = new Zend_Form_Element_Submit("submit", array("label" => $this->view->translate("Procurar")));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator('HtmlTag', array('tag' => 'dd'));

        // Botão Lista Completa
        $reset = new Zend_Form_Element_Button("buttom", array("label" => $this->view->translate("Lista Completa") ) );
        $reset->setAttrib("onclick", "location.href='{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page'");
        $reset->removeDecorator('DtDdWrapper');
        $reset->addDecorator('HtmlTag', array('tag' => 'dd'));

        // Define elementos do 'campo' select
        $campo = $filter->getElement('campo');
        $campo->setMultiOptions( $opcoes );

        $filtro = $filter->getElement('filtro');
        $filtro->setValue( $this->_request->getParam('filtro') );
        
        $filter->addElement( $submit);
        $filter->addElement( $reset );

        $this->view->form_filter = $filter;
        $this->view->filter = array( array("url" => "/snep/src/grupos.php",
                                           "display" => $this->view->translate("Incluir Grupos de Captura"),
                                           "css" => "include")
                                   );
        
    }

}
