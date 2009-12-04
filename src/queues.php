<?php
/*-----------------------------------------------------------------------------
 * Programa: queues.php - Cadastro de Filas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");

  ver_permissao(19) ;

 // Faz Leitura do Arquivo snep-musiconhold.conf
 $row = executacmd("cat /etc/asterisk/snep/snep-musiconhold.conf","",True) ;
 $secoes = array(""=>"") ;
 $secao =  "" ;

 foreach($row as $key => $value){
    if ( (substr($value,0,1) === ";" &&
          substr($value,1,4) != "SNEP") ||
          substr($value,0,1) == "[" ||
          strlen(trim($value)) == 0 )        
       continue ;     
    if (substr($value,0,5) == ';SNEP') {
       $secao=substr($value,6) ;
       $secao=substr($secao,0,strpos($secao,")"));
       $secoes[$secao] = $secao." (".substr($value,strpos($value,"=")+1,30) ."...)" ;
       continue ;
    }
 }
 // Sons que estao no diretorio de sons
 $sounds=array(""=>"");
 $files = scandir(SNEP_PATH_SOUNDS);

 foreach($files as $i => $value) {
   if (substr($value, 0, 1) == '.') {
      unset($files[$i]);
      continue ;
   }
   if (is_dir(SNEP_PATH_SOUNDS.$value)){
      unset($files[$i]);
      continue ;
   }
   $sounds[$value] = $value;
 }
 // Monta lista de Contextos
 $ext_list = explode(";",$SETUP['ambiente']['extensions_on']);
 $extensions_list = array() ;
 $extensions_list[''] = '' ;
 
 foreach ($ext_list as $val)
    $extensions_list["$val"] = $val ;
 asort($extensions_list);
 
 // Variaveis de ambiente do form
 $tipos_holdtime = array("yes" => $LANG['yes'], 
                         "no" => $LANG['no'], 
                         "once" => $LANG['once']) ;
                         
 $tipos_joinempty = array("yes" => $LANG['yes'], 
                          "no" => $LANG['no'], 
                          "strict" => $LANG['strict']) ;
                         
 $tipos_strategy = array("ringall" => $LANG['ringall']." (ringall)",
                         "roundrobin" => $LANG['roundrobin']." (roundrobin)",
                         "leastrecent" => $LANG['leastrecent']." (lastrecent)", 
                         "random" => $LANG['random']." (random)",
                         "fewestcalls" => $LANG['fewestcalls']." (fewestcalls)", 
                         "rrmemory" => $LANG['rrmemory']." (rrmemory)" );
                                                  
 $smarty->assign ('ACAO',$acao) ;
 $smarty->assign ('OPCOES_HOLDTIME',$tipos_holdtime);
 $smarty->assign ('OPCOES_JOINEMPTY',$tipos_joinempty);
 $smarty->assign ('OPCOES_STRATEGY',$tipos_strategy);
 $smarty->assign ('OPCOES_TRUEFALSE',$tipos_tf);
 $smarty->assign ('OPCOES_SECAO', $secoes);
 $smarty->assign ('OPCOES_SONS',$sounds) ;
 $smarty->assign ('SOUNDS_PATH',SNEP_PATH_SOUNDS);
 $smarty->assign('EXTEN_LIST',$extensions_list);   
  
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." -> ".$LANG['menu_queues']." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_register']." -> ".$LANG['menu_queues']." -> ".$LANG['include'];
   principal() ;
 }
/*-----------------------------------------------------------------------------
 * Funcao PRINCIPAL - Monta a tela principal da rotina
 *-----------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo, $SETUP ;   
   $smarty->assign('ACAO',"cadastrar");
   $dt_queues['max_time_call'] = $SETUP['ambiente']['max_time_call'] ;
   $dt_queues['max_call_queue'] = $SETUP['ambiente']['max_call_queue'] ;
   $smarty->assign('dt_queues',$dt_queues);
   display_template("queues.tpl",$smarty,$titulo) ;
}
/*-----------------------------------------------------------------------------
 * Funcao CADASTRAR - Inclui um novo registro
 *-----------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $name, $musiconhold, $announce, $context, $timeout, $monitor_type, $monitor_format, $queue_youarenext, $queue_thereare, $queue_callswaiting, $queue_holdtime, $queue_minutes, $queue_seconds, $queue_lessthan, $queue_thankyou, $queue_reporthold, $announce_frequency, $announce_round_seconds, $announce_holdtime, $retry, $wrapuptime, $maxlen, $servicelevel, $strategy, $joinempty, $leavewhenempty, $eventmemberstatus, $eventwhencalled, $reportholdtime, $memberdelay, $weight, $periodic_announce, $periodic_announce_frequency,$max_call_queue, $max_time_call,$alert_mail;

   // Campos Default
   $eventwhencalled = True ;
   $monitor_type = False ;
   $monitor_format = "" ;
   $timeoutrestart = "" ;
   
   
   //Desabilitados
   $announce_round_seconds = 0;
   $periodic_announce_frequency = 0 ;

   $sql  = "INSERT INTO queues " ;
   $sql .= " VALUES ('$name', '$musiconhold', '$announce', '$context', $timeout, '$monitor_type', '$monitor_format', '$queue_youarenext', '$queue_thereare', '$queue_callswaiting', '$queue_holdtime', '$queue_minutes', '$queue_seconds', '$queue_lessthan', '$queue_thankyou', '$queue_reporthold', $announce_frequency, $announce_round_seconds, '$announce_holdtime', $retry, $wrapuptime, $maxlen, $servicelevel, '$strategy', '$joinempty', '$leavewhenempty', '$eventmemberstatus', '$eventwhencalled', '$reportholdtime', $memberdelay, $weight, '$timeoutrestart', '$periodic_announce', $periodic_announce_frequency,$max_call_queue, $max_time_call,'$alert_mail')" ;
   
   
   try {
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      // Executa comando do Asterisk para recarregar as Filas
      ast_status("module reload app_queue.so", "" );
      echo "<meta http-equiv='refresh' content='0;url=../src/queues.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }
/*-----------------------------------------------------------------------------
 * Funcao ALTERAR - Alterar um registro
 * ----------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$db,$smarty,$titulo,$acao ;
   $name = isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
   if (!$name) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
    $sql = "SELECT * FROM queues WHERE name='".mysql_escape_string($name)."'";
    $row = $db->query($sql)->fetch();
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
 $smarty->assign('ACAO',"grava_alterar") ;
 $smarty->assign ('dt_queues',$row);
 display_template("queues.tpl",$smarty,$titulo);
}

/*-----------------------------------------------------------------------------
 * Funcao GRAVA_ALTERAR - Grava registro Alterado
 *-----------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $name, $musiconhold, $announce, $context, $timeout, $monitor_type, $monitor_format, $queue_youarenext, $queue_thereare, $queue_callswaiting, $queue_holdtime, $queue_minutes, $queue_seconds, $queue_lessthan, $queue_thankyou, $queue_reporthold, $announce_frequency, $announce_round_seconds, $announce_holdtime, $retry, $wrapuptime, $maxlen, $servicelevel, $strategy, $joinempty, $leavewhenempty, $eventmemberstatus, $eventwhencalled, $reportholdtime, $memberdelay, $weight, $periodic_announce, $periodic_announce_frequency,$max_call_queue, $max_time_call, $alert_mail;
    
   // Campos desabilitados
   $announce_round_seconds = 0;
   $periodic_announce_frequency = 0 ;
    
   $sql = "update queues set " ;
   $sql.= "musiconhold='$musiconhold', announce='$announce', context='$context', timeout=$timeout, monitor_type='$monitor_type', monitor_format='$monitor_format', queue_youarenext='$queue_youarenext', queue_thereare='$queue_thereare', queue_callswaiting='$queue_callswaiting', queue_holdtime='$queue_holdtime', queue_minutes='$queue_minutes', queue_seconds='$queue_seconds', queue_lessthan='$queue_lessthan', queue_thankyou='$queue_thankyou', queue_reporthold='$queue_reporthold', announce_frequency=$announce_frequency, announce_round_seconds=$announce_round_seconds, announce_holdtime='$announce_holdtime', retry=$retry, wrapuptime=$wrapuptime, maxlen=$maxlen, servicelevel=$servicelevel, strategy='$strategy', joinempty='$joinempty', leavewhenempty='$leavewhenempty', eventmemberstatus='$eventmemberstatus', eventwhencalled='$eventwhencalled', reportholdtime='$reportholdtime', memberdelay=$memberdelay, weight=$weight,  periodic_announce='$periodic_announce', periodic_announce_frequency=$periodic_announce_frequency,    max_call_queue=$max_call_queue, max_time_call=$max_time_call, alert_mail='$alert_mail'";
   $sql .= "  where name='$name'" ;
   

   try {
     $db->beginTransaction() ;
     $db->exec($sql) ;
     $db->commit();
     // Executa comando do Asterisk para recarregar as Filas
     echo ast_status("module reload app_queue.so", "" ) ;
     echo "<meta http-equiv='refresh' content='0;url=../src/rel_queues.php'>\n" ;
   } catch (Exception $e) {
     $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*-----------------------------------------------------------------------------
 * Funcao EXCLUIR - Excluir registro selecionado
 *-----------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db;
   $name = isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
   if (!$name) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
      $sql = "DELETE FROM queues WHERE name='".mysql_escape_string($name)."'";
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      // Executa comando do Asterisk para recarregar as Filas
      ast_status("module reload app_queue.so", "" ) ;
     // display_error($LANG['msg_excluded'],true) ;
     //echo "<meta http-equiv='refresh' content='0;url=../src/rel_queues.php'>\n" ;
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
}?>