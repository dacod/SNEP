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
class TrunksController extends Zend_Controller_Action {

    public function indexAction() {

        require_once( APPLICATION_PATH . "/includes/classes.php");

        $this->view->breadcrumb = $this->view->translate("Cadastro » Troncos");

        $db = Zend_Registry::get('db');

        $select =   "SELECT t.id, t.callerid, t.name, t.type, t.trunktype, t.time_chargeby, t.time_total,
                            (
                                SELECT th.used
                                FROM time_history AS th
                                WHERE th.owner = t.id AND th.owner_type='T'
                                ORDER BY th.changed DESC limit 1
                            ) as used,
                            (
                                SELECT th.changed
                                FROM time_history AS th
                                WHERE th.owner = t.id AND th.owner_type='T'
                                ORDER BY th.changed DESC limit 1
                            ) as changed
                     FROM trunks as t ";
        
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

            if ( is_null($val['time_total'])) {

                $trunks[$id]['saldo'] = $this->view->translate("Não Configurado");

            } else {

                $ligacao = new Zend_Date($val['changed']);
                $anoLigacao = substr($ligacao,6,4);
                $mesLigacao = substr($ligacao,3,2);
                $diaLigacao = substr($ligacao,0,2);

                switch ($val['time_chargeby']) {

                    case 'Y':

                        if ($anoLigacao == date('Y')) {

                            $saldo = $val['time_total'] - $val['used'];

                            if ($val['used'] >= $val['time_total']) {

                                $saldo = 0;

                            }

                        } else {
                            
                            $saldo = $val['time_total'];
                            
                        }

                        break;

                    case 'M':

                        if ($anoLigacao == date('Y') && $mesLigacao == date('m')) {

                            $saldo = $val['time_total'] - $val['used'];

                            if ($val['used'] >= $val['time_total']) {

                                $saldo = 0;

                            }

                        } else {

                            $saldo = $val['time_total'];

                        }

                        break;

                    case 'D':

                        if ($anoLigacao == date('Y') && $mesLigacao == date('m') && $diaLigacao == date('d')) {

                            $saldo = $val['time_total'] - $val['used'];

                        } else {

                            $saldo = $val['time_total'];

                        }

                        break;

                }

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
