<?php

/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */
class ServicesReportController extends Zend_Controller_Action {

    public function indexAction() {
        // Title
         $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Reports"),
            $this->view->translate("Services Use")
        ));

        $config = Zend_Registry::get('config');

        // Include Inpector class, for permission test
        include_once( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        $form = $this->getForm();


        if ($this->_request->getPost()) {
            $formIsValid = $form->isValid($_POST);
            $formData = $this->_request->getParams();

            $locale = Snep_Locale::getInstance()->getLocale();

            if($locale == 'en_US')  {
                $format = 'yyyy-MM-dd';
            }else{
                $format = Zend_Locale_Format::getDateFormat( $locale );
            }

            $ini_date = explode(" ", $formData['period']['init_day']);
            $final_date = explode(" ", $formData['period']['till_day']);

            $ini_date_valid = Zend_Date::isDate($ini_date[0], $format);
            $final_date_valid = Zend_Date::isDate($final_date[0], $format);

            if( ! $ini_date_valid ) {
                $iniDateElem = $form->getSubForm('period')->getElement('init_day');
                $iniDateElem->addError( $this->view->translate('Invalid Date') );
                $formIsValid = false;
            }
            if( ! $final_date_valid ) {
                $finalDateElem = $form->getSubForm('period')->getElement('till_day');
                $finalDateElem->addError( $this->view->translate('Invalid Date') );
                $formIsValid = false;
            }

            if ($formIsValid) {
                $reportType = $formData['service']['out_type'];
                if ($reportType == 'csv') {
                    $this->csvAction();
                } else {
                    $this->viewAction();
                }
            }
        }
        $this->view->form = $form;
    }

    protected function getForm() {
        // Create object Snep_Form
        $form = new Snep_Form();

        // Set form action
        $form->setAction($this->getFrontController()->getBaseUrl() . '/services-report/index');

        $form_xml = new Zend_Config_Xml('./modules/default/formsservices_report.xml');
        $config = Zend_Registry::get('config');
        $period = new Snep_Form_SubForm($this->view->translate("Period"), $form_xml->period);
        $validatorDate = new Zend_Validate_Date(Zend_Locale_Format::getDateFormat(Zend_Registry::get('Zend_Locale')));


        $locale = Snep_Locale::getInstance()->getLocale();
        $now = Zend_Date::now();

        if($locale == 'en_US') {
            $now = $now->toString('YYYY-MM-dd HH:mm');
        }else{
            $now = $now->toString('dd/MM/YYYY HH:mm');
        }

        $yesterday = Zend_Date::now()->subDate(1);
        $initDay = $period->getElement('init_day');
        $initDay->setValue( $now );
        //$initDay->addValidator($validatorDate);

        $tillDay = $period->getElement('till_day');
        $tillDay->setValue( $now );
        //$tillDay->addValidator($validatorDate);
        $form->addSubForm($period, "period");

        $exten = new Snep_Form_SubForm($this->view->translate("Extensions"), $form_xml->exten);
        $groupLib = new Snep_GruposRamais();
        $groupsTmp = $groupLib->getAll();

        $groupsData = array();
        foreach ($groupsTmp as $key => $group) {

            switch ($group['name']) {
                case 'administrator':
                    $groupsData[$this->view->translate('Administrators')] = $group['name'];
                    break;
                case 'users':
                    $groupsData[$this->view->translate('Users')] = $group['name'];
                    break;
                case 'all':
                    $groupsData[$this->view->translate('All')] = $group['name'];
                    break;
                default:
                    $groupsData[$group['name']] = $group['name'];
            }
        }

        $selectGroup = $exten->getElement('group_select');
        $selectGroup->addMultiOption(null, '----');

        foreach ($groupsData as $key => $value) {
            $selectGroup->addMultiOption($value, $key);
        }

        $selectGroup->setAttrib('onSelect', "enableField('exten-group_select', 'exten-exten_select');");

        $form->addSubForm($exten, "exten");

        $service = new Snep_Form_SubForm($this->view->translate("Services"), $form_xml->service);

        $form->addSubForm($service, "service");

        $form->getElement('submit')->setLabel($this->view->translate("Show Report"));
        $form->removeElement("cancel");
        return $form;
    }

    protected function getQuery($data, $ExportCsv = false) {

        $fromDay = $data["period"]["init_day"];
        $tillDay = $data["period"]["till_day"];
        
        $fromDay = new Zend_Date($fromDay);
        $tillDay = new Zend_Date($tillDay);

        $extenList = $data["exten"]["exten_select"];
        $extenGroup = $data["exten"]["group_select"];
        $services = $data["service"]["serv_select"];
        $state = $data["service"]["stat_select"];

        $configFile = "./includes/setup.conf";
        $config = new Zend_Config_Ini($configFile, null, true);

        $srv = '';
        if (count($services) > 0) {
            foreach ($services as $service) {
                $srv .= "'$service',";
            }
            $srv = " AND service IN (" . substr($srv, 0, -1) . ")";
        }

        $extenSrc = $extenDst = $cond = "";

        if ($extenGroup) {
            $origins = PBX_Usuarios::getByGroup($extenGroup);
            if (count($origins) == 0) {
                throw new Zend_Exception('Group not registered');
            } else {
                foreach ($origins as $ext) {
                    $extenSrc .= "'{$ext->getNumero()}'" . ',';
                }
                $extenSrc = " AND peer in (" . trim($extenSrc, ',') . ") ";
            }
        } else if ($extenList) {

            $extenList = explode(";", $extenList);
            $list = '';

            foreach ($extenList as $value) {
                $list .= trim($value) . ',';
            }
            $extenSrc = " AND services_log.peer IN ('" . substr($list, 0, -1) . "') ";
        }

        $state_cnt = count($state);
        if ($state_cnt == 2) {
            $state = " ";
        } else {
            if ($state[0] == "D") {
                $state = " AND services_log.state = '0' ";
            }
            if ($state[0] == "A") {
                $state = " AND services_log.state = '1' ";
            }
        }

        $dateClause = " ( date >= '{$fromDay->toString('yyyy-MM-dd hh:mm')}'";
        $dateClause.=" AND date <= '{$tillDay->toString('yyyy-MM-dd hh:mm')}') "; //'
        $cond .= " $dateClause ";

        $sql = " SELECT *, DATE_FORMAT(date,'%d/%m/%Y %T') as date FROM services_log WHERE ";
        $sql.= $cond . $state;
        $sql.= ( $extenSrc ? $extenSrc : '');
        $sql.= ( $srv ? $srv : '');

        $db = Zend_Registry::get('db');
        $stmt = $db->query($sql);
        $dataTmp = $stmt->fetchAll();

        foreach ($dataTmp as $key => $value) {
            if (!$ExportCsv) {

                if ($value['state'] == 1) {
                    $dataTmp[$key]['state'] = $this->view->translate(' - Activated');
                } else {
                    $dataTmp[$key]['state'] =  $this->view->translate(' - Deactivated');
                }
            } else {

                if ($value['state'] == 1) {
                    $dataTmp[$key]['state'] =  $this->view->translate('Activated');
                } else {
                    $dataTmp[$key]['state'] =  $this->view->translate('Deactivated');
                }

                $dataTmp[$key]['status'] = '"' . $value['status'] . '"';
            }
        }
        return $dataTmp;
    }

    public function viewAction() {

        if ($this->_request->getPost()) {
            $formData = $this->_request->getParams();
            $reportData = $this->getQuery($formData);
            $_SESSION['formDataSRC'] = $formData;
        } else {
            $formData = $_SESSION['formDataSRC'];
            $page = $this->_request->getParam(page);
            $reportData = $this->getQuery($formData);
        }

        if ($reportData) {
            $this->view->breadcrumb = $this->view->translate("Reports » Services Use <br/> Period: {$formData["period"]["init_day"]} to {$formData["period"]["till_day"]} ");

            $paginatorAdapter = new Zend_Paginator_Adapter_Array($reportData);
            $paginator = new Zend_Paginator($paginatorAdapter);

            if (!isset($page)) {
                $paginator->setCurrentPageNumber($this->view->page);
            } else {
                $paginator->setCurrentPageNumber($page);
            }
            $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

            $this->view->report = $paginator;
            $this->view->pages = $paginator->getPages();
            $this->view->PAGE_URL = "/snep/index.php/{$this->getRequest()->getControllerName()}/view/";
            $this->_helper->viewRenderer('view');
        } else {
            $this->view->error = $this->view->translate("No records found.");
            $this->view->back = $this->view->translate("Back");
            $this->_helper->viewRenderer('error');
        }
    }

    public function csvAction() {
        if ($this->_request->getPost()) {
            $formData = $this->_request->getParams();
            $reportData = $this->getQuery($formData, true);

            if ($reportData) {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();

                $csv = new Snep_Csv();
                $csvData = $csv->generate($reportData, true);

                $dateNow = new Zend_Date();
                $fileName = $this->view->translate('services_report_csv_') . $dateNow->toString($this->view->translate(" dd-MM-yyyy_hh'h'mm'm' ")) . '.csv';

                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');

                echo $csvData;
            } else {
                $this->view->error = $this->view->translate("No records found.");
                $this->view->back = $this->view->translate("Back");
                $this->_helper->viewRenderer('error');
            }
        }
    }

}
