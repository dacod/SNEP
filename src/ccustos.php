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
 ver_permissao(13);
 // Variaveis de ambiente do form
 $smarty->assign('ACAO',$acao) ;
 $smarty->assign('TIPOS_CCUSTOS', $tipos_ccustos) ;
 if ($acao == "cadastrar") {
    cadastrar();

 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." » ".$LANG['menu_ccustos']." » ".$LANG['change'];
    alterar() ;

 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;

 } elseif ($acao ==  "excluir") {
    excluir() ;

 } else {
   $titulo = $LANG['menu_register']." » ".$LANG['menu_ccustos']." » ".$LANG['include'];
   principal() ;
   
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo, $LANG, $db ;
   $smarty->assign ('dt_ccustos',array("tipo"=>"E"));
   $smarty->assign('ACAO',"cadastrar");
   display_template("ccustos.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro 
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $nome,  $tipo, $descricao, $codigo;
   $nome     = addslashes($nome) ;
   $sql  = "INSERT INTO ccustos " ;
   $sql .= " (codigo, nome, tipo, descricao)" ;
   $sql .= " VALUES ('$codigo','$nome','$tipo','$descricao')" ;
   try {
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../index.php/costcenter/' >\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG, $db, $smarty, $titulo, $acao ;
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];
   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
    $cod_father = $cod_sun = "" ;
    // Pega 1a. PArte do Centro de Custo
    if (strlen($codigo) > 1) {
       $sql = "SELECT * FROM ccustos WHERE codigo='".substr($codigo,0,1)."'";
       $row = $db->query($sql)->fetch();
       $cod_father = $row['codigo']." - ".$row['nome'] ;
    }
    if (strlen($codigo) > 4) {
       $sql = "SELECT * FROM ccustos WHERE codigo='".substr($codigo,0,4)."'";
       $row = $db->query($sql)->fetch();
       $cod_sun = $row['codigo']." - ".$row['nome'] ;
    }
    $sql = "SELECT * FROM ccustos WHERE codigo='$codigo'";
    $row = $db->query($sql)->fetch();
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
 $smarty->assign('ACAO',"grava_alterar") ;
 $smarty->assign('dt_ccustos',$row);
 $smarty->assign('family',array("father"=>$cod_father,"sun"=>$cod_sun)) ;
 display_template("ccustos.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $codigo, $nome, $tipo, $descricao; 
   
   $sql = "update ccustos set nome='$nome', tipo='$tipo', descricao='$descricao'" ;
   $sql .= "  where codigo='$codigo'" ;
   try {
     $db->beginTransaction() ;
     $db->exec($sql) ;
     $db->commit();
     echo "<meta http-equiv='refresh' content='0;url=../index.php/costcenter/'>\n" ;
   } catch (Exception $e) {
     $db->rollBack();
     display-error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db;
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];
   if (!$codigo) {
      display_error($LANG['msg_notselect'],1) ;
      exit ;
   }
   try {
      $sql = "DELETE FROM ccustos WHERE codigo='".$codigo."'";
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../index.php/costcenter/'>\n" ;

   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage(),true) ;
      
   }  
}
?>