<?php

require_once 'Zend/Session.php';
require_once 'Zend/Application/Bootstrap/Bootstrap.php';
require_once 'default/model/AclPlugin.php';

Zend_Session::start();

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Inicia o sistema de permissões do snep client.
     */
    protected function _initAcl() {
        $acl = new Zend_Acl();

        // Main roles
        $acl->addRole(new Zend_Acl_Role('all')); // Everyone
        $acl->addRole(new Zend_Acl_Role('users'),'all'); // Users
        $acl->addRole(new Zend_Acl_Role('guest'),'all'); // Non authenticated users

        // Dynamic roles
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) {
            // Already authenticated user
            $acl->addRole(new Zend_Acl_Role($auth->getIdentity()),'users');
        }

        // System resources
        $acl->add(new Zend_Acl_Resource('default'));
        $acl->add(new Zend_Acl_Resource('error'));
        $acl->add(new Zend_Acl_Resource('index'));
        $acl->add(new Zend_Acl_Resource('auth'));
        $acl->add(new Zend_Acl_Resource('installer'));

        // Default permissions
        $acl->deny('all');
        $acl->allow('users');

        $acl->allow(null,'auth');
        $acl->allow(null,'error');
        $acl->allow(null,'installer');

        $this->acl = $acl;

        $front = Zend_Controller_Front::getInstance();

        // Defining Role
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) {
            $role = $auth->getIdentity();
        }
        else {
            $role = 'guest';
        }

        $front->registerPlugin(new AclPlugin($this->acl, $role));
    }

    /**
     * Efetua a correção da baseUrl para os links adicionando index.php para que
     * o sistema funcione mesmo que o servidor não esteja com mod_rewrite
     * habilitado
     */
    protected function _initRouter() {
        $front_controller = Zend_Controller_Front::getInstance();
        $front_controller->setBaseUrl($_SERVER['SCRIPT_NAME']);
    }

    /**
     * Inicia o doctype e outros parametros do layout.
     *
     * @return Bootstrap
     */
    protected function _initViewHelpers() {
        // Initialize view
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();

        $view->doctype('HTML5');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle('SNEP');

        $view->headLink()->setStylesheet($view->baseUrl() . "/css/main.css");
        $view->headScript()->appendFile($view->baseUrl() . "/includes/javascript/prototype.js", 'text/javascript');
        $view->headScript()->appendFile($view->baseUrl() . "/includes/javascript/functions.js", 'text/javascript');

        // Return it, so that it can be stored by the bootstrap
        return $view;
    }

    protected function _initSnep() {
        $snepBoot = new Snep_Bootstrap_Web("includes/setup.conf");
        $snepBoot->specialBoot();

        $layout = $this->getResource('layout');
        $view = $layout->getView();
        $view->setScriptPath('./default/views/scripts');

        $view->menu = Zend_Registry::get('menu');
        $view->menu->setId("navmenu");
    }
}
