<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 require_once("../includes/verifica.php");
 require_once("../configs/config.php");
 

 $n = $_POST['n'];

 $log = new Snep_Log($smarty->agi_log, 'agi.log');

 if($log != 'error') {
       $res = $log->getTail($n);
 }else{
       display_error($LANG['error_logfile'],false) ;
       exit ;
 }
     
 print_r($res);