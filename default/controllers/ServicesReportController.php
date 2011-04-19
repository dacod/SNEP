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
        $this->view->breadcrumb = $this->view->translate("Relatórios » Serviços Utilizados");

        $config = Zend_Registry::get('config');

        // Include Inpector class, for permission test
        include_once( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        $form = $this->getForm();
        $this->view->form = $form;
    }

    protected function getForm() {
        // Create object Snep_Form
        $form = new Snep_Form();

        // Set form action
        $form->setAction($this->getFrontController()->getBaseUrl() . '/services-report/submit');

        $form_xml = new Zend_Config_Xml('./default/forms/services_report.xml');
        $period = new Snep_Form_SubForm($this->view->translate("Período"), $form_xml->period);

        $yesterday = Zend_Date::now()->subDate(1);
        $initDay = $period->getElement('init_day');
        $initDay->setValue(strtok($yesterday, ' '));

        $tillDay = $period->getElement('till_day');
        $tillDay->setValue(strtok(Zend_Date::now(), ' '));
        $form->addSubForm($period, "period");


        $exten = new Snep_Form_SubForm($this->view->translate("Ramais"), $form_xml->exten);
        $groupLib = new Snep_GruposRamais();
        $groupsTmp = $groupLib->getAll();

        $groupsData = array();
        foreach ($groupsTmp as $key => $group) {

            switch ($group['name']) {
                case 'administrator':
                    $groupsData[$this->view->translate('Administradores')] = $group['name'];
                    break;
                case 'users':
                    $groupsData[$this->view->translate('Usuários')] = $group['name'];
                    break;
                case 'all':
                    $groupsData[$this->view->translate('Todos')] = $group['name'];
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

        $service = new Snep_Form_SubForm($this->view->translate("Serviços"), $form_xml->service);

        $form->addSubForm($service, "service");

        $form->getElement('submit')->setLabel($this->view->translate("Exibir Relatório"));
        $form->removeElement("cancel");
        $buttonCsv = new Zend_Form_Element_Submit("submit_csv", array("label" => $this->view->translate("Exportar CSV")));
        $buttonCsv->setOrder(1001);
        $buttonCsv->removeDecorator('DtDdWrapper');
        $buttonCsv->addDecorator(array("closetd" => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $buttonCsv->addDecorator(array("closetr" => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
        $form->addElement($buttonCsv);

        return $form;
    }

    protected function submitAction() {
        
        if ($this->_request->getPost()) {
            $formData = $this->_request->getParams();
           
            if (key_exists('submit_csv', $formData))
                $this->csvAction();
            else
                $this->viewAction();
        }
    }

    protected function getQuery($data, $ExportCsv = false) {

        $fromDay = $data["period"]["init_day"];
        $tillDay = $data["period"]["till_day"];
        $fromHour = $data["period"]["init_hour"];
        $tillHour = $data["period"]["till_hour"];
        $extenList = $data["exten"]["exten_select"];
        $extenGroup = $data["exten"]["group_select"];
        $services = $data["service"]["serv_select"];
        $state = $data["service"]["stat_select"];

        $configFile = "./includes/setup.conf";
        $config = new Zend_Config_Ini($configFile, null, true);
        
        if (Zend_Date::isDate($fromDay, 'dd/MM/yyyy', $config->ambiente->language) && Zend_Date::isDate($tillDay, 'dd/MM/yyyy', $config->ambiente->language)){
            
            $dayTmp = new Zend_Date(Zend_Locale_Format::getDate($tillDay, array('date_format' => 'dd/MM/yyyy')));
            $tillDay = $dayTmp;
            
             $dayTmp = new Zend_Date(Zend_Locale_Format::getDate($fromDay, array('date_format' => 'dd/MM/yyyy')));
            $fromDay = $dayTmp;
            
        }
        else{
            $this->view->error = $this->view->translate("Formato da data inválido!");
            $this->view->back = $this->view->translate("Voltar");
            $this->renderScript('services-report/error.phtml');
            return;
        }

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

        $dateClause = " ( date >= '{$fromDay->get('yyyy-MM-dd')}'";
        $dateClause.=" AND date <= '{$tillDay->get('yyyy-MM-dd')} 23:59:59'"; //'
        $dateClause.=" AND DATE_FORMAT(date,'%T') >= '$fromHour:00'";
        $dateClause.=" AND DATE_FORMAT(date,'%T') <= '$tillHour:59') ";
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
                    $dataTmp[$key]['state'] = ' - Ativado';
                } else {
                    $dataTmp[$key]['state'] = ' - Desativado';
                }
            } else {

                if ($value['state'] == 1) {
                    $dataTmp[$key]['state'] = 'Ativado';
                } else {
                    $dataTmp[$key]['state'] = 'Desativado';
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
            $this->view->breadcrumb = $this->view->translate("Relatórios » Serviços Utilizados | Periodo: {$formData["period"]["init_day"]} ({$formData["period"]["init_hour"]}) a {$formData["period"]["till_day"]} ({$formData["period"]["till_hour"]})");

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
            $this->view->error = $this->view->translate("Nenhum registro encontrado.");
            $this->view->back = $this->view->translate("Voltar");
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
                $fileName = $this->view->translate('relatorio_servicos_csv_') . $dateNow->toString($this->view->translate('dd-MM-yyyy')) . '.csv';

                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');

                echo $csvData;
            } else {
                $this->view->error = $this->view->translate("Nenhum registro encontrado.");
                $this->view->back = $this->view->translate("Voltar");
                $this->_helper->viewRenderer('error');
            }
        }
    }
    
    public function errorAction(){
        
    }

}
