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
 * Users Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2012 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */
class UsersController extends Zend_Controller_Action {


    public function indexAction()
    {

        // @todo localização das datas na listagem da view

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Users")
        ));

        $this->view->url = $this->getFrontController()->getBaseUrl() .'/'.
                           $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from("user");

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("$field LIKE '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->users = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array('ds_login'     => $this->view->translate('Name'),
                        'ds_mail'      => $this->view->translate('E-mail') );

        $this->view->active = array(1 => $this->view->translate('Yes'),
                                    0 => $this->view->translate('No'));

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Add User"),
                                          "css" => "include"));
        

    }

    public function addAction()
    {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Users"),
            $this->view->translate("Add User") ));

            $form = new Snep_Form( new Zend_Config_Xml('./modules/default/forms/user.xml') );
            $form->getElement('fg_active')->setValue(1);

            if ($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);

                $user = new Snep_User_Manager();
                $select = $user->select()->where('ds_login = ?', $_POST['ds_login']);
                $db_user = $user->fetchRow($select);

                if( $db_user) {
                    $form_isValid = false;
                    $form->getElement('ds_login')->addError( $this->view->translate('Nome de usuário já existe.') );
                }

                if ($form_isValid) {
                    $pass = md5($_POST['cd_password']);
                    $dados = array('ds_login' => $_POST['ds_login'],
                                   'cd_password' => $pass,
                                   'dt_lastlogin' => null,
                                   'ds_mail' => $_POST['ds_mail'],
                                   'fg_active' => $_POST['fg_active'],
                                   'id_profile' => null );
                    $user->insert($dados);
                    $this->_redirect($this->getRequest()->getControllerName());
                }
            }         
            
            $this->view->form = $form;
    }

    public function editAction()
    {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Users"),
            $this->view->translate("Edit User") ));

            $id = $this->_request->getParam("id");

            $obj = new Snep_User_Manager();
            $select = $obj->select()->where("id_user = ?", $id);
            $user = $obj->fetchRow($select)->toArray();

            $form = new Snep_Form( new Zend_Config_Xml('./modules/default/forms/user.xml') );

            $form->getElement('id')->setValue($user['id_user']);
            $form->getElement('ds_login')->setValue($user['ds_login']);
            $form->getElement('ds_mail')->setValue($user['ds_mail']);
            $form->getElement('fg_active')->setValue($user['fg_active']);

            if ($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);

                if ($form_isValid) {

                    $dados = array('ds_login' => $_POST['ds_login'],
                                   'dt_lastlogin' => $user['dt_lastlogin'],
                                   'ds_mail' => $_POST['ds_mail'],
                                   'fg_active' => $_POST['fg_active'],
                                   'id_profile' => null );

                    if($_POST['cd_password'] != '') {
                        $new_pass = md5($_POST['cd_password']);

                        if($user['cd_password'] != $new_pass) {
                            $dados['cd_password'] = md5($_POST['cd_password']);
                        }
                    }
                    
                    $obj->update($dados, "id_user = '{$id}'");
                    $this->_redirect($this->getRequest()->getControllerName());
                }
            }

            $this->view->form = $form;
    }

    public function removeAction()
    {
        $id = $this->_request->getParam('id');
        $cost_center = new Snep_User_Manager();
        $cost_center->delete("id_user = $id");
    }
    
}
?>
