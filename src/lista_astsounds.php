<?php
/*-----------------------------------------------------------------------------
 * Programa: lista_ccustos.php - Lista de Centros de Custos Cadastrados
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 // Lista de Arquivos que estao cadastrados no Sistema
 try {
    $sql = "SELECT arquivo from sounds" ;
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }           
 // Lista de Arquivos que estao no Disco 
 $files = scandir(SNEP_PATH_SOUNDS);
 foreach($files as $i => $value) {
   if (substr($value, 0, 1) == '.') {
      unset($files[$i]);
   }
   if (is_dir(SNEP_PATH_SOUNDS.$value)){
      unset($files[$i]);
   }
 }
 // Retira da Lista os arquivos que ja estao no Cadastro do SNEP
 foreach ($row as $key => $value) {
   if ($i = array_search($value['arquivo'],$files)) {
      unset($files[$i]);
   }
 }
 $smarty->assign('dt_files',$files);
 if(!isset($titulo))
  $titulo = "";
 display_template("lista_astsounds.tpl",$smarty,$titulo) ;
?>
