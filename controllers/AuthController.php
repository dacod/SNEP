<?php

require_once 'Zend/Controller/Action.php';

class AuthController extends Zend_Controller_Action {

    public function loginAction() {
        $this->view->headTitle($this->view->translate("Login"));
        $this->view->breadcrumb = $this->view->translate("Login");
        
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
                $this->view->message = $this->view->translate("Por favor insira um nome de usuário");
                $this->view->msgclass = 'failure';
            }
            else {
                $db = Zend_Registry::get('db');

                // criando adaptador de autorização
                $authAdapter = new Zend_Auth_Adapter_DbTable($db);

                // informações das tabelas
                $authAdapter->setTableName('peers');
                $authAdapter->setIdentityColumn('name');
                $authAdapter->setCredentialColumn('password');

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
                        $this->view->message = $this->view->translate('Usuário ou senha inválida');
                        $this->view->msgclass = 'failure';
                        break;
                    case Zend_Auth_Result::SUCCESS:
                        $auth->getStorage()->write($result->getIdentity());

                        $extension = $db->query("SELECT id, callerid FROM peers WHERE name='$username'")->fetchObject();
                        
                        /* Mantendo antigo verifica.php no ar */
                        $_SESSION['id_user'] = $extension->id;
                        $_SESSION['name_user'] = $username;
                        $_SESSION['active_user'] = $extension->callerid;
                        $_SESSION['vinculos_user'] = "";

                        $this->_redirect('/');
                        break;
                    default:
                        $this->view->message = $this->view->translate('Falha na autenticação');
                        $this->view->msgclass = 'failure';
                        break;
                }
            }
        }
    }

    public function logoutAction() {
        if(Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Auth::getInstance()->clearIdentity();
        }
        $this->_redirect("auth/login");
    }
}
