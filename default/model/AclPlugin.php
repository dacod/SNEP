<?php

class AclPlugin extends Zend_Controller_Plugin_Abstract {

    /**
     * @var Zend_Acl
     **/
    protected $_acl;

    /**
     * @var string
     **/
    protected $_roleName;

    /**
     * @var array
     **/
    protected $_errorPage;

    /**
     * Constructor
     *
     * @param mixed $aclData
     * @param $roleName
     * @return void
     **/
    public function __construct(Zend_Acl $aclData, $roleName = 'guest') {
        $this->_errorPage = array(
                'module' => 'default',
                'controller' => 'error',
                'action' => 'denied'
        );

        $this->_roleName = $roleName;

        if (null !== $aclData) {
            $this->setAcl($aclData);
        }
    }

    /**
     * Sets the ACL object
     *
     * @param mixed $aclData
     * @return void
     **/
    public function setAcl(Zend_Acl $aclData) {
        $this->_acl = $aclData;
    }

    /**
     * Returns the ACL object
     *
     * @return Zend_Acl
     **/
    public function getAcl() {
        return $this->_acl;
    }

    /**
     * Sets the ACL role to use
     *
     * @param string $roleName
     * @return void
     **/
    public function setRoleName($roleName) {
        $this->_roleName = $roleName;
    }

    /**
     * Returns the ACL role used
     *
     * @return string
     * @author
     **/
    public function getRoleName() {
        return $this->_roleName;
    }

    /**
     * Sets the error page
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @return void
     **/
    public function setErrorPage($action, $controller = 'error', $module = null) {
        $this->_errorPage = array('module' => $module,
                'controller' => $controller,
                'action' => $action);
    }

    /**
     * Returns the error page
     *
     * @return array
     **/
    public function getErrorPage() {
        return $this->_errorPage;
    }

    /**
     * Predispatch
     * Checks if the current user identified by roleName has rights to the requested url (module/controller/action)
     * If not, it will call denyAccess to be redirected to errorPage
     *
     * @return void
     **/
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $resource = "unknown";
        $acl = $this->getAcl();

        $module = $request->getModuleName();
        if( $acl->has($module) ) {
            $controller = $request->getControllerName();
            $resource = $acl->has($controller) ? $controller : $module;
        }

        if(!$acl->isAllowed($this->getRoleName(), $resource)) {
            if(Zend_Auth::getInstance()->hasIdentity()) {
                $this->denyAccess();
            }
            else {
                $redirector = new Zend_Controller_Action_Helper_Redirector();
                $redirector->direct("login", "auth", "default");
            }
        }
    }

    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function denyAccess() {
        $this->_request->setModuleName($this->_errorPage['module']);
        $this->_request->setControllerName($this->_errorPage['controller']);
        $this->_request->setActionName($this->_errorPage['action']);
    }

}
