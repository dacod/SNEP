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
class IpStatusController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
                    $this->view->translate("Status"),
                    $this->view->translate("IP Status")
                ));

        require_once "includes/AsteriskInfo.php";
        $astinfo = new AsteriskInfo();

        $data = $astinfo->status_asterisk("database show", "", True);
        $lines = explode("\n", $data);
        $arr = array();

        foreach ($lines as $indice => $ramal) {
            $arr[] = substr($ramal, 0, strpos($ramal, ":"));
        }

        $agents = array();
        $lista = array();

        foreach ($arr as $ind => $arr2) {
            if (substr($arr2, 1, 3) == 'IAX' || substr($arr2, 1, 3) == 'SIP') {
                $lista[$ind]['tec'] = substr($arr2, 1, 3);
                $lista[$ind]['num'] = substr($arr2, 14);
            }
        }

        $ramais = array();
        foreach ($lista as $ram) {
            $swp = $this->ramalInfo($ram);

            if ($swp['ramal'] != '') {
                $ramais[] = $swp;
            }
        }

        // ---------------------------------------------------------------------

        $filas = $astinfo->status_asterisk("queue show", "", True);

        $queues = array();
        $fila = explode("\n", $filas);
        unset($fila['0']);
        if ($fila['1'] == 'No queues.') {
            unset($fila['1']);
            unset($fila['2']);
        }
        $strFila = '';


        foreach ($fila as $keyl => $vall) {

            if (!isset($queues[$strFila]['fila'])) {

                $queues[$strFila]['fila'] = '';
            }

            if (!isset($queues[$strFila]['agent'])) {

                $queues[$strFila]['agent'] = '';
            }

            if (!isset($queues[$strFila]['status'])) {

                $queues[$strFila]['status'] = '';
            }

            if (substr($vall, 0, 3) != "   " && strlen(trim($vall)) > 1) {

                $strFila = substr($vall, 0, strpos($vall, " "));
                $queues[$strFila]['fila'] = substr($vall, 0, strpos($vall, " "));
            }

            if (strpos($vall, "SIP") > 1 || strpos($vall, "IAX2") > 1 || strpos($vall, "KHOMP") > 1 || strpos($vall, "Agent") > 1) {

                $d = trim($vall);

                $queues[$strFila]['agent'] .= substr($d, 0, strpos($d, " ")) . "<br> ";

                switch ($vall) {

                    case strpos($vall, "Not in use") > 1 :

                        $queues[$strFila]['status'] .= $this->view->translate('Unused') . "<br> ";
                        break;

                    case strpos($vall, "Unknown") > 1 :

                        $queues[$strFila]['status'] .= $this->view->translate('Unknown') . "<br> ";
                        break;

                    case strpos($vall, "In use") > 1 :

                        $queues[$strFila]['status'] .= $this->view->translate('In Use') . "<br> ";
                        break;

                    case strpos($vall, "paused") > 1 :

                        $queues[$strFila]['status'] .= $this->view->translate('Paused') . "<br> ";
                        break;

                    case strpos($vall, "Unavailable") > 1 :

                        $queues[$strFila]['status'] .= $this->view->translate('Unavailable') . "<br> ";
                        break;
                }
            }
        }

        /* -------------------------------------------------------------------------------------- */

        $trunk = $astinfo->status_asterisk("sip show registry", "", True);

        $peer = $astinfo->status_asterisk("sip show peers", "", True);


        $peers = explode("\n", $peer);
        $trunks = explode("\n", $trunk);

        $trunk_all = array();
        $trunk_ret = array();

        foreach ($trunks as $t_key => $t_val) {
            if ($t_key > 1) {
                $trunk_val = strtok($t_val, ' ');
                $trunk_val = strtok(' ');

                if ($trunk_val != null)
                    array_push($trunk_all, $trunk_val);
            }
        }

        // SIP Trunks from Peer list
        foreach ($peers as $p_key => $p_val) {

            if ($p_key > 1) {

                if (preg_match_all('/^([A-Za-z0-9]+|\w+\/|\d+|\d+\.\d+\.\d+\.\d+|\d+\/)(\w+)?[ ]+(\d+\.\d+\.\d+\.\d+)[ ]+([[:alpha:]]?[[:space:]]?)*\d+[ ]+(\w+[[:space:]]?)(\(\d+ ms\))?[ ]+$/', $p_val, $match)) {

                    $trunk_tmp = array();

                    foreach ($trunk_all as $trunk_ip) {

                        if (($trunk_ip == $match[1][0]) || ($trunk_ip == $match[2][0])) {

                            array_push($trunk_tmp, $match[1][0] . $match[2][0]);
                            array_push($trunk_tmp, $match[3][0]);

                            $status = $match[5][0];

                            if (!strcmp("UNREACHABLE ", $status)) {
                                $status = $this->view->translate("Not Registered");
                            } elseif (!strcmp("Unmonitored ", $status)) {
                                $status = $this->view->translate("N/A");
                            } elseif (!strcmp("OK ", $status)) {
                                $status = $this->view->translate("Registered");
                            }
                            array_push($trunk_tmp, $status);

                            array_push($trunk_tmp, $match[6][0]);
                        }
                    }
                    array_push($trunk_ret, $trunk_tmp);
                }
            }
        }

        $this->view->troncos = $trunk_ret;

        // IAX2 TRUNK
        if (!$iax_trunk =$astinfo->status_asterisk("iax2 show peers", "", True)) {
           $this->view->iax2 = NULL;
        }
        else{
        $trunk_val = '';
        $iax_trunks = explode("\n", $iax_trunk);
        $iax_all_trunks = array();

        foreach ($iax_trunks as $t_key => $t_val) {
            if (!preg_match("/\[+.*/", $t_val) && $t_key > 1) {
                $t_val = preg_replace("'\s+'", ' ', $t_val);
                $iax_all_trunks[] = explode(")", $t_val);
            }
        }

        $iax2TrunksFormat = '';
        foreach ($iax_all_trunks as $value) {

            $tempIax1 = explode(' ', $value[0]);
            $nameIax = trim($tempIax1[0]);

            if (!empty($nameIax)) {
                $ipIax = $tempIax1[1];
                $tempIax2 = explode(' ', $value[1]);
                $stateIax = trim($tempIax2[3]);
                $latIax = '';
                if ($stateIax == "UNREACHABLE") {
                    $stateIax = $this->view->translate("Not Registered");
                } elseif ($stateIax == "Unmonitored") {
                    $stateIax =  $this->view->translate("N/A");
                } elseif ($stateIax == 'OK') {
                    $stateIax =$this->view->translate("Registered");
                    $latIaxTemp = explode('(', $tempIax2[4]);
                    $latIax = trim($latIaxTemp[1]);
                    if ($latIax != '') {
                        $latIax .= ' ms';
                    } else {
                        $latIax = '';
                    }
                }
                $iax2TrunksFormat[] = array('name' => $nameIax,
                    'ip' => $ipIax,
                    'status' => $stateIax,
                    'lat' => $latIax);
            }
        }

        $this->view->iax2 =  $iax2TrunksFormat;
        }

        /* -------------------------------------------------------------------------------------- */

        $codecs = $astinfo->status_asterisk("g729 show licenses", "", True);

        $arrCodecs = explode("\n", $codecs);

        $codec = null;
        if (!preg_match("/No such command/", $arrCodecs['1'])) {
            $arrValores = explode(" ", $arrCodecs['1']);
            $exp = explode("/", $arrValores['0']);
            $codec = array('0' => $arrValores['3'],
                '1' => $exp['0'],
                '2' => $exp['1']
            );
        }

        $this->view->filas = $queues;
        $this->view->ramais = $ramais;
        $this->view->codecs = $codec;
    }

    protected function ramalInfo($ramal) {
        if ($ramal['tec'] == 'SIP') {

            $astinfo = new AsteriskInfo();
            $info = $astinfo->status_asterisk("sip show peer {$ramal['num']}", "", True);

            $return = null;


            $return = array();

            if (preg_match("/(\d+)/", $info, $matches)) {
                $return['ramal'] = $matches[0];
            }
            else
                $return['ramal'] = $this->view->translate('Undefined');

            $return['tipo'] = 'SIP';

            $tmp = substr($info, strpos($info, 'Addr->IP'), +35);
            if (preg_match("#[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}# ", $tmp, $matches)) {
                $return['ip'] = $matches[0];
            }
            else
                $return['ip'] = $this->view->translate('Undefined');

            $tmp = substr($info, strpos($info, 'Status'), +40);
            if (preg_match("#\((.*?)\)#", $tmp, $matches))
                $return['delay'] = $matches[0];
            else
                $return['delay'] = '---';

            $tmp = substr($info, strpos($info, 'Codecs'), +50);
            if (preg_match("#\((.*?)\)#", $tmp, $matches)) {
                $return['codec'] = $matches[0];
                $return['codec'] = str_replace(")", "", $return['codec']);
                $return['codec'] = str_replace("(", "", $return['codec']);
                $return['codec'] = str_replace("|", ", ", $return['codec']);
            }
            else
                $return['codec'] = '---';


            return $return;
        }
    }

}
