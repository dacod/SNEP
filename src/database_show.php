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
            
            $return = null;
           
                
                $return = array();
               
                if (preg_match("/(\d+)/" , $info, $matches)){
                $return['ramal'] = $matches[0];
                }
                else
                $return['ramal'] = 'Indeterminado';
                
                $return['tipo'] = 'SIP';
                
                $tmp = substr($info,strpos($info, 'Addr->IP'), +35);
                if (preg_match("#[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}# " , $tmp, $matches)){
                $return['ip'] = $matches[0];
                }
                else
                $return['ip'] = 'Indeterminado';
               
                $tmp = substr($info,strpos($info, 'Status'), +40);
                if (preg_match("#\((.*?)\)#" , $tmp, $matches))
                $return['delay'] = $matches[0];
                else
                $return['delay'] = '---';
                
               $tmp = substr($info,strpos($info, 'Codec Order'), +50);
               if (preg_match("#\((.*?)\)#" , $tmp, $matches)){
                $return['codec'] = $matches[0];
                $return['codec'] = str_replace(")","", $return['codec']);
                $return['codec'] = str_replace("(","", $return['codec']);
                $return['codec'] = str_replace("|",", ", $return['codec']);
               }
                else
                $return['codec'] = '---';
  
            
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
    
    if (!$filas = ast_status("queue show","",True )) {
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

	if (!$trunk = ast_status("sip show registry","",True )) {
	   display_error($LANG['msg_nosocket'],true) ;
	   exit;
	}	
	   
	if (!$peer = ast_status("sip show peers","",True )) {
	   display_error($LANG['msg_nosocket'],true) ;
	   exit;
	}	
	
	
	$peers = explode("\n", $peer);
	$trunks = explode("\n", $trunk);

    $trunk_all = array();
    $trunk_ret = array();
    
    foreach($trunks as $t_key => $t_val) {    	
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
						if(($trunk_ip == $match[1][0]) || ($trunk_ip == $match[2][0])) {
							
                                                        array_push($trunk_tmp, $match[1][0].$match[2][0]);
                                                        array_push($trunk_tmp, $match[3][0]);
                                                        
							$status = $match[5][0];
							
							if (!strcmp("UNREACHABLE ", $status)) {
								$status = "Não Registrado";
							} elseif (!strcmp("Unmonitored ", $status)) {
								$status = "N/A";
							} elseif (!strcmp("OK ", $status)) {
								$status = "Registrado";
							}
							array_push($trunk_tmp, $status);
							
							array_push($trunk_tmp, $match[6][0]);
						}        	
        		}
        		array_push($trunk_ret, $trunk_tmp);
        	}
     	}
     }
      
	$smarty->assign ('TRONCOS', $trunk_ret) ;

/*-------------------------------------------------------------------------------------- */

    
if (!$codecs = ast_status("g729 show licenses","",True )) {
   display_error($LANG['msg_nosocket'],true) ;
   exit;
}

$arrCodecs = explode("\n", $codecs);

$codec = null;
if(!preg_match("/No such command/", $arrCodecs['1'])) {
    $arrValores = explode(" ", $arrCodecs['1']);
    $exp = explode("/", $arrValores['0']);
    $codec = array('0' => $arrValores['3'],
                   '1' => $exp['0'],
                   '2' => $exp['1']
    );
}


$titulo = $LANG['menu_status']." » ".$LANG['menu_databaseshow'];
$smarty->assign ('FILAS',$queues) ;
$smarty->assign ('RAMAIS',$ramais) ;
$smarty->assign ('CODECS',$codec) ;
display_template("database_show.tpl",$smarty,$titulo) ;
