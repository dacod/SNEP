<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */
require_once 'Zend/Controller/Action.php';

/**
 * Authentication Controll
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli <henrique@opens.com.br>
 */
class AuthController extends Zend_Controller_Action {

    public function loginAction() {
        $this->view->headTitle($this->view->translate("Login"));
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Login")
        ));
        $this->view->hideMenu = true;

        $config = Zend_Registry::get('config');
        if( trim ( $config->ambiente->db->host ) == "" ) {
            if(Zend_Auth::getInstance()->hasIdentity()) {
                Zend_Auth::getInstance()->clearIdentity();
            }
            $this->_redirect("installer/");
        }
        
        // Não precisamos fazer login se ja estamos logados
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) {
            $this->_redirect('/');
        }

        if ($this->_request->isPost()) {
            // Filtrando informações do usuário
            $f = new Zend_Filter_StripTags();
            $username = $f->filter($this->_request->getPost('exten'));
            $password = $this->_request->getPost('password');

            if (empty($username)) {
                $this->view->message = $this->view->translate("Please insert a username");
                $this->view->msgclass = 'failure';
            }
            else {
                $db = Zend_Registry::get('db');

                // criando adaptador de autorização
                $authAdapter = new Zend_Auth_Adapter_DbTable($db);

                // informações das tabelas
                $authAdapter->setTableName('user');
                $authAdapter->setIdentityColumn('ds_login');
                $authAdapter->setCredentialColumn('cd_password');

                // Valores vindos do usuário como credencial
                $authAdapter->setIdentity($username);
                $authAdapter->setCredential($password);

                // autenticação
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                // tratando resultados
                switch ($result->getCode()) {
                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $this->view->message = $this->view->translate('User or password invalid');
                        $this->view->msgclass = 'failure';
                        break;
                    case Zend_Auth_Result::SUCCESS:
                        $auth->getStorage()->write($result->getIdentity());

                        $extension = $db->query("SELECT id, callerid FROM peers WHERE name='$username'")->fetchObject();
                        
                        $select = $db->select();
                        
                        $select->from('user', array('id_user', 'ds_login'))
                                ->where("ds_login = '$username'");
                        
                        $extension = $db->query($select)->fetchObject();
          
                        /* Mantendo antigo verifica.php no ar */
                        $_SESSION['id_user'] = $extension->id_user;
                        $_SESSION['name_user'] = $username;
                        $_SESSION['active_user'] = $extension->ds_login;
                        $_SESSION['vinculos_user'] = "";

                        $this->_redirect('/');
                        break;
                    default:
                        $this->view->message = $this->view->translate('Failed to authenticate');
                        $this->view->msgclass = 'failure';
                        break;
                }
            }
        }

        $layout = Zend_Layout::getMvcInstance();        
        $layout->setLayout('login');
        
    }

    public function logoutAction() {
        if(Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Auth::getInstance()->clearIdentity();
        }
        $this->_redirect("auth/login");
    }
}
