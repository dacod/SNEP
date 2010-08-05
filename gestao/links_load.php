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
 ver_permissao(26);
 
if($acao == "relatorio") {
   exibe_monitor();
   exit;
}  
   
if (!$data = ast_status("khomp links show concise","",True )) {
   display_error($LANG['msg_nosocket'], true) ;
   exit;
}

$lines = explode("\n",$data);
$links = array() ; 
$boards = array() ;
$lst = '';

 if(trim(substr($lines['1'], 10 ,16)) === "Error" || strpos($lines['1'], "such command") > 0 ) {
       display_error($LANG['msg_nokhomp'], true) ;
 }  
 while (list($key, $val) = each($lines)) {
        
        if (substr($val,0,1) === "B" &&  substr($val,3,1) === "L" ) {
               if(substr($val,0,3) != $lst) {
                   $board = substr($val,0,3) ;       
                   $boards[] = $board;
                   $lnk   = substr($val,3,3) ;
                   $status= trim(substr($val,strpos($val,":")+1)) ;
                   $links[$board][$lnk] = $khomp_signal[$status] ;
                   $lst = $board;
               }
        }
 }

 $titulo = $LANG['menu_links'] ;
 $smarty->assign('PLACAS',$boards) ; 
 display_template("links_load.tpl",$smarty,$titulo) ;
 
    
 function exibe_monitor() {
 
 global $smarty, $SETUP;
 
 $_SESSION['tiporel'] = $_POST['tiporel'];
 $_SESSION['statusk'] = $_POST['statusk'];
     
 $listplacas = $_POST['listplacas']; 
 $smarty->assign ('REFRESH',array('mostrar'=> True,
                                  'tempo'  => $SETUP['ambiente']['tempo_refresh'],
                                  'url'    => "../gestao/links.php?placas=".implode(';',$listplacas)));
 $titulo = $LANG['menu_links'];
 $smarty->assign ('LINKS_KHOMP', $links_khomp_lista);
 display_template("cabecalho.tpl",$smarty,$titulo) ; 
 }
 ?>