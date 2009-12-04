<?php
/*-----------------------------------------------------------------------------
 * Programa: grupos.php - Cadastro de grupos do sistema
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");
 ver_permissao(12) ;
  // Variaveis de ambiente do form
 $smarty->assign('ACAO',$acao) ;
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." -> ".$LANG['menu_grupos']." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_register']." -> ".$LANG['menu_grupos']." -> ".$LANG['include'];
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo ;
   $smarty->assign('ACAO',"cadastrar");
   display_template("grupos.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $nome, $login, $senha;
   $sql  = "INSERT INTO grupos " ;
   $sql .= " (nome)" ; 
   $sql .= " VALUES ('$nome')" ;
   try {
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../src/grupos.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$db,$smarty,$titulo, $acao ;
   $codigo = isset($_POST['cod_grupo']) ? $_POST['cod_grupo'] : $_GET['cod_grupo'];
   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
    $sql = "SELECT * FROM grupos WHERE cod_grupo=".$codigo;
    $row = $db->query($sql)->fetch();
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
 $smarty->assign('ACAO',"grava_alterar") ;
 $smarty->assign ('dt_grupos',$row);
 display_template("grupos.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $cod_grupo, $nome ;
  
   $sql = "UPDATE grupos SET nome='$nome'";
   $sql .= "  where cod_grupo=$cod_grupo" ;
   try {
     $db->beginTransaction() ;
     $db->exec($sql) ;
     $db->commit();
     echo "<meta http-equiv='refresh' content='0;url=../src/rel_grupos.php'>\n" ;
   } catch (Exception $e) {
     $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db;
   $codigo = isset($_POST['cod_grupo']) ? $_POST['cod_grupo'] : $_GET['cod_grupo'];
   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
      $sql = "DELETE FROM grupos WHERE cod_grupo='".$codigo."'";
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      display_error($LANG['msg_excluded'],true) ;
     echo "<meta http-equiv='refresh' content='0;url=../src/rel_grupos.php'>\n" ;
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
}?>