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
class TrunksController extends Zend_Controller_Action {

    public function indexAction() {

        require_once( APPLICATION_PATH . "/includes/classes.php");

        $this->view->breadcrumb = $this->view->translate("Cadastro » Troncos");

        $db = Zend_Registry::get('db');

        $select =   "SELECT t.id, t.callerid, t.name, t.type, t.trunktype, t.time_chargeby, t.time_total, th.used
                     FROM trunks as t
                     LEFT JOIN time_history as th
                     ON t.id=th.owner and th.owner_type='T'
                     GROUP BY t.id
                    ";

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );

        $this->view->filtro = $this->_request->getParam('filtro');

        $datasql = $db->query($select);
        $trunks = $datasql->fetchAll();

        foreach ($trunks as $id => $val) {

            if ( $val['time_total'] == '' ) {
                
                $trunks[$id]['saldo'] = $this->view->translate("Não Configurado");

            } else {

                $saldo = $val['time_total'] - $val['used'];
                $trunks[$id]['saldo'] = sprintf("%d:%02d",floor($saldo/60), $saldo%60);

            }
        }

        $paginatorAdapter = new Zend_Paginator_Adapter_Array($trunks);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->trunks = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "/snep/index.php/trunks/index/";

        $opcoes = array("name" => $this->view->translate("Código"),
            "callerid" => $this->view->translate("Nome")
        );

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "/snep/src/troncos.php",
                "display" => $this->view->translate("Incluir Tronco"),
                "css" => "include"),
        );
    }

}
