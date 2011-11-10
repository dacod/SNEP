<?php

require_once 'Zend/Session.php';
require_once 'Zend/Application/Bootstrap/Bootstrap.php';
require_once 'Snep/Locale.php';

Zend_Session::start();

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Adds user role on Snep Acl and register plugin for permission check before
     * dispatch.
     */
    protected function _initAcl() {
        $acl = Snep_Acl::getInstance();

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $acl->addRole((string) $auth->getIdentity());
            $role = $auth->getIdentity();
            if($role == "admin") {
                $acl->allow("admin");
            }
        } else {
            $role = 'guest';
        }

        $front = Zend_Controller_Front::getInstance();
        require_once 'modules/default/model/AclPlugin.php';
        $front->registerPlugin(new AclPlugin($acl, $role));
    }

    protected function _initRouter() {
        $front_controller = Zend_Controller_Front::getInstance();
        $front_controller->setBaseUrl($_SERVER['SCRIPT_NAME']);

        $router = $front_controller->getRouter();
        $router->addRoute('route_edit',
                new Zend_Controller_Router_Route('route/edit/:id', array('controller' => 'route', 'action' => 'edit'))
        );
        $router->addRoute('route_duplicate',
                new Zend_Controller_Router_Route('route/duplicate/:id', array('controller' => 'route', 'action' => 'duplicate'))
        );
        $router->addRoute('route_delete',
                new Zend_Controller_Router_Route('route/delete/:id', array('controller' => 'route', 'action' => 'delete'))
        );
        $router->addRoute('route_permission',
                new Zend_Controller_Router_Route('permission/:exten', array('controller' => 'permission', 'action' => 'index'))
        );
    }

    protected function _initLocale() {
        $locale = Snep_Locale::getInstance();
        Zend_Registry::set("i18n", $locale->getZendTranslate());
    }

    /**
     * Starts the system view and layout
     *
     * @return Zend_View
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
        $view->headScript()->appendFile($view->baseUrl() . "/includes/javascript/snep-env.js.php", 'text/javascript');
        $view->headScript()->appendFile($view->baseUrl() . "/includes/javascript/prototype.js", 'text/javascript');
        $view->headScript()->appendFile($view->baseUrl() . "/includes/javascript/functions.js", 'text/javascript');

        // Return it, so that it can be stored by the bootstrap
        return $view;
    }

    protected function _initCCustos() {
        $db = Snep_Db::getInstance();
        $ccustos = Snep_CentroCustos::getInstance();

        $select = $db->select()
                  ->from('ccustos')
                  ->order("codigo");

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        foreach($result as $ccusto) {
            $ccustos->register(array("codigo" => $ccusto['codigo'], "nome" => $ccusto['nome']));
        }
    }

    protected function _initQueues() {
        $db = Snep_Db::getInstance();
        $queues = Snep_Queues::getInstance();

        $select = $db->select()->from('queues');

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        foreach($result as $queue) {
            $queues->register($queue['name']);
        }
    }
    
    protected function _initLogger() {
        $log = Snep_Logger::getInstance();
        
        $config = Snep_Config::getConfig();
        
        $writer = new Zend_Log_Writer_Stream($config->system->path->log . '/ui.log');
        // Filtramos a 'sujeira' dos logs se não estamos em debug mode.
        if(!$config->system->debug) {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::WARN);
            $writer->addFilter($filter);
        }
        $log->addWriter($writer);
    }

}
