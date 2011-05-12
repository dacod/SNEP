<?php

require_once 'Zend/Controller/Action.php';

/**
 * Description of ErrorController
 *
 * @author guax
 */
class ErrorController extends Zend_Controller_Action {
    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->code = 404;
                $this->view->message = $this->view->translate("The page you are looking for was not found or does not exists.");
                $this->view->title = $this->view->translate("Not Found");
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->code = 500;
                $this->view->title = $this->view->translate("Internal Error");
                $this->view->sidebar = false;
                $this->view->message = $this->view->translate("Some internal error occured while processing your request. Please contact the system administrator and report this incident.");
                break;
        }

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Error"),
            $this->view->title
        ));

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
        $this->view->hideMenu  = true;
        $this->view->headTitle($this->view->title, 'PREPEND');
    }
}
