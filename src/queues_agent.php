<?php
/*-----------------------------------------------------------------------------
 * Programa: queues_agent.php - Filas de um Agente
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(33) ;
 global $name, $acao ;
 if (isset($_GET['agente']))
     $agente = $_GET['agente']   ;
 elseif (isset($_POST['agente']))
         $agente = $_POST['agente']   ;
 else {
    display_error($LANG['no_agent']) ;
    echo "<meta http-equiv='refresh' content='0;url=../gestao/agentes.php'>\n" ;
 }
 if (isset($_POST['gravar'])) {
    grava_members() ;    
    echo "<meta http-equiv='refresh' content='0;url=../gestao/agentes.php'>\n" ;
 }
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_agents']." -> ".$LANG['queues_agent']." : ".$agente;
$filas_disp = array() ;
 try {
    $sql = "SELECT name FROM queues ORDER BY name" ;
    $row = $db->query($sql)->fetchAll() ;
    foreach ($row as $val) {
       $filas_disp[$val['name']] = $val['name'] ;
    }
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }
 
 $filas_used = array() ;
 try {
    $sql = "SELECT queue from queues_agent WHERE agent_id = '".$agente."'" ;
    $row = $db->query($sql)->fetchAll() ;
    foreach ($row as $val) {
       $filas_used[$val['queue']] = $val['queue'] ;
    }
    // Retira da Lista de disponiveis os que ja estao sendo usados
    foreach ($filas_disp as $key=>$val) {
       if (array_key_exists($key,$filas_used)) {
          unset($filas_disp[$key]) ;
       }
    }       
 } catch (Exception $e) {
   display_error($LANG['error'].$e->getMessage(),true) ;
 }
 $smarty->assign ('OPCOES_LIVRES',$filas_disp);
 $smarty->assign ('OPCOES_USADOS',$filas_used);
 $smarty->assign ('ACAO','gravar') ;
 $smarty->assign ('agente',$agente) ;
 display_template("queues_agent.tpl",$smarty,$titulo);
   
/*-----------------------------------------------------------------------------
 * Funcao grava_members - Grava dados nas teb&#231;las do BD
 * ----------------------------------------------------------------------------*/   
 function grava_members() {
    global $db, $lista2, $agente ;
    try {
      $db->beginTransaction() ;
      $sql = "DELETE FROM queues_agent WHERE agent_id = '$agente'";
      $stmt = $db->prepare($sql) ;
      $stmt->execute() ;
      $sql = "INSERT INTO queues_agent (agent_id,queue) VALUES (:agente, :queue)" ;
      $stmt = $db->prepare($sql) ;
      $stmt->bindParam('agente',$agente) ;
      $stmt->bindParam('queue',$tmp_fila) ;
      foreach ($lista2 as $val) {
         $tmp_fila = $val ;
         $stmt->execute() ;
      }
      $db->commit();
      //echo "<meta http-equiv='refresh' content='0;url=../gestao/agentes.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }  
 ?>