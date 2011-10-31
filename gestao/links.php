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
 
 ver_permissao(35);
 
 $tiporel = $_SESSION['tiporel'];
 $statusk = $_SESSION['statusk'];
 
 /*$statusk = $_SESSION['statusk'];*/
 $placas = explode(";", $_GET['placas']);

 /* Procura por ocorrencias de KGSM entre placas khomp. */
 if (!$sumary = ast_status("khomp summary concise","",True ) ) {
    display_error($LANG['msg_nosocket']) ;
    exit;
 }

 $sumary = explode("\n", $sumary);
 $gsm = array();
 foreach( $sumary as $id => $iface ) {
     if( strpos( $iface, "KGSM" ) ) {
         $gsms = explode( ";", $iface );
         $id = substr( $gsms[0], 4, 2 );
         $gsm[$id] = "yes";
     }
 }

 // Informacoes dos Links
 //----------------------
 
 if (!$data = ast_status("khomp links show concise","",True ) ) {
    display_error($LANG['msg_nosocket']) ;
    exit;
 }

 //ast_status("khomp links show concise","",True );

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

 
 if (count($links) === 0) {
    display_error($LANG['msg_nolinksselected'],false);
    exit ;
 }
 // Informacoes dos Canais de Cada Links
 // ------------------------------------
 $link = "" ;
 $cntSemUso = 0;
 $cntEmCurso = 0;
 $cntChamando = 0;
 $cntReservado = 0;
    
 foreach ($links as $key => $val) {
    if ($link != substr($key,1)) 
    {
       $link = (int) substr($key,1) ;
       
       if (!$data = ast_status("khomp channels show concise $link","",True ) )
       {
          display_error($LANG['msg_nosocket']) ;
          exit;
       }    
    }
    else 
    {
       continue ;
    }
           
    $lines = explode("\n",$data);
    $all_chann = array();

    while (list($chave, $valor) = each($lines)) {

       if (substr($valor,0,1) === "B" &&  substr($valor,3,1) === "C") {
       //if (substr($valor,4,1) === "B" &&  substr($valor,7,1) === "C") {
              /* Tradução dos status */
              $linha = explode(":", $valor) ;
              $st_ast = $khomp_signal[$linha[1]] ;
              $st_placa = $khomp_signal[$linha[2]] ;              
              $st_canal = $khomp_signal[$linha[3]] ;

	      if ($st_placa != "Canal Livre")
	         array_push($all_chann, $st_placa);

              /*  Relatório Sintético */
              $sintetic[substr($valor,0,3)][$linha[1]] += 1 ;
	      $sintetic[substr($valor,0,3)]['Total'] = sizeof($all_chann);
              //$sintetic[substr($valor,4,3)][$linha[1]] += 1 ;
              $l = "$linha[0]:$st_ast:$st_placa:$st_canal";

              /* Pega status de sinal/operadora GSM */
              if(strpos( $valor, "kgsm" )) {
                    $st_sinal = $linha[4];
                    $st_opera = $linha[5];
                    $st_gsm = true;
              }else{
                    $st_gsm = false;
              }
                  
              $board = substr($l,0,3) ;
              //$board = substr($l,4,3) ;
              $channel = substr($l,4,3) ;
              //$channel = substr($l,7,3) ;
              $status = explode(":", $l);

           if ($status[3] != "kecs{Busy,Locked,LocalFail}") {
              $channels[$key][$channel]['asterisk']  =  $status[1] ;
              $channels[$key][$channel]['k_call']    =  $status[2] ;
              $channels[$key][$channel]['k_channel'] =  $status[3] ;
              $channels[$key][$channel]['k_signal']  =  $st_sinal ;
              $channels[$key][$channel]['k_opera']   =  $st_opera ;
              $channels[$key][$channel]['k_gsm']   =  $st_gsm ;
           }
       }
    }
 }
 $smarty->assign('GSM', $gsm);
 $smarty->assign('DADOS', $links);
 $smarty->assign('CANAIS', $channels);
 $smarty->assign('STATUS_CANAIS', $status_canais_khomp);
 $smarty->assign('STATUS_SINTETIC', $status_sintetico_khomp);
 $smarty->assign('COLS', (100/count( $links ) )) ;
 $smarty->assign('STATUS', $statusk);
 $smarty->assign('TIPOREL', $tiporel);
 $smarty->assign('SINTETIC', $sintetic);
 $titulo = $LANG['menu_links'] ;
 display_template("links.tpl", $smarty,$titulo) ;
 
