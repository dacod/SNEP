<?php

require_once 'Zend/Session.php';
require_once 'Zend/Application/Bootstrap/Bootstrap.php';

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
        require_once 'default/model/AclPlugin.php';
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
    }

    protected function _initLocale() {
        require_once "Zend/Translate.php";
        $config = Snep_Config::getConfig();
        // silenciando strict até arrumar zend_locale
        date_default_timezone_set("America/Sao_Paulo");

        $i18n = new Zend_Translate('gettext', $config->system->path->base . '/lang/pt_BR.mo', 'pt_BR');
        Zend_Registry::set('i18n', $i18n);

        $translation_files = $config->system->path->base . "/lang/";
        foreach( scandir($translation_files) as $filename ) {
            // Todos os arquivos .php devem ser classes de descrição de modulos
            if( preg_match("/.*\.mo$/", $filename) ) {
                $translation_id = basename($filename, '.mo');
                if($translation_id != "pt_BR") {
                    $i18n->addTranslation($translation_files . "/$filename", $translation_id);
                }
            }
        }

        require_once "Zend/Locale.php";

        if(Zend_Locale::isLocale($config->system->locale)) {
            $locale = $config->system->locale;
        } else {
            $locale = "pt_BR";
        }

        Zend_Registry::set('Zend_Locale', new Zend_Locale($locale));
        Zend_Locale::setDefault($locale);
        Zend_Locale_Format::setOptions(array("locale"=> $locale));
        $i18n->setLocale($locale);
        Zend_Registry::set("Zend_Translate", $i18n);

        $zend_validate_translator = new Zend_Translate_Adapter_Array(
            $config->system->path->base . "/lang/Zend_Validate/$locale/Zend_Validate.php",
            $locale
        );
        Zend_Validate_Abstract::setDefaultTranslator($zend_validate_translator);
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

}
