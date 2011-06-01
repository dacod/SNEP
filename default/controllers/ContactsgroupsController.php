<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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



class ContactsgroupsController extends Zend_Controller_Action {

    public function indexAction() {
       
        $this->view->breadcrumb = $this->view->translate("Cadastro » Grupos de Contatos");
        
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from("contacts_group");

        if( $this->_request->getPost('filtro') ) {
            $field = mysql_escape_string( $this->_request->getPost('campo') );
            $query = mysql_escape_string( $this->_request->getPost('filtro') );
            $select->where("`$field` like '%$query%'");
        }

        $stmt = $db->query($select);
        $resultado = $stmt->fetchAll();

        echo "<pre>";
        $final = array();
        foreach($resultado as $num => $grupo) {

            $final[$num] = $grupo;
            $select = $db->select()
                    ->from('contacts_names', 'count(id) as numContacts')
                    ->where("contacts_names.group = '{$grupo['id']}'");
                    $result = $db->query($select)->fetch();
                    $final[$num]['numContacts'] = $result['numContacts'];
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page)  ? $page : 1 );

        $this->view->filtro = $this->_request->getParam('filtro');
        
        $paginatorAdapter = new Zend_Paginator_Adapter_Array($final);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber( $this->view->page );
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->contactsgroups = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "/snep/index.php/{$this->getRequest()->getControllerName()}/index/";
        
        $opcoes = array("id"        => $this->view->translate("Código"),
                        "name"      => $this->view->translate("Nome") );

	// Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array( array("url" => "/snep/src/contacts_group.php",
                                           "display" => $this->view->translate("Incluir Grupo de Contatos"),
                                           "css" => "include"),
                                   );
        
    }

}
