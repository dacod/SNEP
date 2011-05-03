<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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

require_once("../includes/verifica.php");
require_once("../configs/config.php");

 if ($acao == "zerar") {
    ast_status("khomp links errors clear","","") ;
    unset( $acao ) ;
    echo "<meta http-equiv='refresh' content='0;url=../gestao/links_errors_load.php'>\n" ;
    exit ;
 }

 if (!$data = ast_status("khomp summary concise","",True )) {
   display_error($LANG['msg_nosocket'], true) ;
   exit;
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
    display_error($LANG['error'].$LANG['msg_noerrorreport'], false) ;
    exit;
 }
 if (!$data = ast_status("khomp links errors concise","",True )) {
    display_error($LANG['msg_nosocket'], true) ;
    exit;
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
 $smarty->assign('CANAIS',$kchannels) ;
 $smarty->assign('STATUS',$kstatus) ;
 $titulo = $LANG['menu_links_erros'] ;
 display_template("links_errors.tpl",$smarty,$titulo) ;
