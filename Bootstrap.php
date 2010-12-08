<?php

require_once 'Zend/Session.php';
require_once 'Zend/Application/Bootstrap/Bootstrap.php';
require_once 'default/model/AclPlugin.php';

Zend_Session::start();

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

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

        $config = Snep_Config::getConfig();
        $baseurl = $view->getHelper("baseUrl");
        $baseurl->setBaseUrl($config->system->path->web);

        $view->doctype('HTML5');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle('SNEP');

        $view->headLink()->setStylesheet($view->baseUrl() . "/css/main.css");
        $view->headScript()->appendFile($view->baseUrl() . "/includes/javascript/prototype.js", 'text/javascript');

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

    /**
     * Inicia o sistema de permissões do snep.
     *
     * Todas as roles e resources são definidas em sistema. Somente os controles
     * de acesso são definidos em banco de dados pela tabela de permissões.
     *
     * @TODO: Carregamento de resources de módulos.
     * @TODO: Carregar de banco as politicas de controle de acesso de usuário
     */
    protected function _initAcl() {
        $acl = new Snep_Acl();
        $config = Snep_Config::getConfig();

        // Recurso padrão caso um módulo não tenha nenhuma regra de acesso
        $acl->add(new Zend_Acl_Resource('unknown'));

        // Adicionando resources mapeados no resources.xml do módulo Default
        libxml_use_internal_errors(true);
        $resourcesXml = simplexml_load_file($config->system->path->base . "/configs/resources.xml");
        $acl->loadResourcesXml($resourcesXml);

        // Role para o super-usuário
        $acl->addRole(new Zend_Acl_Role('root'));
        // Role para usuários não autenticados
        $acl->addRole(new Zend_Acl_Role('guest'));

        // Adicionando roles de usuário
        $db = Snep_Db::getInstance();
        $roles = $db->fetchPairs("SELECT name, inherit FROM groups;");

        function addRole($role, $parent, $acl) {
            if(!$acl->hasRole($role)) {
                if(!$acl->hasRole($parent) && $parent !== null) {
                    addRole($parent, $roles['parent'], $acl);
                }
                $acl->addRole($role, $parent);
            }
        }

        foreach ($roles as $role => $parent) {
            addRole($role, $parent, $acl);
        }

        // Role de usuário caso este esteja logado
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $role = $auth->getIdentity();
            $extManager = new Snep_Extensions();
            $exten = $extManager->get($role);
            $acl->addRole(new Zend_Acl_Role($role), $exten->getGroup());
        } else {
            $role = "guest";
        }

        /* Políticas de controle de acesso */
        if($acl->hasRole("admin")) {
            $acl->allow('admin');
        }

        // Plugin que controla o acesso automaticamente para module e controller
        Zend_Controller_Front::getInstance()->registerPlugin(new AclPlugin($acl, $role));
    }

}
