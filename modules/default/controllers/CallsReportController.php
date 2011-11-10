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
include ("includes/functions.php");

class CallsReportController extends Zend_Controller_Action {

    public function indexAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Reports"),
                    $this->view->translate("Calls")
                ));

        $config = Zend_Registry::get('config');

        include( $config->system->path->base . "/inspectors/Permissions.php" );
        $test = new Permissions();
        $response = $test->getTests();

        $form = $this->getForm();

        if ($this->_request->getPost()) {

            $formIsValid = $form->isValid($_POST);
            $locale = Snep_Locale::getInstance()->getLocale();

            if ($locale == 'en_US') {
                $format = 'yyyy-MM-dd';
            } else {
                $format = Zend_Locale_Format::getDateFormat($locale);
            }

            $ini_date = explode(" ", $_POST['period']['initDay']);
            $final_date = explode(" ", $_POST['period']['finalDay']);

            $ini_date_valid = Zend_Date::isDate($ini_date[0], $format);
            $final_date_valid = Zend_Date::isDate($final_date[0], $format);

            if (!$ini_date_valid) {
                $iniDateElem = $form->getSubForm('period')->getElement('initDay');
                $iniDateElem->addError($this->view->translate('Invalid Date'));
                $formIsValid = false;
            }
            if (!$final_date_valid) {
                $finalDateElem = $form->getSubForm('period')->getElement('finalDay');
                $finalDateElem->addError($this->view->translate('Invalid Date'));
                $formIsValid = false;
            }

            if ($formIsValid) {
                $this->createAction();
            }
        }

        $this->view->form = $form;
    }

    private function getForm() {

        $config = Zend_Registry::get('config');
        $db = Zend_Registry::get('db');

        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . '/calls-report/');
        $form->setName('create');

        $form_xml = new Zend_Config_Xml('./default/forms/calls_report.xml');

        // --- Subsection - Periods
        $period = new Snep_Form_SubForm($this->view->translate("Period"), $form_xml->period);

        $locale = Snep_Locale::getInstance()->getLocale();
        $now = Zend_Date::now();

        if ($locale == 'en_US') {
            $now = $now->toString('YYYY-MM-dd HH:mm');
        } else {
            $now = $now->toString('dd/MM/YYYY HH:mm');
        }


        $initDay = $period->getElement('initDay');
        $initDay->setValue($now);

        $finalDay = $period->getElement('finalDay');
        $finalDay->setValue($now);


        $order = $period->getElement('order');
        $order->setValue('data');
        $form->addSubForm($period, "period");

        // Populate groups
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

        // --- Subsection -- Source
        $source = new Snep_Form_SubForm($this->view->translate("Source"), $form_xml->source);

        $sourceElement = $source->getElement('selectSrc');
        $sourceElement->addMultiOption(null, '');
        $sourceElement->setAttrib('onChange', 'blockFields($(this).id, $(this).value);');

        $srcType = $source->getElement('srctype');
        $srcType->setValue('src1');
        $form->addSubForm($source, "source");


        // --- Subsection -- Destination
        $destination = new Snep_Form_SubForm($this->view->translate("Destination"), $form_xml->destination);

        $destinationElement = $destination->getElement('selectDst');
        $destinationElement->addMultiOption(null, '');
        $destinationElement->setAttrib('onChange', 'blockFields($(this).id, $(this).value);');

        $dstType = $destination->getElement('dsttype');
        $dstType->setValue('dst1');

        $form->addSubForm($destination, "destination");

        foreach ($groupsData as $key => $value) {
            $sourceElement->addMultiOption($value, $key);
            $destinationElement->addMultiOption($value, $key);
        }

        // --- Subsection - Calls related options
        $calls = new Snep_Form_SubForm($this->view->translate("Calls"), $form_xml->calls);

        // List Cost Centers and populate select
        $select = $db->select()
                ->from(array('cc' => 'ccustos'))
                ->order('codigo');

        $costs = $db->query($select)->fetchAll();
        $costsElement = $calls->getElement('costs_center');

        foreach ($costs as $cost) {
            $costsElement->addMultiOption($cost['codigo'], $cost['tipo'] . ' : ' . $cost['codigo'] . ' - ' . $cost['nome']);
        }

        $calls->getElement('status')->setValue('ALL');
        $calls->getElement('type')->setValue('T');

        $form->addSubForm($calls, "calls");

        // --- Subsection - Other options
        $other = new Snep_Form_SubForm($this->view->translate("Other Options"), $form_xml->others);

        //$other->getElement('graph_type')->setValue('bars');
        $other->getElement('report_type')->setValue('analytical');

        $form->addSubForm($other, "others");

        $form->getElement('submit')->setLabel($this->view->translate("Show Report"));
        $form->removeElement('cancel');
        /*
          $form->addElement(new Zend_Form_Element_Submit("submit_graph",
          array("label" => $this->view->translate("Exibir Gráfico"))
          ));
          $buttonCsv = new Zend_Form_Element_Submit("submit_csv", array("label" => $this->view->translate("Exportar CSV")));

          $buttonCsv->setOrder(5004);

          $buttonCsv->addDecorator(array("closetd" => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));
          $buttonCsv->addDecorator(array("closetr" => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND));

          $form->addElement($buttonCsv);

         */
        return $form;
    }

    public function createAction() {
        $my_object = new Formata;

        $formData = $this->_request->getParams();

        $db = Zend_Registry::get('db');
        $config = Zend_Registry::get('config');

        $prefix_inout = $config->ambiente->prefix_inout;
        $dst_exceptions = $config->ambiente->dst_exceptions;

        $init_day = $formData['period']['initDay'];
        $final_day = $formData['period']['finalDay'];

        $formated_init_day = new Zend_Date($init_day);
        $formated_init_day = $formated_init_day->toString('yyyy-MM-dd hh:mm');

        $formated_final_day = new Zend_Date($final_day);
        $formated_final_day = $formated_final_day->toString('yyyy-MM-dd hh:mm');


        $ordernar = $formData['period']['order'];

        $groupsrc = $formData['source']['selectSrc'];
        if (isset($formData['source']['groupSrc'])) {
            $src = $formData['source']['groupSrc'];
        } else {
            $src = "";
        }
        if (isset($formData['source']['srctype'])) {
            $srctype = $formData['source']['srctype'];
        } else {
            $srctype = "";
        }

        $groupdst = $formData['destination']['selectDst'];
        if (isset($formData['destination']['groupDst'])) {
            $dst = $formData['destination']['groupDst'];
        } else {
            $dst = "";
        }
        if (isset($formData['destination']['dsttype'])) {
            $dsttype = $formData['destination']['dsttype'];
        } else {
            $dsttype = "";
        }

        if (isset($formData['calls']['costs_center'])) {
            $contas = $formData['calls']['costs_center'];
        }

        $duration1 = $formData['calls']['duration_init'];
        $duration2 = $formData['calls']['duration_end'];

        $status = $formData['calls']['status'];
        $status_ans = $status_noa = $status_fai = $status_bus = $status_all = '';

        foreach ($status as $stat) {
            switch ($stat) {
                case 'ANSWERED':
                    $status_ans = 'ANSWERED';
                    break;
                case 'NOANSWER':
                    $status_noa = 'NO ANSWER';
                    break;
                case 'FAILED':
                    $status_fai = 'FAILED';
                    break;
                case 'BUSY':
                    $status_bus = 'BUSY';
                    break;
            }
        }

        $call_type = $formData['calls']['type'];

        $view_files = $formData['others']['show_records'];
        $view_tarif = $formData['others']['charging'];
        // $graph_type	= $formData['others']['graph_type'];
        $rel_type = $formData['others']['report_type'];

        $this->view->back = $this->view->translate("Back");

        // Default submit
        $acao = 'relatorio';

        if (key_exists('submit_csv', $formData))
            $acao = 'csv';
        else if (key_exists('submit_graph', $formData))
            $acao = 'grafico';

        /* Busca os ramais pertencentes ao grupo de ramal de origem selecionado */
        $ramaissrc = $ramaisdst = "";
        if ($groupsrc) {
            $origens = PBX_Usuarios::getByGroup($groupsrc);

            if (count($origens) == 0) {
                $this->view->error = $this->view->translate("There are no extensions in the selected group.");
                $this->_helper->viewRenderer('error');
            } else {
                $ramalsrc = "";

                foreach ($origens as $ramal) {
                    $num = $ramal->getNumero();
                    if (is_numeric($num)) {
                        $ramalsrc .= $num . ',';
                    }
                }
                $ramaissrc = " AND src in (" . trim($ramalsrc, ',') . ") ";
            }
        }

        /* Busca os ramais pertencentes ao grupo de ramal de destino selecionado */
        if ($groupdst) {
            $destinos = PBX_Usuarios::getByGroup($groupdst);

            if (count($destinos) == 0) {
                $this->view->error = $this->view->translate("There are no extensions in the selected group.");
                $this->_helper->viewRenderer('error');
            } else {
                $ramaldst = "";
                foreach ($destinos as $ramal) {
                    $num = $ramal->getNumero();
                    if (is_numeric($num)) {
                        $ramaldst .= $num . ',';
                    }
                }
                $ramaisdst = " AND dst in (" . trim($ramaldst, ',') . ") ";
            }
        }

        /* Verificando existencia de vinculos no ramal */
        $name = $_SESSION['name_user'];
        $sql = "SELECT id_peer, id_vinculado FROM permissoes_vinculos WHERE id_peer ='$name'";
        $result = $db->query($sql)->fetchObject();

        $vinculo_table = "";
        $vinculo_where = "";
        if ($result) {
            $vinculo_table = " ,permissoes_vinculos ";
            $vinculo_where = " ( permissoes_vinculos.id_peer='{$result->id_peer}' AND (cdr.src = permissoes_vinculos.id_vinculado OR cdr.dst = permissoes_vinculos.id_vinculado) ) AND ";
        }

        /* Clausula do where: periodos inicial e final                                */
        $dia_inicial = $formated_init_day;
        $dia_final = $formated_final_day;

        $date_clause = " ( calldate >= '$dia_inicial'";
        $date_clause .=" AND calldate <= '$dia_final' )  ";

        $CONDICAO = $date_clause;

        $ORIGENS = '';

        // Clausula do where: Origens
        if ($src !== "") {
            if (strpos($src, ",")) {
                $SRC = '';
                $arrSrc = explode(",", $src);
                foreach ($arrSrc as $srcs) {
                    $SRC .= ' OR src LIKE \'' . $srcs . '\' ';
                }
                $SRC = " AND (" . substr($SRC, 3) . ")";
            } else {
                $CONDICAO = $this->do_field($CONDICAO, $src, substr($srctype, 3), 'src');
            }
        }

        // Clausula do where: Destinos
        if ($dst !== "") {
            if (strpos($dst, ",")) {
                $DST = '';
                $arrDst = explode(",", $dst);
                foreach ($arrDst as $dsts) {
                    $DST .= ' OR dst LIKE \'' . $dsts . '\' ';
                }
                $DST = " AND (" . substr($DST, 3) . ")";
            } else {
                $CONDICAO = $this->do_field($CONDICAO, $dst, substr($dsttype, 3), 'dst');
            }
        }

        if (isset($ORIGENS)) {
            $CONDICAO .= $ORIGENS;
        }
        if (isset($DST)) {
            $CONDICAO .= $DST;
        }
        if (isset($SRC)) {
            if (isset($DST)) {
                $CONDICAO .= " OR " . $SRC = substr($SRC, 4);
            } else {
                $CONDICAO .= $SRC;
            }
        }

        /* Clausula do where: Duracao da Chamada                                      */
        if ($duration1) {
            $CONDICAO .= " AND duration >= $duration1 ";
        } else {
            $CONDICAO .= " AND duration > 0 ";
        }
        if ($duration2) {
            $CONDICAO .= " AND duration <= $duration2 ";
        }


        /* Clausula do where:  Filtro de desccarte                                    */
        $TMP_COND = "";
        $dst_exceptions = explode(";", $dst_exceptions);
        foreach ($dst_exceptions as $valor) {
            $TMP_COND .= " dst != '$valor' ";
            $TMP_COND .= " AND ";
        }
        $CONDICAO .= " AND ( " . substr($TMP_COND, 0, strlen($TMP_COND) - 4) . " ) ";

        /* Clausula do where: // Centro de Custos Selecionado(s)                      */
        if (isset($contas) && count($contas) > 0) {
            $TMP_COND = "";
            foreach ($contas as $valor) {
                $TMP_COND .= " accountcode like '" . $valor . "%'";
                $TMP_COND .= " OR ";
            }
            $contas = implode(",", $contas);
            if ($TMP_COND != "")
                $CONDICAO .= " AND ( " . substr($TMP_COND, 0, strlen($TMP_COND) - 3) . " ) ";
        }

        /* Clausula do where: Status/Tipo Ligacao                                     */
        if (($status_all) || ($status_ans && $status_noa && $status_bus && $status_fai)) {
            $CONDICAO .= "";
        } else {
            if ($status_ans && $status_noa && $status_bus) {
                $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_noa' ";
                $CONDICAO .= " OR disposition = '$status_bus' ) ";
            } elseif ($status_ans && $status_noa && $status_fai) {
                $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_noa' ";
                $CONDICAO .= " OR disposition = '$status_fai' ) ";
            } elseif ($status_ans && $status_fai && $status_bus) {
                $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_bus' ";
            } elseif ($status_noa && $status_bus && $status_fai) {
                $CONDICAO .= " AND ( disposition = '$status_noa' OR disposition = '$status_bus' ";
                $CONDICAO .= " OR disposition = '$status_fai' ) ";
            } elseif ($status_ans && $status_noa) {
                $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_noa' ) ";
            } elseif ($status_ans && $status_bus) {
                $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_bus' ) ";
            } elseif ($status_ans && $status_fai) {
                $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_fai' ) ";
            } elseif ($status_noa && $status_bus) {
                $CONDICAO .= " AND ( disposition = '$status_bus' OR disposition = '$status_noa' ) ";
            } elseif ($status_fai && $status_noa) {
                $CONDICAO .= " AND ( disposition = '$status_fai' OR disposition = '$status_noa' ) ";
            } elseif ($status_bus && $status_fai) {
                $CONDICAO .= " AND ( disposition = '$status_bus' OR disposition = '$status_fai' ) ";
            } elseif ($status_ans) {
                $CONDICAO .= " AND ( disposition = '$status_ans' ) ";
            } elseif ($status_noa) {
                $CONDICAO .= " AND ( disposition = '$status_noa' ) ";
            } elseif ($status_bus) {
                $CONDICAO .= " AND ( disposition = '$status_bus' ) ";
            } elseif ($status_fai) {
                $CONDICAO .= " AND ( disposition = '$status_fai' ) ";
            }
        }

        /* Clausula do where: Tipo de Chamada (Originada/Recebida/Outra))             */
        if ($call_type == "S") {                                                      // Chamadas Originadas
            $CONDICAO .= " AND (ccustos.tipo = 'S')";
        } elseif ($call_type == "E") {  // Chamadas Recebidas
            $CONDICAO .= " AND (ccustos.tipo = 'E')";
        } elseif ($call_type == "O") {  // Chamadas Outras
            $CONDICAO .= " AND (ccustos.tipo = 'O')";
        }

        /* Clausula do where: Prefixos de Login/Logout                                */
        if (strlen($prefix_inout) > 3) {
            $COND_PIO = "";
            $array_prefixo = explode(";", $prefix_inout);
            foreach ($array_prefixo as $valor) {
                $par = explode("/", $valor);
                $pio_in = $par[0];
                if (!empty($par[1])) {
                    $pio_out = $par[1];
                }
                $t_pio_in = strlen($pio_in);
                $t_pio_out = strlen($pio_out);
                $COND_PIO .= " substr(dst,1,$t_pio_in) != '$pio_in' ";
                if (!$pio_out == '') {
                    $COND_PIO .= " AND substr(dst,1,$t_pio_out) != '$pio_out' ";
                }
                $COND_PIO .= " AND ";
            }
            if ($COND_PIO != "")
                $CONDICAO .= " AND ( " . substr($COND_PIO, 0, strlen($COND_PIO) - 4) . " ) ";
        }
        $CONDICAO .= " AND ( locate('ZOMBIE',channel) = 0 ) ";

        /* Montagem do SELECT de Consulta */
        $SELECT = "ccustos.codigo,ccustos.tipo,ccustos.nome, date_format(calldate,\"%d/%m/%Y\") AS key_dia, date_format(calldate,\"%d/%m/%Y %H:%i:%s\") AS dia, src, dst, disposition, duration, billsec, accountcode, userfield, dcontext, amaflags, uniqueid, calldate ";
        $tot_tarifado = 0;

        /* Consulta de sql para verificar quantidade de registros selecionados e
          Montar lista de Totais por tipo de Status */
        try {
            unset($duration, $billsec);
            $sql_ctds = "SELECT " . $SELECT . " FROM cdr, ccustos $vinculo_table ";
            $sql_ctds .= " WHERE (cdr.accountcode = ccustos.codigo) AND $vinculo_where " . $CONDICAO;
            $sql_ctds .= ( $ramaissrc === null ? '' : $ramaissrc) . ($ramaisdst === null ? '' : $ramaisdst);
            $sql_ctds .= " GROUP BY userfield ORDER BY calldate, userfield";

            if ($acao == "grafico") {
                $tot_fai = $tot_bus = $tot_ans = $tot_noa = $tot_oth = array();
            } else {
                $tot_fai = $tot_bus = $tot_ans = $tot_noa = $tot_bil = $tot_dur = $tot_oth = 0;
            }

            $flag_ini = True; // Flag para controle do 1o. registro lido
            $userfield = "XXXXXXX";  // Flag para controle do Userfield
            unset($result);

            foreach ($db->query($sql_ctds) as $row) {

                /* Incializa array se tipo = grafico                                   */
                $key_dia = $row['key_dia'];

                if ($acao == "grafico") {
                    $tot_dias[$key_dia] = $key_dia;
                    $tot_ans[$key_dia] = (!array_key_exists($key_dia, $tot_ans)) ? 0 : $tot_ans[$key_dia];
                    $tot_noa[$key_dia] = (!array_key_exists($key_dia, $tot_noa)) ? 0 : $tot_noa[$key_dia];
                    $tot_bus[$key_dia] = (!array_key_exists($key_dia, $tot_bus)) ? 0 : $tot_bus[$key_dia];
                    $tot_fai[$key_dia] = (!array_key_exists($key_dia, $tot_fai)) ? 0 : $tot_fai[$key_dia];
                    $tot_oth[$key_dia] = (!array_key_exists($key_dia, $tot_oth)) ? 0 : $tot_oth[$key_dia];
                }

                /*  Faz verificacoes para contabilizar valores dentro do mesmo userfield
                  So vai contabilziar resultados por userfield */
                if ($userfield != $row['userfield']) {
                    if ($flag_ini) {
                        $result[$row['uniqueid']] = $row;
                        $userfield = $row['userfield'];
                        $flag_ini = False;
                        continue;
                    }
                } else {
                    $result[$row['uniqueid']] = $row;
                    continue;
                }
                if ($row['uniqueid'] == '') {
                    continue;
                }


                /* Varre o array da chamada com mesmo userfield                        */
                foreach ($result as $val) {
                    switch ($val['disposition']) {
                        case "ANSWERED":
                            if ($acao == 'grafico')
                                $tot_ans[$key_dia]++;
                            else
                                $tot_ans++;

                            $tot_bil += $val['billsec'];
                            $tot_dur += $val['duration'];
                            if ($view_tarif) {
                                $valor = money_format('%.2n', $my_object->fmt_tarifa(
                                                array("a" => $val['dst'],
                                            "b" => $val['billsec'],
                                            "c" => $val['accountcode'],
                                            "d" => $val['calldate']), "A")
                                );
                                $tot_tarifado += $valor;
                            }
                            break;
                        case "NO ANSWER":
                            if ($acao == 'grafico') {
                                $tot_noa[$key_dia]++;
                            } else {
                                $tot_noa++;
                            }
                            break;
                        case "BUSY" :
                            if ($acao == 'grafico') {
                                $tot_bus[$key_dia]++;
                            } else {
                                $tot_bus++;
                            }
                            break;
                        case "FAILED" :
                            if ($acao == 'grafico') {
                                $tot_fai[$key_dia]++;
                            } else {
                                $tot_fai++;
                            }
                            break;
                        default :
                            if ($acao == 'grafico') {
                                $tot_oth[$key_dia]++;
                            } else {
                                $tot_oth++;
                            }
                            break;
                    } // Fim do Switch
                }
                // Fim do Foreach do array "result"
                unset($result);
                $result[$row['uniqueid']] = $row;
                $userfield = $row['userfield'];
            }

            /* Switch a seguir é para pegar um possível último registro               */
            if (isset($result)) {
                foreach ($result as $val) {
                    switch ($val['disposition']) {
                        case "ANSWERED":
                            if ($acao == 'grafico') {
                                $tot_ans[$key_dia]++;
                            } else {
                                $tot_ans++;
                                $tot_bil += $val['billsec'];
                                $tot_dur += $val['duration'];
                                if ($view_tarif) {
                                    $valor = money_format('%.2n', $my_object->fmt_tarifa(
                                                    array("a" => $val['dst'],
                                                "b" => $val['billsec'],
                                                "c" => $val['accountcode'],
                                                "d" => $val['calldate']), "A")
                                    );
                                    $tot_tarifado += $valor;
                                }
                            }
                            break;
                        case "NO ANSWER":
                            if ($acao == 'grafico') {
                                $tot_noa[$key_dia]++;
                            } else {
                                $tot_noa++;
                            }
                            break;
                        case "BUSY" :
                            if ($acao == 'grafico') {
                                $tot_bus[$key_dia]++;
                            } else {
                                $tot_bus++;
                            }
                            break;
                        case "FAILED" :
                            if ($acao == 'grafico') {
                                $tot_fai[$key_dia]++;
                            } else {
                                $tot_fai++;
                            }
                            break;
                        default :
                            if ($acao == 'grafico') {
                                $tot_oth[$key_dia]++;
                            } else {
                                $tot_oth++;
                            }
                            break;
                    } // Fim do Switch
                }
            }
            // Fim do Foreach do array result para possivel ultimo registro
        } catch (Exception $e) {
            $this->view->error = $this->view->translate("Error");
            $this->_helper->viewRenderer('error');
        }

        if ($acao == "relatorio") {
            if (($tot_fai + $tot_bus + $tot_ans + $tot_noa) == 0) {
                $this->view->error = $this->view->translate("No entries found!.");
                $this->_helper->viewRenderer('error');
            }
            $tot_wait = $tot_dur - $tot_bil;
            $totais = array("answered" => $tot_ans,
                "notanswer" => $tot_noa,
                "busy" => $tot_bus,
                "fail" => $tot_fai,
                "billsec" => $tot_bil,
                "duration" => $tot_dur,
                "espera" => $tot_wait,
                "oth" => $tot_oth,
                "tot_tarifado" => $tot_tarifado);
            // "tot_tarifado"=>number_format($tot_tarifado,2,",","."));
        } else {

            if (count($tot_fai) == 0 && count($tot_bus) == 0 &&
                    count($tot_ans) == 0 && count($tot_noa) == 0 &&
                    count($tot_oth) == 0) {
                $this->view->error = $this->view->translate("No entries found!");
                $this->_helper->viewRenderer('error');
                return;
            }

            if ($acao != "grafico") {
                $totais = array("ans" => $tot_ans, "noa" => $tot_noa,
                    "bus" => $tot_bus, "fai" => $tot_fai,
                    "dias" => $tot_dias, "dur" => $tot_dur,
                    "bil" => $tot_bil);
            } else {
                $totais = array();
            }
        }

        /* Define um SQL de Exibicao no Template, agrupado e com ctdor de agrupamentos */
        $sql_chamadas = "SELECT count(userfield) as qtdade," . $SELECT . " FROM cdr, ccustos $vinculo_table ";
        $sql_chamadas .= " WHERE (cdr.accountcode = ccustos.codigo) AND $vinculo_where " . $CONDICAO;
        $sql_chamadas .= ( $ramaissrc === null ? '' : $ramaissrc) . ($ramaisdst === null ? '' : $ramaisdst);

        switch ($ordernar) {
            case "data":
                $ordernar = " calldate ";
                break;
            case "src":
                $ordernar = " src, calldate ";
                break;
            case "dst":
                $ordernar = "  dst, calldate ";
                break;
        }

        $sql_chamadas .= " GROUP BY userfield ORDER BY $ordernar ";

        $defaultNS = new Zend_Session_Namespace('call_sql');
        $defaultNS->sql = $sql_chamadas;
        $defaultNS->totais = $totais;
        $defaultNS->view_tarif = $view_tarif;
        $defaultNS->view_files = $view_files;
        $defaultNS->status = $status;
        if (isset($contas)) {
            $defaultNS->contas = $contas;
        }
        $defaultNS->report_type = $rel_type;

        $defaultNS->src = $src;
        $defaultNS->groupsrc = $groupsrc;

        $defaultNS->dst = $dst;
        $defaultNS->groupdst = $groupdst;

        $defaultNS->sub_title = $this->view->translate($formData['period']['initDay'] . " - " . $formData['period']['initDay']);

        $row = $db->query($sql_chamadas)->fetchAll();


        for ($i = 0; $i <= count($row) - 1; $i++) {
            $row[$i]['id'] = $i + 1;
        }

        $defaultNS->row = $row;

        if (count($defaultNS->row) == 0) {
            $this->view->error = $this->view->translate("No entries found!");
            $this->_helper->viewRenderer('error');
            return;
        }

        switch ($acao) {
            case 'relatorio':
                $this->reportAction();
                break;
            /* case 'grafico':
              $this->graphAction();
              break;
              case 'csv':
              $this->csvAction();
              break;
             */
        }
    }

    public function do_field($sql, $fld, $fldtype, $nmfld="", $tpcomp="AND") {
        if (isset($fld) && ($fld != '')) {
            $sql = "$sql $tpcomp";

            if ($nmfld == "") {
                $sql = "$sql $fld";
            } else {
                $sql = "$sql $nmfld";
            }

            if (isset($fldtype)) {
                switch ($fldtype) {
                    case 1:
                        $sql = "$sql='" . $fld . "'";
                        break;
                    case 2:
                        $sql = "$sql LIKE '" . $fld . "%'";
                        break;
                    case 3:
                        $sql = "$sql LIKE '%" . $fld . "'";
                        break;
                    case 4:
                        $sql = "$sql LIKE '%" . $fld . "%'";
                        break;
                }
            } else {
                $sql = "$sql LIKE '%" . $fld . "%'";
            }
        }
        return $sql;
    }

    public function reportAction() {
        $db = Zend_Registry::get('db');
        $config = Zend_Registry::get('config');
        $format = new Formata;

        // View labels
        $this->view->seq = $this->view->translate("SEQ");
        $this->view->calldate = $this->view->translate("Call's date");
        $this->view->origin = $this->view->translate("Source");
        $this->view->destination = $this->view->translate("Destination");
        $this->view->callstatus = $this->view->translate("Status");
        $this->view->duration = $this->view->translate("Duration");
        $this->view->conversation = $this->view->translate("Conversation");
        $this->view->cost_center = $this->view->translate("Cost Center");
        $this->view->city = $this->view->translate("City");
        $this->view->state = $this->view->translate("State");

        $this->view->filter = $this->view->translate("Filter");
        $this->view->calls = $this->view->translate("Calls");
        $this->view->totals_sub = $this->view->translate("Totals");
        $this->view->times = $this->view->translate("Times");
        $this->view->tot_tariffed = $this->view->translate("Total tariffed");

        $this->view->answered = $this->view->translate("Answered");
        $this->view->nanswered = $this->view->translate("Not Answered");
        $this->view->busy = $this->view->translate("Busy");
        $this->view->failure = $this->view->translate("Failed");
        $this->view->other = $this->view->translate("Other");

        $this->view->tarrifation = $this->view->translate("Charging");
        $this->view->wait = $this->view->translate("Waiting");
        $this->view->sub_total = $this->view->translate("Subtotal");
        $this->view->gravation = $this->view->translate("Records");

        $this->view->back = $this->view->translate("Back");

        $defaultNS = new Zend_Session_Namespace('call_sql');

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Reports"),
                    $this->view->translate("Calls"),
                    $defaultNS->sub_title
                ));


        $this->view->totals = $defaultNS->totais;

        $this->view->tariffed = $defaultNS->view_tarif;
        $this->view->files = $defaultNS->view_files;
        $this->view->status = $defaultNS->status;
        $this->view->compress_files = $this->view->translate("Compress selected files");

        $this->view->duration_call = $format->fmt_segundos(
                        array("a" => $defaultNS->totais['duration'], "b" => 'hms')
        );
        $this->view->bill_sec = $format->fmt_segundos(
                        array("a" => $defaultNS->totais['billsec'], "b" => 'hms')
        );
        $this->view->wait_call = $format->fmt_segundos(
                        array("a" => $defaultNS->totais['espera'], "b" => 'hms')
        );

        $row = $defaultNS->row;

        if ($defaultNS->report_type == 'synth') {

            // Cost center treatment
            $cc = $defaultNS->contas;
            if ($cc != '') {
                $valores = '';
                $sqlcc = "select nome from ccustos where codigo IN (" . $cc . ")";
                $ccs = $db->query($sqlcc)->fetchAll(PDO::FETCH_ASSOC);
                $ccusto_sintetic = '';
                foreach ($ccs as $id => $value) {
                    $ccusto_sintetic .= $ccs[$id]['nome'] . ", ";
                }
            } else {
                $ccusto_sintetic = $this->view->translate("Any");
            }

            $this->view->cost_center_res = $ccusto_sintetic;

            // Groups treatment 
            $sint_destino = $defaultNS->dst;
            $sint_groupdst = $defaultNS->groupdst;

            if ($sint_destino != '' && $sint_groupdst == '') {
                $sint_dest = $sint_destino;
            }

            if ($sint_groupdst != '' && $sint_destino == '') {
                $sqldst = "select name from peers where peers.group = '$sint_groupdst' ";
                $sint_dst = $db->query($sqldst)->fetchAll(PDO::FETCH_ASSOC);
                $sint_dest = '';
                foreach ($sint_dst as $id => $value) {
                    $sint_dest .= $sint_dst[$id]['name'] . ", ";
                }
            }

            if (!empty($sint_dest)) {
                $this->view->sinteticdst = $sint_dest;
            }

            $sint_origem = $defaultNS->src;
            $sint_groupsrc = $defaultNS->groupsrc;

            if ($sint_origem != '' && $sint_groupsrc == '') {
                $src_sintetic = trim($sint_origem);
            }
            if ($sint_groupsrc != '' && $sint_origem == '') {
                $sqlsrc = "select name from peers where peers.group = '$sint_groupsrc' ";
                $sint_src = $db->query($sqlsrc)->fetchAll(PDO::FETCH_ASSOC);
                $src_sintetic = '';
                foreach ($sint_src as $id => $value) {
                    $src_sintetic .= $sint_src[$id]['name'] . ", ";
                }
            }
            if (!empty($sint_dest)) {
                $this->view->sinteticsrc = $src_sintetic;
            }

            $this->renderScript('calls-report/synthetic-report.phtml');
        } else {

            // Analytical Report 
            $paginatorAdapter = new Zend_Paginator_Adapter_Array($row);
            $paginator = new Zend_Paginator($paginatorAdapter);

            $paginator->setCurrentPageNumber($this->_request->page);
            $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

            $items = $paginator->getCurrentItems();

            $this->view->pages = $paginator->getPages();

            $this->view->PAGE_URL = "/snep/index.php/calls-report/report/";

            $listItems = array();

            foreach ($items as $item) {

                // Status
                switch ($item['disposition']) {
                    case 'ANSWERED':
                        $item['disposition'] = $this->view->translate('Answered');
                        break;
                    case 'NO ANSWER':
                        $item['disposition'] = $this->view->translate('Not Answered');
                        break;
                    case 'FAILED':
                        $item['disposition'] = $this->view->translate('Failed');
                        break;
                    case 'BUSY':
                        $item['disposition'] = $this->view->translate('Busy');
                        break;
                    case 'OTHER':
                        $item['disposition'] = $this->view->translate('Others');
                        break;
                }

                // Search for a city or format the telephone type
                if (strlen($item['src']) > 7 && strlen($item['dst']) < 5) {
                    $item['city'] = $this->telType($item['src']);
                } else {
                    $item['city'] = $this->telType($item['dst']);
                }

                $item['nome'] = $item['tipo'] . " : " . $item['codigo'] . " - " . $item['nome'];

                if ($defaultNS->view_tarif) {
                    $item['rate'] = $format->fmt_tarifa(array("a" => $item['dst'],
                                "b" => $item['billsec'],
                                "c" => $item['accountcode'],
                                "d" => $item['calldate'],
                                "e" => $item['tipo']));
                }

                $item['src'] = $format->fmt_telefone(array("a" => $item['src']));
                $item['dst'] = $format->fmt_telefone(array("a" => $item['dst']));

                // Tarrifation
                $item['billsec'] = $format->fmt_segundos(array("a" => $item['billsec'], "b" => 'hms'));
                $item['duration'] = $format->fmt_segundos(array("a" => $item['duration'], "b" => 'hms'));


                if ($defaultNS->view_files) {
                    $filePath = Snep_Manutencao::arquivoExiste($item['calldate'], $item['userfield']);
                    $item['file_name'] = $item['userfield'] . ".wav";

                    if ($filePath) {
                        $item['file_path'] = $filePath;
                    } else {
                        $item['file_path'] = 'N.D.';
                    }
                }

                array_push($listItems, $item);
            }

            $this->view->call_list = $listItems;
            $this->view->compact_success = $this->view->translate("The files were compressed successfully! Wait for the download start.");
            $this->renderScript('calls-report/analytical-report.phtml');
        }

        return;
    }

    private function telType($telefone) {
        $prefixo = 0;
        if (strlen($telefone) == 6)
            $prefixo = $telefone;
        elseif (strlen($telefone) > 6) {
            $prefixo = substr($telefone, 0, strlen($telefone) - 4);
            if (strlen($prefixo) > 6)
                $prefixo = substr($prefixo, -6);
        } else
            $prefixo = $telefone;

        if (!is_numeric($prefixo)) {
            $cidade = $this->view->translate('Unknown');
        }

        try {
            $cidade = '';

            $city = Snep_Cnl::getCidade($prefixo);
            if (sizeof($city) > 0) {
                $cidade = $city[0]['municipio'] . ' - ' . $city[0]['estado'];
            }

            if (strlen($cidade) <= 3) {
                if (strlen($telefone) >= 8 && substr($prefixo, -4, 1) > 6)
                    $cidade = $this->view->translate('Cellphone');
                else
                if (strlen($telefone) >= 14)
                    $cidade = $this->view->translate('D.D.I');
                else
                    $cidade = $this->view->translate('Local');
            }
        } catch (Exception $e) {
            $cidade = $e;
        }

        return $cidade;
    }

    public function compactAction() {

        $config = Zend_Registry::get('config');
        $this->_helper->layout->disableLayout();

        $zip = new ZipArchive();
        $path = $config->ambiente->path_voz;

        $fileName = date("d-m-Y-h-i") . ".zip";

        $zip->open($path . $fileName, ZIPARCHIVE::CREATE);

        $files = $this->_request->getParam('files');
        $arrFiles = explode(',', $files);

        foreach ($arrFiles as $file) {
            $zip->addFile($path . $file, $file);
        }

        $zip->close();

        $this->view->path = '/snep/arquivos/' . $fileName;
    }

    public function csvAction() {
        
    }

    public function errorAction() {
        
    }

}
