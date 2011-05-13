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
  ver_permissao(13) ;
 // Variaveis de ambiente do form
 $smarty->assign('TIPOS_CONTAS',$tipos_contas) ;
 $smarty->assign('ACAO',$acao) ;
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." -> ".$LANG['menu_accounts']." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_register']." -> ".$LANG['menu_accounts']." -> ".$LANG['include'];
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo,$tipos_contas ;
   
   $smarty->assign('ACAO',"cadastrar");
   display_template("contas.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $nome,  $tipo; 
   $nome     = addslashes($nome) ;
   $sql  = "INSERT INTO contas " ;
   $sql .= " (nome, tipo)" ;
   $sql .= " VALUES ('$nome','$tipo')" ;
   try {
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../src/contas.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$db,$smarty,$titulo,$tipos_contas, $acao ;
   if (!$_POST['id']) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
    $sql = "SELECT * FROM contas WHERE codigo='".$_POST['id']."'";
    $row = $db->query($sql)->fetch();
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
 $smarty->assign('ACAO',"grava_alterar") ;
 $smarty->assign ('dt_contas',$row);
 $smarty->assign ('tipos_contas',$tipos_contas) ;
 display_template("contas.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $codigo, $nome, $tipo; 
   
   $nome = addslashes($nome) ;
   
   $sql = "update contas set nome='$nome', tipo='$tipo'" ;
   $sql .= "  where codigo='$codigo'" ;
   try {
     $db->beginTransaction() ;
     $db->exec($sql) ;
     $db->commit();
     echo "<meta http-equiv='refresh' content='0;url=../src/rel_contas.php'>\n" ;
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
   if (!$_POST['id']) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
      $sql = "DELETE FROM contas WHERE codigo='".$_POST['id']."'";
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      display_error($LANG['msg_excluded'],true) ;
     echo "<meta http-equiv='refresh' content='0;url=../src/rel_contas.php'>\n" ;
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
}?>