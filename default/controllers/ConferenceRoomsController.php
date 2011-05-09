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

/**
 * controller  Conference Rooms.
 */
class ConferenceRoomsController extends Zend_Controller_Action {

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

        $this->view->breadcrumb = $this->view->translate("Manage » Conference Rooms");

        $config = Zend_Registry::get('config');

        $conf_app = $config->ambiente->conference_app;

        for ($i=901 ; $i <= 915; $i++) {
            
            $salas[$i]["id"] = $i ;
            $salas[$i]["usa_auth"] = False ;
            $salas[$i]["authenticate"] = "" ;
            $salas[$i]["status"] = 0 ;
            $salas[$i]["ccustos"] = "" ;
        }

        exec('cat /etc/asterisk/snep/snep-authconferences.conf | grep "^[9][0-1][0-9]"',$senhas,$err);

        foreach ($senhas as $value) {
            
            $line = explode(":",$value);
            $salas[$line[0]]["authenticate"] = $line[1] ;
            $salas[$line[0]]["usa_auth"] = True ;
        }

        exec("cat /etc/asterisk/snep/snep-conferences.conf | grep 'exten => [9][0-1][0-9]' | cut -d '>' -f2",$out,$err);

        foreach ( $out as $key => $value){

            $room = explode(",",$value);

            if (isset($room[0])) {
                
                $sala = trim($room[0]) ;
                $salas[$sala]["status"] = 1;
                (isset($salas[$sala]["ccustos"]) ? null : $salas[$sala]["ccustos"] = "");
            }

            if (strpos($room[2],"accountcode") > 0) {

                $ccustos = trim(substr($room[2],strpos($room[2],"=")+1,-1)) ;
                $salas[$sala]["ccustos"] = $ccustos;
            }
        }

        $this->view->conferenceRooms = $salas;
        $this->view->costCenter = Snep_CostCenter_Manager::getAll();

        if ($this->getRequest()->getPost()) {

            $file_conf = "/etc/asterisk/snep/snep-conferences.conf" ;
            $file_auth = "/etc/asterisk/snep/snep-authconferences.conf" ;

            if (!is_writable($file_conf) || !is_writable($file_auth)) {
                
                echo $this->translate('File does not have editing permission');
                return False ;
            }

            $linhas_conf = file($file_conf) ;
            $linhas_auth = file($file_auth) ;

            $authenticate = $_POST['authenticate'] ;
            $password = $_POST['password'] ;
            $costCenter = $_POST['costCenter'] ;
            $activate = $_POST['activate'] ;

            $updateDate = "; Atualizado em:".date('d/m/Y H:i:s')."\n" ;

            $contentAuth = ";-------------------------------------------------------------------------------\n";
            $contentAuth .= "; Arquivo: snep-authenticate.conf - Cadastro de Senhas de Cadeado\n";
            $contentAuth .= "; Sintaxe: codigo,senha(hash MD5)\n";
            $contentAuth .= "; Include: em /etc/asterisk/extensions.conf\n";
            $contentAuth .= ";      Ex: exten => _[7-9]XXXXXXX,n,Authenticate(/etc/asterisk/snep/snep-authenticate,am)\n";
            $contentAuth .= "; Atualizado em:22/07/2008 14:28:53\n";
            $contentAuth .= "; Copyright(c) 2008 Opens Tecnologia\n";
            $contentAuth .= ";-------------------------------------------------------------------------------\n";
            $contentAuth .= "; Os registros a Seguir sao gerados pelo Software SNEP. \n";
            $contentAuth .= "; Este Arquivo NAO DEVE ser editado Manualmente sob riscos de \n";
            $contentAuth .= "; causar mau funcionamento do Asterisk\n";
            $contentAuth .= ";-------------------------------------------------------------------------------\n";

            $contentConfe = ";----------------------------------------------------------------\n";
            $contentConfe .= "; Arquivo: snep-conferences.conf - Salasa de COnferencia\n";
            $contentConfe .= "; Sintaxe: exten => sala,1,Set(CHANNEL(language)=br)\n";
            $contentConfe .= ";          exten => sala,n,Set(CDR(accountcode)=Conferencia)\n";
            $contentConfe .= ";  (*)     exten => sala,n,Authenticate(senha em hash md5,a)\n";
            $contentConfe .= ";          exten => sala,n,Conference(\${EXTEN}/S)\n";
            $contentConfe .= ";          exten => sala,n,Hangup\n";
            $contentConfe .= "; (*) = Linha Opcional - so aparece se usar senha\n";
            $contentConfe .= "; Include: em /etc/asterisk/extensions.conf\n";
            $contentConfe .= ";          include /etc/asterisk/snep/snep-conferences.conf\n";
            $contentConfe .= "; Atualizado em:22/07/2008 14:28:53\n";
            $contentConfe .= "; Copyright(c) 2008 Opens Tecnologia\n";
            $contentConfe .= ";----------------------------------------------------------------\n";
            $contentConfe .= "; Os registros a Seguir sao gerados pelo Software SNEP.\n";
            $contentConfe .= "; Este Arquivo NAO DEVE ser editado Manualmente sob riscos de\n";
            $contentConfe .= "; causar mau funcionamento do Asterisk\n";
            $contentConfe .= ";----------------------------------------------------------------\n";
            $contentConfe .= "[conferences]\n";
            $contentConfe .= "\n";
            $contentConfe .= "; Next Lines = Default of System - don't change, please\n";
            $contentConfe .= "exten => i,1,Set(CHANNEL(language)=br)\n";
            $contentConfe .= "exten => i,n,Playback(invalid)\n";
            $contentConfe .= "exten => i,n,Hangup\n";
            $contentConfe .= "\n";
            $contentConfe .= "exten => t,1,Hangup\n";
            $contentConfe .= "exten => h,1,Hangup\n";
            $contentConfe .= "exten => H,1,Hangup\n";
            $contentConfe .= "\n";

            foreach ($activate as $idActivate => $valueActivate) {

                foreach ($password as $idPassword => $valuePassword) {

                    if ( $valuePassword <> "" && $idActivate == $idPassword ) {

                        $newPassword = $idPassword.":".$valuePassword."\n";
                        $contentAuth .= $newPassword;
                    }
                }

                foreach ($costCenter as $idCostCenter => $valueCostCenter) {

                    if ( $idActivate == $idCostCenter ) {

                        $contentConfe .= ";SNEP(".$idActivate."): Room added by system\n";
                        $contentConfe .= "exten => ".$idActivate.",1,Set(CHANNEL(language)=pt_BR)\n";
                    }

                    foreach ($password as $idPassword => $valuePassword) {

                        if ( $idActivate == $idCostCenter && $idActivate == $idPassword && $valuePassword <> "" ) {

                            $contentConfe .= "exten => ".$idActivate.",n,Authenticate(/etc/asterisk/snep/snep-authconferences.conf,m)\n";
                        }
                    }

                    if ( $idActivate == $idCostCenter ) {

                        $contentConfe .= "exten => ".$idActivate.",n,Set(CDR(accountcode)=".$valueCostCenter.")\n";
                        $contentConfe .= "exten => ".$idActivate.",n,Conference(\${EXTEN}/S)\n";
                        $contentConfe .= "exten => ".$idActivate.",n,Hangup\n";
                        $contentConfe .= "\n";
                    }
                }                
            }

            file_put_contents($file_conf, $contentConfe );
            file_put_contents($file_auth, $contentAuth );
            
            $this->_redirect( $this->getRequest()->getControllerName() );
        }
    }
}
