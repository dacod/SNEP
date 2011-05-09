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

class ErrorsKhompController extends Zend_Controller_Action {

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

        $this->view->breadcrumb = $this->view->translate("Status » Errors Khomp Links");

        require_once "includes/AsteriskInfo.php";
        $astinfo = new AsteriskInfo();

        $data = $astinfo->status_asterisk("database show","",True);

        if (!isset($data)) {

            throw new ErrorException( $this->view->translate("Socket connection to the server is not available at the moment."));
        }

        $data = $astinfo->status_asterisk("khomp summary concise","",True);

        if (!isset($data)) {

            throw new ErrorException( $this->view->translate("Socket connection to the server is not available at the moment."));
        }

        $lines = explode("\n",$data);

        $kchannels = array() ;
        $ONLYGSM = False ;

        while (list($key, $val) = each($lines)) {

            $lin = explode(";", $val) ;

            if (substr($lin[0],0,3) == "<K>" ) {

                $placa = substr($lin[0],3) ;

                if (isset($lin[4])) {

                    if ( substr($lin[1],0,4) != 'KGSM' && substr($lin[1],0,7) != 'KFXVoIP' )  {

                        if ($lin[4] > 0 ) {

                            for ($i=0; $i <= $lin[4]-1; $i++ )

                                $kchannels[$placa][$i] = $lin[1] ;

                        } else {

                            $kchannels[$placa][0] = $lin[1] ;
                        }
                    }
                }
            }

            if (isset($lin[1])) {

                if (substr($lin[1],0,4) == 'KGSM' || substr($lin[1],0,7) == 'KFXVoIP' ) {

                    $ONLYGSM = TRUE ;
                }
            }
        }

        if( $ONLYGSM && count($kchannels) == 0) {

            throw new ErrorException( $this->view->translate("Error"));
        }

        if (!$data = $astinfo->status_asterisk("khomp links errors concise","",True )) {

            throw new ErrorException( $this->view->translate("Socket connection to the server is not available at the moment."));
        }
        
        $lines = explode("\n",$data);
        $kstatus = array() ;

        while (list($key, $val) = each($lines)) {

            $lin = explode(":", $val) ;

            if (substr($lin[0],0,3) == "<K>" ) {

                $placa = substr($lin[0],3) ;
                $link  = $lin[1] ;
                $sts_name = $lin[2];
                $sts_val = $lin[3];
                $kstatus[$sts_name][$placa][$link] = $sts_val ;
            }
        }

        $this->view->canais = $kchannels;
        $this->view->status = $kstatus;

        if ($this->_request->getPost()) {

            require_once "includes/AsteriskInfo.php";
            $astinfo = new AsteriskInfo();
            $astinfo->status_asterisk("khomp links errors clear","","");

            $this->_redirect($this->getRequest()->getControllerName());
        }
    }
}
