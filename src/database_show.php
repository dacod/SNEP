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

require_once("../includes/verifica.php");
require_once("../configs/config.php");

if (!$data = ast_status("database show","",True )) {
   display_error($LANG['msg_nosocket'],true) ;
   exit;
}
$lines = explode("\n",$data);
$arr = array();

    foreach($lines as $indice => $ramal) {
        $arr[] = substr($ramal, 0, strpos($ramal,":"));
    }

$agents = array();
$lista = array();

    foreach($arr as $ind => $arr2) {
        if(substr($arr2,1,3) == 'IAX' || substr($arr2,1,3) == 'SIP') {
            $lista[$ind]['tec'] = substr($arr2,1,3);
            $lista[$ind]['num'] = substr($arr2,14);
        }
    }

    function ramalInfo($ramal) {
        if($ramal['tec'] == 'SIP') {
            if (!$info = ast_status("sip show peer {$ramal['num']}","",True )) {
               display_error($LANG['msg_nosocket'],true) ;
               exit;
            }


            $info = explode("\n", $info);
            if($info['3'] != '' && $info['39']) {

                $return = array();
                $return['ramal'] = substr($info['3'], strpos($info['3'],':')+2) ;
                $return['tipo'] = 'SIP' ;
                $return['ip'] = ( strpos($info['38'], 'Unspecified') > 0 ? 'Indeterminado' : substr($info['38'], 17, strpos(substr($info['38'],17)," ") ) ) ;
                $return['delay'] = substr($info['45'], strpos($info['45'],'('), strpos($info['45'],')'))  ;
                $return['cds'] = str_replace("|",", ", substr($info['43'], strpos($info['43'],'(')+1, strpos($info['43'],')'))) ;
                $return['codec'] = str_replace(")"," ", $return['cds']);
                unset($return['cds']);
            }
            return $return;
        }
    }

    $ramais = array();
    foreach($lista as $ram) {
        $swp = ramalInfo($ram);

        if($swp['ramal'] != ''){
            $ramais[] = $swp;
        }
    }

// ---------------------------------------------------------------------
    
    if (!$filas = ast_status("show queues","",True )) {
       display_error($LANG['msg_nosocket'],true) ;
       exit;
    }

    $queues = array();
    $fila = explode("\n", $filas);
    unset($fila['0']);
    $strFila = '';

    foreach($fila as $keyl => $vall) {

        if(substr($vall, 0, 3) != "   "  && strlen(trim($vall)) > 1) {
            $strFila = substr($vall, 0, strpos($vall, " "));
            $queues[$strFila]['fila'] = substr($vall, 0, strpos($vall, " "));
        }
        if(strpos($vall, "SIP") > 1 || strpos($vall, "IAX2") > 1 || strpos($vall, "KHOMP") > 1 || strpos($vall, "Agent") > 1) {
            $d = trim ($vall);
            $queues[$strFila]['agent'] .= substr($d, 0, strpos($d, " ")) . ", ";
            switch($vall) {
                case strpos($vall, "Not in use") > 1 :
                    $queues[$strFila]['status'] .=  $LANG['notinuse'].",";
                    break;
                case strpos($vall, "Unknown") > 1 :
                    $queues[$strFila]['status'] .=  $LANG['unknown'].",";
                    break;
                case strpos($vall, "In use") > 1 :
                    $queues[$strFila]['status'] .=  $LANG['inuse'].",";
                    break;
                case strpos($vall, "paused") > 1 :
                    $queues[$strFila]['status'] .=  $LANG['inpause'].",";
                    break;
                case strpos($vall, "Unavailable") > 1 :
                    $queues[$strFila]['status'] .=  $LANG['unavailable'].",";
                    break;
            }
        }
    }

/*-------------------------------------------------------------------------------------- */
    
if (!$codecs = ast_status("show g729","",True )) {
   display_error($LANG['msg_nosocket'],true) ;
   exit;
}

$arrCodecs = explode("\n", $codecs);

if(strpos($arrCodecs['1'], "No such command") > 0) {


}else{  
    $arrValores = explode(" ", $arrCodecs['1']);
    $exp = explode("/", $arrValores['0']);
    $codec = array('0' => $arrValores['3'],
                   '1' => $exp['0'],
                   '2' => $exp['1']
    );
}




$titulo = $LANG['menu_status']." -> ".$LANG['menu_databaseshow'];
$smarty->assign ('AGENTES', $agentes) ;
$smarty->assign ('FILAS',$queues) ;
$smarty->assign ('RAMAIS',$ramais) ;
$smarty->assign ('CODECS',$codec) ;
display_template("database_show.tpl",$smarty,$titulo) ;
?>