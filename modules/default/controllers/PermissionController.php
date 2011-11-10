<?php

class PermissionController extends Zend_Controller_Action {

    public function indexAction() {
        $exten = $this->getRequest()->getParam("exten");
        if ($exten === null) {
            throw new Zend_Controller_Action_Exception('Page not found.', 404);
        }

        try {
            PBX_Usuarios::get($exten);
        } catch (PBX_Exception_NotFound $ex) {
            throw new Zend_Controller_Action_Exception($this->view->translate("Extension %s does not exists.", $exten), 404);
        }

        $this->view->exten = $exten;

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    "Manage",
                    "Extensions",
                    "Permissions"
                ));

        $resources = array();
        foreach (Snep_Acl::getInstance()->getResources() as $resource) {
            $res_tree = explode("_", $resource);
            $resource = array();
            while ($element = array_pop($res_tree)) {
                $resource = array($element => $resource);
            }
            $resources = array_merge_recursive($resources, $resource);
        }
        $this->view->resources = $resources;
    }

}
