<?php

class LogsController extends Zend_Controller_Action {

    public function indexAction() {
        
       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Status"),
            $this->view->translate("System Logs")
        ));
        $config = Zend_Registry::get('config');

        include( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        $form = new Snep_Form(new Zend_Config_Xml('./default/forms/logs.xml', 'general', true));

        $form->setAction($this->getFrontController()->getBaseUrl() . '/logs/view');

        $locale = Snep_Locale::getInstance()->getLocale();
        $now = Zend_Date::now();

        if($locale == 'en_US') {
            $now = $now->toString('YYYY-MM-dd HH:mm');
        }else{
            $now = $now->toString('dd/MM/YYYY HH:mm');
        }

        $initDay = $form->getElement('init_day');
        $initDay->setValue( $now );

        $endDay = $form->getElement('end_day');
        $endDay->setValue( $now );

        $status = $form->getElement('status');
        $status->setValue('ALL');

        $realtime = $form->getElement('real_time');
        $realtime->setValue('no');

        $submit = $form->getElement("submit");
        $submit->setLabel("Log Search");

        $this->initLogFile();

        $this->view->form = $form;
    }

    private function initLogFile() {
        $log = new Snep_Log(Zend_Registry::get('config')->system->path->log, 'agi.log');

        return $log;
    }

    public function viewAction() {

        $log = $this->initLogFile();

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Status"),
            $this->view->translate("System Logs")
        ));

        $this->view->back = $this->view->translate("Back");
        $this->view->exibition_mode = $this->view->translate("Exibition mode:");
        $this->view->normal = $this->view->translate("Normal");
        $this->view->terminal = $this->view->translate("Terminal");
        $this->view->contrast = $this->view->translate("Contrast");

        if ($log != 'error') {

            // Normal search mode
            if (strcmp($this->_request->getParam('real_time'), 'yes')) {
                
                $formData = $this->_request->getParams();

                $this->view->mode = 'normal';
                $this->view->location = 'index';
                
                $init_day = $formData['init_day'];
                $final_day =  $formData['end_day'];
                
                $formated_init_day = new Zend_Date( $init_day );
                $formated_init_time =$formated_init_day->toString('hh:mm');
                $formated_init_day =  $formated_init_day->toString('yyyy-MM-dd');
                

                $formated_final_day = new Zend_Date( $final_day );
                $formated_final_time =$formated_final_day->toString('hh:mm');
                $formated_final_day =  $formated_final_day->toString('yyyy-MM-dd');
                
                $result = $log->getLog($formated_init_day, $formated_final_day, $formated_init_time, $formated_final_time ,$formData['status'], $formData['source'], $formData['dest']);
                
                if (count($result) > 0) {

                    $this->view->result = $result;
                } else {

                    $this->view->error = $this->view->translate("No entries found!");
                    $this->_helper->viewRenderer('error');
                }

                // Tail log mode
            } else {
                $this->view->mode = 'tail';
                $this->view->location = '../../index';
                $this->view->lines = $this->view->translate("Line numbers");
            }
        } else {

            $this->view->error = $this->view->translate("The log file cannot be open!");
            $this->_helper->viewRenderer('error');
        }
    }

    public function tailAction() {
        $this->_helper->layout->disableLayout();

        $this->view->lines = $this->view->translate("Line numbers");


        $log = $this->initLogFile();
        $lines = $this->_request->getParam('lines');

        $this->view->lines = $lines;
        $result = $log->getTail($lines);

        $this->view->result = $result;
    }

    public function errorAction() {
        
    }

}
