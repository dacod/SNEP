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
class RankingReportController extends Zend_Controller_Action {

    private $form;

    public function indexAction() {
        // Title
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
			$this->view->translate("Reports"),
			$this->view->translate("Call Rankings"))
		);

        $config = Zend_Registry::get('config');
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

            $reportType = $formData['rank']['out_type'];

            if($formIsValid) {
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
        $form->setAction($this->getFrontController()->getBaseUrl() . '/ranking-report/index');

        $form_xml = new Zend_Config_Xml('./default/forms/ranking_report.xml');
        $config = Zend_Registry::get('config');
        $period = new Snep_Form_SubForm($this->view->translate("Period"), $form_xml->period);

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

        $tillDay = $period->getElement('till_day');
        $tillDay->setValue( $now );

        $form->addSubForm($period, "period");

        $rank = new Snep_Form_SubForm($this->view->translate("Ranking Options"), $form_xml->rank);
        $selectNumView = $rank->getElement('view');

        for ($index = 1; $index <= 30; $index++) {
            $selectNumView->addMultiOption($index, $index);
        }

        $form->addSubForm($rank, "rank");

        $form->getElement('submit')->setLabel($this->view->translate("Show Report"));
        $form->removeElement("cancel");
        return $form;
    }

    protected function getQuery($data, $exportCsv = false) {

        $init_day = explode(" ", $formData['period']['init_day'] );
        $final_day = explode(" ", $formData['period']['till_day']);

        $formated_init_day = new Zend_Date( $init_day[0] );
        $formated_init_day =  $formated_init_day->toString('yyyy-MM-dd');
        $formated_init_time = $init_day[1];

        $formated_final_day = new Zend_Date( $final_day[0] );
        $formated_final_day =  $formated_final_day->toString('yyyy-MM-dd');
        $formated_final_time = $final_day[1];

        $fromDay =  $formated_init_day;
        $tillDay =  $formated_final_day;
        $fromHour = $formated_init_time;
        $tillHour = $formated_final_time;
        
        $rankType = $data["rank"]["type"];
        $rankOrigins = $data["rank"]["origin"];
        $rankView = $data["rank"]["view"];


        $config = Zend_Registry::get('config');
        $db = Zend_Registry::get('db');


        $dateClause = " ( calldate >= '{$fromDay->get($dateFormat)}'";
        $dateClause.=" AND calldate <= '{$tillDay->get($dateFormat)} 23:59:59'"; //'
        $dateClause.=" AND DATE_FORMAT(calldate,'%T') >= '$fromHour:00'";
        $dateClause.=" AND DATE_FORMAT(calldate,'%T') <= '$tillHour:59') ";
        $whereCond = " WHERE $dateClause";

        $prefixInout = $config->get('prefix_inout');

        if (strlen($prefixInout) > 6) {
            $condPrefix = "";
            $prefixArray = explode(";", $prefixInout);

            foreach ($prefixArray as $valor) {
                $pair = explode("/", $valor);

                $prefixIn = $pair[0];
                $prefixOut = isset($pair[1]);

                $prefixInSize = strlen($prefixIn);
                $prefixOutSize = strlen($prefixOut);

                $condPrefix .= " substr(dst,1,$prefixInSize) != '$prefixIn' ";
                if (!$prefixOut == '') {
                    $condPrefix .= " AND substr(dst,1,$prefixOutSize) != '$prefixOut' ";
                }
                $condPrefix .= " AND ";
            }
            if ($condPrefix != "")
                $whereCond .= " AND ( " . substr($condPrefix, 0, strlen($condPrefix) - 4) . " ) ";
        }


        $condDstExp = "";
        $dstExceptions = $config->ambiente->dst_exceptions;
        $dstExceptions = explode(";", $dstExceptions);

        foreach ($dstExceptions as $valor) {
            $condDstExp .= " dst != '$valor' ";
            $condDstExp .= " AND ";
        }
        $whereCond .= " AND ( " . substr($condDstExp, 0, strlen($condDstExp) - 4) . " ) ";

        /* Vinc */
        $name = Zend_Auth::getInstance()->getIdentity();
        $sql = "SELECT id_peer, id_vinculado FROM permissoes_vinculos WHERE id_peer ='$name'";
        $result = $db->query($sql)->fetchObject();

        $vincTable = "";
        $vincWhere = "";

        if ($result) {
            $vincTable = " ,permissoes_vinculos ";
            $vincWhere = " AND ( permissoes_vinculos.id_peer='{$result->id_peer}' AND (cdr.src = permissoes_vinculos.id_vinculado OR cdr.dst = permissoes_vinculos.id_vinculado) ) ";
        }

        $whereCond .= " AND ( locate('ZOMBIE',channel) = 0 ) ";

        $sql = "SELECT cdr.src, cdr.dst, cdr.disposition, cdr.duration, cdr.billsec, cdr.userfield ";
        $sql .= " FROM cdr JOIN peers on cdr.src = peers.name" . $vincTable . $whereCond . " " . $vincWhere . " ORDER BY calldate,userfield,cdr.amaflags";

        $rankData = array();

        try {

            $flag = $disposition = "";
            $dst = "";
            $brk = False;
            unset($duration, $billsec);

            foreach ($db->query($sql) as $row) {

                // Trata das Chamadas - Quantidades
                if ($flag == $row['userfield']) {
                    $disposition = $row['disposition'];
                    $src = $this->formatNumberAsPhone($row['src']);
                    $dst = $this->formatNumberAsPhone($row['dst']);
                    $brk = False;
                    continue;
                } else {

                    $dst = $this->formatNumberAsPhone($row['dst']);
                    if (!isset($disposition) || $disposition == "") { // Primeira vez
                        $flag = $row['userfield'];
                        $disposition = $row['disposition'];
                        $src = $this->formatNumberAsPhone($row['src']);
                        $dst = $this->formatNumberAsPhone($row['dst']);
                        $brk = False;
                        continue;
                    }
                    $brk = True;
                }

                if (!isset($duration)) {
                    $duration = '';
                }


                if (!isset($rankData[$src])) {
                    $rankData[$src][$dst]["QA"] = 0;
                    $rankData[$src][$dst]["QN"] = 0;
                    $rankData[$src][$dst]["QT"] = 0;
                    $rankData[$src][$dst]["TA"] = 0;
                    $rankData[$src][$dst]["TN"] = 0;
                    $rankData[$src][$dst]["TT"] = 0;
                    $countTotal[$src] = 0;
                    $timeTotal[$src] = 0;
                }
                switch ($disposition) {
                    case "ANSWERED":
                        $rankData[$src][$dst]["QA"]++;
                        $rankData[$src][$dst]["TA"] += $duration;
                        break;
                    default:
                        $rankData[$src][$dst]["QN"]++;
                        $rankData[$src][$dst]["TN"] += $duration;
                        break;
                }
                $rankData[$src][$dst]["QT"]++;
                $rankData[$src][$dst]["TT"] += $duration;
                $countTotal[$src]++;
                $timeTotal[$src] += $duration;

                $disposition = $row['disposition'];
                $src = $row['src'];
                $dst = $row['dst'];
                $duration = $row['duration'];
                unset($brk);
            } // Fim do Foreach que varre o SELECT do CDR

            if (!isset($rankData[$src])) {
                $rankData[$src][$dst]["QA"] = 0;
                $rankData[$src][$dst]["QN"] = 0;
                $rankData[$src][$dst]["QT"] = 0;
                $rankData[$src][$dst]["TA"] = 0;
                $rankData[$src][$dst]["TN"] = 0;
                $rankData[$src][$dst]["TT"] = 0;
                $countTotal[$src] = 0;
                $timeTotal[$src] = 0;
            }
            switch ($disposition) {
                case "ANSWERED":
                    $rankData[$src][$dst]["QA"]++;
                    $rankData[$src][$dst]["TA"] += $duration;
                    break;
                default:
                    $rankData[$src][$dst]["QN"]++;
                    $rankData[$src][$dst]["TN"] += $duration;
                    break;
            } // Fim do switch
            $rankData[$src][$dst]["QT"]++;
            $rankData[$src][$dst]["TT"] += $duration;
            $countTotal[$src]++;
            $timeTotal[$src] += $duration;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        if (count($rankData) <= 1) {
            return;
        }
        arsort($countTotal);
        arsort($timeTotal);


        $totView = $rankOrigins - 1;
        if ($rankType == "num") {
            foreach ($countTotal as $src => $qtd) {
                $ctd = $rankView;
                if (isset($rankData[$src])) {
                    foreach ($rankData[$src] as $dst => $val) {
                        if ($ctd == 0)
                            break;
                        $ctd--;
                        $rank[$src] [$val['QT']] [$dst] = $val;
                    }
                }
                if ($totView == 0)
                    break;
                $totView--;
            }
        } else {
            foreach ($timeTotal as $src => $qtd) {
                $ctd = $rankView;
                if (isset($rankData[$src])) {
                    foreach ($rankData[$src] as $dst => $val) {
                        if ($ctd == 0)
                            break;
                        $ctd--;
                        $timeMinutes = $val['TT'];
                        $rank[$src] [$timeMinutes] [$dst] = $val;
                    }
                }
                if ($totView == 0)
                    break;
                $totView--;
            }
        }

        foreach ($rank as $src => $vqtd) {
            krsort($vqtd);
            foreach ($vqtd as $qtd => $vdst) {

                foreach ($vdst as $dst => $val) {
                    $val['TA'] = $this->formatSecondsAsTime($val['TA']);
                    $val['TN'] = $this->formatSecondsAsTime($val['TN']);
                    $val['TT'] = $this->formatSecondsAsTime($val['TT']);
                    $rank[$src][$qtd][$dst] = $val;
                }
            }
        }

        foreach ($timeTotal as $key => $value) {

            $timeTotal[$key] = $this->formatSecondsAsTime($value);
        }
        foreach ($rank as $key => $value) {
            krsort($rank[$key]);
        }

        if ($exportCsv) {


            $resultRank = array();

            foreach ($rank as $chaves => $valores) {
                $rankTmp = array();
                $rankTmp['origem'] = $chaves;
                foreach ($valores as $key => $value) {
                    foreach ($value as $k => $v) {
                        $rankTmp['destino'] = $k;
                        $rankTmp['QA'] = $v['QA'];
                        $rankTmp['QN'] = $v['QN'];
                        $rankTmp['TA'] = $v['TA'];
                        $rankTmp['TN'] = $v['TN'];
                        $resultRank[] = $rankTmp;
                    }
                }
            }
            $titulo = array(
                "origem" => $this->view->translate("SOURCE"),
                "destino" => $this->view->translate("DESTINATION"),
                "QA" => $this->view->translate("N. ANSWERD"),
                "QN" => $this->view->translate('N. UNANSWERD'),
                "TA" => $this->view->translate('TIME TOTAL ANSWERD'),
                "TN" => $this->view->translate('TIME TOTAL UNANSWERD')
            );
            
            $result = array(
                "data" => $resultRank,
                "cols"  => $titulo   
            );
        } else {
            $result = array(
                "timeTotal" => $timeTotal,
                "countTotal" => $countTotal,
                "rank" => $rank,
                "type" => $rankType
            );
        }

        return $result;
    }

    public function viewAction() {

        $formData = $this->_request->getParams();
        $reportData = $this->getQuery($formData);


        if ($reportData) {
        	$this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
				$this->view->translate("Reports"),
				$this->view->translate("Call Ranking"),
				$this->view->translate("{$formData["period"]["init_day"]} ({$formData["period"]["init_hour"]}) - {$formData["period"]["till_day"]} ({$formData["period"]["till_hour"]})"))
			);


            $this->view->PAGE_URL = "/snep/index.php/{$this->getRequest()->getControllerName()}/view/";
            $this->view->rank = $reportData["rank"];
            $this->view->type = $reportData["type"];
            $this->view->timeData = $reportData["timeTotal"];
            $this->view->countData = $reportData["countTotal"];
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
                $csvData = $csv->generate($reportData['data'], true, $reportData['cols']);

                $dateNow = new Zend_Date();
                $fileName = $this->view->translate('relatorio_ranking_csv_') . $dateNow->toString($this->view->translate(" dd-MM-yyyy_hh'h'mm'm' ")) . '.csv';

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

    private function formatNumberAsPhone($phone) {

        if (strlen($phone) == 8)
            return preg_replace("/([0-9]{4})([0-9]{4})/", "$1-$2", $phone);
        elseif (strlen($phone) == 10)
            return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "($1) $2-$3", $phone);
        elseif (strlen($phone) == 11 && preg_match("/([0|8|5|3]{4})([0-9]{3})([0-9]{4})/", $phone))
            return preg_replace("/([0|8|5|3]{4})([0-9]{3})([0-9]{4})/", "$1 $2 $3", $phone);
        elseif (strlen($phone) == 11)
            return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "($1) $2-$3", $phone);
        elseif (strlen($phone) == 13)
            return preg_replace("/([0-9]{3})([0-9]{2})([0-9]{4})([0-9]{4})/", "$1 ($2) $3-$4", $phone);
        else
            return $phone;
    }

    private function formatSecondsAsTime($sec) {
        $minTime = intval($sec / 60);
        $secTime = sprintf("%02s", intval($sec % 60));
        $hourTime = sprintf("%02s", intval($minTime / 60));
        $restMinTime = sprintf("%02s", intval($minTime % 60));
        return $hourTime . ":" . $restMinTime . ":" . $secTime;
    }

    function subval_sort($a, $subkey) {
        foreach ($a as $k => $v) {
            $b[$k] = strtolower($v[$subkey]);
        }
        asort($b);
        foreach ($b as $key => $val) {
            $c[] = $a[$key];
        }
        return $c;
    }

}
