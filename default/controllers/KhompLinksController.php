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

        $this->view->breadcrumb = $this->view->translate("Status » Links Khomp");

        $form = new Snep_Form( new Zend_Config_Xml( "default/forms/khomp_links.xml" ) );
        //$form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/view');
        $form->getElement('submit')->setLabel($this->view->translate("Exibir Relatório"));

        require_once "includes/AsteriskInfo.php";
        $astinfo = new AsteriskInfo();

        $khomp_signal = array(  "kesOk (sync)" => $this->view->translate('Ativado'),
                "kesOk" => $this->view->translate('Ativado'),
                "kes{SignalLost} (sync)" => $this->view->translate('Sem Sinal'),
                "kes{SignalLost},sync" => $this->view->translate('signallost'),
                "kes{SignalLost}" => $this->view->translate('Sem Sinal'),
                "[ksigInactive]" => $this->view->translate('Desativado'),
                "NoLinksAvailable" => $this->view->translate('Sem Links dispon&iacute;veis'),
                "ringing" => $this->view->translate('Chamando'),
                "ongoing"=> $this->view->translate('Em Curso'),
                "unused"=> $this->view->translate('Sem Uso'),
                "dialing"=> $this->view->translate('Discando'),
                "kcsFree"=> $this->view->translate('Canal Livre'),
                "kcsFail"=> $this->view->translate('Falha Canal'),
                "kcsIncoming"=> $this->view->translate('Liga&ccedil;&atilde;o de Entrada'),
                "kcsOutgoing"=> $this->view->translate('Liga&ccedil;&atilde;o saida'),
                "kecsFree"=> $this->view->translate('Livre'),
                "kecsBusy"=> $this->view->translate('Ocupado'),
                "kecsOutgoing"=> $this->view->translate('Sa&iacute;da'),
                "kecsIncoming"=> $this->view->translate('Entrada'),
                "kecsLocked"=> $this->view->translate('Bloqueado'),
                "kecs{SignalLost}" => $this->view->translate('Sem Sinal'),
                "kecs{Busy}"=> $this->view->translate('Falha'),
                "kecs{Busy,Locked,RemoteLock}"=> $this->view->translate('Ocupado Sa&iacute;da'),
                "kecs{Busy,Outgoing}"=> $this->view->translate('Ocupado Sa&iacute;da'),
                "kecs{Busy,Incoming}"=> $this->view->translate('Ocupado Entrada'),
                "kgsmIdle"=> $this->view->translate('Livre'),
                "kgsmCallInProgress"=> $this->view->translate('Ocupado'),
                "kgsmSMSInProgress"=> $this->view->translate('Enviando SMS'),
                "kgsmNetworkError"=> $this->view->translate('Erro Comunica&ccedil;&atilde;o'),
                "kfxsOnHook"=> $this->view->translate('Livre'),
                "kfxsOffHook"=> $this->view->translate('Ocupado'),
                "offhook" => $this->view->translate('Ocupado'),
                "kfxsRinging"=> $this->view->translate('Chamando'),
                "kfxsFail"=> $this->view->translate('Falha'),
                "kfxsDisabled"=> $this->view->translate('Desabilitado'),
                "kfxsEnable"=> $this->view->translate('Habilitado'),
                "reserved"=> $this->view->translate('Reservado'),
                "ring"=> $this->view->translate('Chamando')
        );

        if (!$data = $astinfo->status_asterisk("khomp links show concise", "", True)) {

            throw new ErrorException( $this->view->translate("Conexão com o servidor via socket não disponivel no momento."));
        }

        $lines = explode("\n", $data);
        $links = array();
        $boards = array();
        $lst = '';

        if (trim(substr($lines['1'], 10, 16)) === "Error" || strpos($lines['1'], "such command") > 0) {

            throw new ErrorException( $this->view->translate("Nenhum hardware Khomp foi encontrado"));
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

        $khomp_signal = array(  "kesOk (sync)" => $this->view->translate('Ativado'),
                                "kesOk" => $this->view->translate('Ativado'),
                                "kes{SignalLost} (sync)" => $this->view->translate('Sem Sinal'),
                                "kes{SignalLost},sync" => $this->view->translate('signallost'),
                                "kes{SignalLost}" => $this->view->translate('Sem Sinal'),
                                "[ksigInactive]" => $this->view->translate('Desativado'),
                                "NoLinksAvailable" => $this->view->translate('Sem Links dispon&iacute;veis'),
                                "ringing" => $this->view->translate('Chamando'),
                                "ongoing"=> $this->view->translate('Em Curso'),
                                "unused"=> $this->view->translate('Sem Uso'),
                                "dialing"=> $this->view->translate('Discando'),
                                "kcsFree"=> $this->view->translate('Canal Livre'),
                                "kcsFail"=> $this->view->translate('Falha Canal'),
                                "kcsIncoming"=> $this->view->translate('Liga&ccedil;&atilde;o de Entrada'),
                                "kcsOutgoing"=> $this->view->translate('Liga&ccedil;&atilde;o saida'),
                                "kecsFree"=> $this->view->translate('Livre'),
                                "kecsBusy"=> $this->view->translate('Ocupado'),
                                "kecsOutgoing"=> $this->view->translate('Saída'),
                                "kecsIncoming"=> $this->view->translate('Entrada'),
                                "kecsLocked"=> $this->view->translate('Bloqueado'),
                                "kecs{SignalLost}" => $this->view->translate('Sem Sinal'),
                                "kecs{Busy}"=> $this->view->translate('Falha'),
                                "kecs{Busy,Locked,RemoteLock}"=> $this->view->translate('Ocupado Saída'),
                                "kecs{Busy,Outgoing}"=> $this->view->translate('Ocupado Saída'),
                                "kecs{Busy,Incoming}"=> $this->view->translate('Ocupado Entrada'),
                                "kgsmIdle"=> $this->view->translate('Livre'),
                                "kgsmCallInProgress"=> $this->view->translate('Ocupado'),
                                "kgsmSMSInProgress"=> $this->view->translate('Enviando SMS'),
                                "kgsmNetworkError"=> $this->view->translate('Erro Comunica&ccedil;&atilde;o'),
                                "kfxsOnHook"=> $this->view->translate('Livre'),
                                "kfxsOffHook"=> $this->view->translate('Ocupado'),
                                "offhook" => $this->view->translate('Ocupado'),
                                "kfxsRinging"=> $this->view->translate('Chamando'),
                                "kfxsFail"=> $this->view->translate('Falha'),
                                "kfxsDisabled"=> $this->view->translate('Desabilitado'),
                                "kfxsEnable"=> $this->view->translate('Habilitado'),
                                "reserved"=> $this->view->translate('Reservado'),
                                "ring"=> $this->view->translate('Chamando'));

        $status_sintetico_khomp = array("unused"   => $this->view->translate('Sem Uso'),
                                        "ongoing"  => $this->view->translate('Em Curso'),
                                        "ringing"  => $this->view->translate('Chamando'),
                                        "dialing"  => $this->view->translate('Discando'),
                                        "reserved" => $this->view->translate('Reservado'),
                                        "offhook"  => $this->view->translate('Ocupado'),
                                        "ring"     => $this->view->translate('Chamando'),
                                        "prering"  => $this->view->translate('Chamando'),
                                        "none"     => $this->view->translate('Nenhum'),
                                        "down"     => $this->view->translate('Desligando'));

        $status_canais_khomp = array("Sem Uso"  => "#00A651",
                                    "Em Curso" => "#ED1C24",
                                    "Chamando" => "#ff9c00",
                                    "Discando" => "#ff9c00",
                                    "Reservado" => "#ff9c00",
                                    "Ocupado" => "#ED1C24" );

        $linksKhomp = array ("0" => "B00", "1" => "B01", "3" => "B02", "4" => "B03",
                             "5" => "B04", "6" => "B05", "7" => "B06", "8" => "B07");

        require_once "includes/AsteriskInfo.php";

        $astinfo = new AsteriskInfo();

        if (!$data = $astinfo->status_asterisk("khomp summary concise", "", True)) {

            throw new ErrorException( $this->view->translate("Conexão com o servidor via socket não disponivel no momento."));
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

            throw new ErrorException( $this->view->translate("Conexão com o servidor via socket não disponivel no momento."));
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
        $this->view->breadcrumb = $this->view->translate("Status » Links Khomp");
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
