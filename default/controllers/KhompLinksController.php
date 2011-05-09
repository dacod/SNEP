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

class KhompLinksController extends Zend_Controller_Action {

    /**
     *
     * @var Zend_Form
     */
    protected $form;
    /**
     *
     * @var array
     */
    protected $forms;

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Status » Khomp Links");

        $form = new Snep_Form( new Zend_Config_Xml( "default/forms/khomp_links.xml" ) );
        $form->getElement('submit')->setLabel($this->view->translate("Show Report"));

        require_once "includes/AsteriskInfo.php";
        $astinfo = new AsteriskInfo();

        $khomp_signal = array(  "kesOk (sync)" => $this->view->translate('Activated'),
                "kesOk" => $this->view->translate('Activated'),
                "kes{SignalLost} (sync)" => $this->view->translate('Signal Lost'),
                "kes{SignalLost},sync" => $this->view->translate('Signal Lost'),
                "kes{SignalLost}" => $this->view->translate('Signal Lost'),
                "[ksigInactive]" => $this->view->translate('Deactivated'),
                "NoLinksAvailable" => $this->view->translate('No Link Available'),
                "ringing" => $this->view->translate('Ringing'),
                "ongoing"=> $this->view->translate('On going'),
                "unused"=> $this->view->translate('Unused'),
                "dialing"=> $this->view->translate('Dialing'),
                "kcsFree"=> $this->view->translate('Channel Free'),
                "kcsFail"=> $this->view->translate('Channel Fail'),
                "kcsIncoming"=> $this->view->translate('Incoming Call'),
                "kcsOutgoing"=> $this->view->translate('Outgoing Call'),
                "kecsFree"=> $this->view->translate('Free'),
                "kecsBusy"=> $this->view->translate('Busy'),
                "kecsOutgoing"=> $this->view->translate('Outgoing'),
                "kecsIncoming"=> $this->view->translate('Incoming'),
                "kecsLocked"=> $this->view->translate('Locked'),
                "kecs{SignalLost}" => $this->view->translate('Signal Lost'),
                "kecs{Busy}"=> $this->view->translate('Fail'),
                "kecs{Busy,Locked,RemoteLock}"=> $this->view->translate('Busy Outgoing'),
                "kecs{Busy,Outgoing}"=> $this->view->translate('Busy Outgoing'),
                "kecs{Busy,Incoming}"=> $this->view->translate('Busy Incoming'),
                "kgsmIdle"=> $this->view->translate('Free'),
                "kgsmCallInProgress"=> $this->view->translate('Busy'),
                "kgsmSMSInProgress"=> $this->view->translate('Sending SMS'),
                "kgsmNetworkError"=> $this->view->translate('Communication Error'),
                "kfxsOnHook"=> $this->view->translate('Free'),
                "kfxsOffHook"=> $this->view->translate('Busy'),
                "offhook" => $this->view->translate('Busy'),
                "kfxsRinging"=> $this->view->translate('Ringing'),
                "kfxsFail"=> $this->view->translate('Fail'),
                "kfxsDisabled"=> $this->view->translate('Disabled'),
                "kfxsEnable"=> $this->view->translate('Enabled'),
                "reserved"=> $this->view->translate('Reserved'),
                "ring"=> $this->view->translate('Ringing')
        );

        if (!$data = $astinfo->status_asterisk("khomp links show concise", "", True)) {

            throw new ErrorException( $this->view->translate("Socket connection to the server is not available at the moment."));
        }

        $lines = explode("\n", $data);
        $links = array();
        $boards = array();
        $lst = '';

        if (trim(substr($lines['1'], 10, 16)) === "Error" || strpos($lines['1'], "such command") > 0) {

            throw new ErrorException( $this->view->translate("No Khomp board installed"));
        }

        while (list($key, $val) = each($lines)) {

            if (substr($val, 0, 1) === "B" && substr($val, 3, 1) === "L") {

                if (substr($val, 0, 3) != $lst) {

                    $board = substr($val, 0, 3);
                    $boards[$board] = $board;
                    $lnk = substr($val, 3, 3);
                    $status = trim(substr($val, strpos($val, ":") + 1));
                    $links[$board][$lnk] = $khomp_signal[$status];
                    $lst = $board;
                }
            }
        }

        $form->getElement('boards')->setMultioptions($boards);

        $this->view->placas = $boards;
        $this->view->form = $form;

        if ( $this->_request->getPost() ) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if($form_isValid){

                $dados = $this->_request->getParams();

                $boards = implode(",", $dados['boards']);

                $this->_redirect( $this->getRequest()->getControllerName().'/view/id/'.$dados['view'].','.$dados['status'].','.$boards);
            }
        }
    }

    public function viewAction() {

        $data = $this->_request->getParam('id');

        $placas = explode(',', $data);

        $tiporel = $placas[0] ;
        $statusk = $placas[1] ;

        unset($placas[0]);
        unset($placas[1]);

        $config = Zend_Registry::get('config');

        $khomp_signal = array(  "kesOk (sync)" => $this->view->translate('Activated'),
                "kesOk" => $this->view->translate('Activated'),
                "kes{SignalLost} (sync)" => $this->view->translate('Signal Lost'),
                "kes{SignalLost},sync" => $this->view->translate('Signal Lost'),
                "kes{SignalLost}" => $this->view->translate('Signal Lost'),
                "[ksigInactive]" => $this->view->translate('Deactivated'),
                "NoLinksAvailable" => $this->view->translate('No Link Available'),
                "ringing" => $this->view->translate('Ringing'),
                "ongoing"=> $this->view->translate('On going'),
                "unused"=> $this->view->translate('Unused'),
                "dialing"=> $this->view->translate('Dialing'),
                "kcsFree"=> $this->view->translate('Channel Free'),
                "kcsFail"=> $this->view->translate('Channel Fail'),
                "kcsIncoming"=> $this->view->translate('Incoming Call'),
                "kcsOutgoing"=> $this->view->translate('Outgoing Call'),
                "kecsFree"=> $this->view->translate('Free'),
                "kecsBusy"=> $this->view->translate('Busy'),
                "kecsOutgoing"=> $this->view->translate('Outgoing'),
                "kecsIncoming"=> $this->view->translate('Incoming'),
                "kecsLocked"=> $this->view->translate('Locked'),
                "kecs{SignalLost}" => $this->view->translate('Signal Lost'),
                "kecs{Busy}"=> $this->view->translate('Fail'),
                "kecs{Busy,Locked,RemoteLock}"=> $this->view->translate('Busy Outgoing'),
                "kecs{Busy,Outgoing}"=> $this->view->translate('Busy Outgoing'),
                "kecs{Busy,Incoming}"=> $this->view->translate('Busy Incoming'),
                "kgsmIdle"=> $this->view->translate('Free'),
                "kgsmCallInProgress"=> $this->view->translate('Busy'),
                "kgsmSMSInProgress"=> $this->view->translate('Sending SMS'),
                "kgsmNetworkError"=> $this->view->translate('Communication Error'),
                "kfxsOnHook"=> $this->view->translate('Free'),
                "kfxsOffHook"=> $this->view->translate('Busy'),
                "offhook" => $this->view->translate('Busy'),
                "kfxsRinging"=> $this->view->translate('Ringing'),
                "kfxsFail"=> $this->view->translate('Fail'),
                "kfxsDisabled"=> $this->view->translate('Disabled'),
                "kfxsEnable"=> $this->view->translate('Enabled'),
                "reserved"=> $this->view->translate('Reserved'),
                "ring"=> $this->view->translate('Ringing')
        );

        $status_sintetico_khomp = array("unused"   => $this->view->translate('Unused'),
                                        "ongoing"  => $this->view->translate('On Going'),
                                        "ringing"  => $this->view->translate('Ringing'),
                                        "dialing"  => $this->view->translate('Dialing'),
                                        "reserved" => $this->view->translate('Reserved'),
                                        "offhook"  => $this->view->translate('Busy'),
                                        "ring"     => $this->view->translate('Ringing'),
                                        "prering"  => $this->view->translate('Ringing'),
                                        "none"     => $this->view->translate('None'),
                                        "down"     => $this->view->translate('Hanging Up'));

        $status_canais_khomp = array("Unused"  => "#00A651",
                                    "On Going" => "#ED1C24",
                                    "Ringing" => "#ff9c00",
                                    "Dialing" => "#ff9c00",
                                    "Reserved" => "#ff9c00",
                                    "Busy" => "#ED1C24" );

        $linksKhomp = array ("0" => "B00", "1" => "B01", "3" => "B02", "4" => "B03",
                             "5" => "B04", "6" => "B05", "7" => "B06", "8" => "B07");

        require_once "includes/AsteriskInfo.php";

        $astinfo = new AsteriskInfo();

        if (!$data = $astinfo->status_asterisk("khomp summary concise", "", True)) {

            throw new ErrorException( $this->view->translate("Socket connection to the server is not available at the moment."));
        }

        $sumary = explode("\n", $data);

        $gsm = array();

        foreach( $sumary as $id => $iface ) {

            if( strpos( $iface, "KGSM" ) ) {

                $gsms = explode( ";", $iface );
                $id = substr( $gsms[0], 4, 2 );
                $gsm[$id] = "yes";
            }
        }

        if (!$data = $astinfo->status_asterisk("khomp links show concise", "", True)) {

            throw new ErrorException( $this->view->translate("Socket connection to the server is not available at the moment."));
        }

        $lines = explode("\n",$data);
        $links = array() ;

        while (list($key, $val) = each($lines)) {

            if (substr($val,0,1) === "B" &&  substr($val,3,1) === "L") {

                $s =  substr($val,0,3);

                if (in_array($s, $placas) ) {

                    $board = substr($val,0,3) ;
                    $lnk   = substr($val,3,3) ;
                    $status= trim(substr($val,strpos($val,":")+1)) ;
                    $links[$board][$lnk] = $khomp_signal[$status] ;
                }
            }
        }

        // Informacoes dos Canais de Cada Links
        // ------------------------------------
        $link = "" ;
        $cntSemUso = 0;
        $cntEmCurso = 0;
        $cntChamando = 0;
        $cntReservado = 0;

        foreach ($links as $key => $val) {

            if ($link != substr($key,1)) {

                $link = (int) substr($key,1) ;

                $data = $astinfo->status_asterisk("khomp channels show concise $link","",True);

            } else {

                continue ;
            }

            $lines = explode("\n",$data);

            while (list($chave, $valor) = each($lines)) {

                if (substr($valor,0,1) === "B" &&  substr($valor,3,1) === "C") {

                    $linha = explode(":", $valor) ;
                    $st_ast = $khomp_signal[$linha[1]] ;
                    $st_placa = $khomp_signal[$linha[2]] ;
                    $st_canal = $khomp_signal[$linha[3]] ;

                    if ( isset($sintetic[substr($valor,0,3)][$linha[1]]) ) {

                        $sintetic[substr($valor,0,3)][$linha[1]] += 1 ;
                    }else{

                        $sintetic[substr($valor,0,3)][$linha[1]] = 1 ;
                    }

                    $l = "$linha[0]:$st_ast:$st_placa:$st_canal";

                    if(strpos( $valor, "kgsm" )) {

                        $st_sinal = $linha[4];
                        $st_opera = $linha[5];
                        $st_gsm = true;
                    }else{

                        $st_sinal = '';
                        $st_opera = '';
                        $st_gsm = false;
                    }

                    $board = substr($l,0,3) ;
                    $channel = substr($l,3,3) ;
                    $status = explode(":", $l);

                    if ($status[3] != "kecs{Busy,Locked,LocalFail}") {

                        $channels[$key][$channel]['asterisk']  =  $status[1] ;
                        $channels[$key][$channel]['k_call']    =  $status[2] ;
                        $channels[$key][$channel]['k_channel'] =  $status[3] ;

                        $channels[$key][$channel]['k_signal']  =  $st_sinal ;
                        $channels[$key][$channel]['k_opera']   =  $st_opera ;
                        $channels[$key][$channel]['k_gsm']     =  $st_gsm ;
                    }
                }
            }
        }

        $this->view->linksKhomp = $linksKhomp;
        $this->view->breadcrumb = $this->view->translate("Status » Khomp Links");
        $this->view->gsm = $gsm;
        $this->view->dados = $links;
        $this->view->canais = $channels;
        $this->view->status_canais = $status_canais_khomp;
        $this->view->status_sintetic = $status_sintetico_khomp;
        $this->view->cols = $links;
        $this->view->status = $statusk;
        $this->view->tiporel = $tiporel;
        $this->view->sintetic = $sintetic;
    }
}
