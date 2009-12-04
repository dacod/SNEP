<?php
/*-----------------------------------------------------------------------------
 * Programa: lista_ccustos.php - Lista de Centros de Custos Cadastrados
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 try {
   $sql = "SELECT * FROM ccustos ORDER BY codigo" ;
   $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
   display_error($LANG['error'].$e->getMessage(),true) ;
 }
 $smarty->assign('DADOS',$row);
 $smarty->assign('TIPOS_CCUSTOS',array("E"=>$LANG['entrance'],"S"=>$LANG['exit'])) ;
 if(!isset($titulo))
  $titulo = "";
 display_template("lista_ccustos.tpl",$smarty,$titulo) ;
?>
