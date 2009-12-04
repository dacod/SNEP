<?php
/* ----------------------------------------------------------------------------
 * Programa: permissoes.php - Cadastro de Permissoes 
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");
 ver_permissao(99);
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_ramais']." -> ".$LANG['permitions']." ".$LANG['of']." ".$LANG['user'] ;
 if (array_key_exists ('permissao', $_POST)) {
    gravar() ;
 }
 $id = $_POST['id'];
 $nome = $_POST['nome'] ;
 
 // Lista das Rotinas disponiveis na tabela ROTINAS
 try {
    $sql = "SELECT r.cod_rotina as cod_rotina,r.desc_rotina as desc_rotina," ;
    $sql.= " permissoes.permissao as permissao"; 
    $sql.= " FROM rotinas as r " ;
    $sql.= " LEFT JOIN permissoes ON permissoes.cod_rotina = r.cod_rotina ";
    $sql.= " AND permissoes.cod_usuario = ".$id ;
    $sql.= " order by desc_rotina" ;
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }           
 // Define variaveis do template          
 $smarty->assign ('dt_permissoes',$row);
 $smarty->assign ('dt_usuario',$nome) ;
 $smarty->assign ('dt_id',$id);
 $smarty->assign  ('TIPOS_PERMS', array('S' => $LANG['yes'], 'N' => $LANG['no'], '' =>$LANG['undef']));
 display_template("permissoes.tpl",$smarty,$titulo) ;
    
/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function gravar()  {
  global $db;
  $id = $_POST['id'] ; 
  try {
     $db->beginTransaction() ;
     $sql = "SELECT cod_rotina FROM rotinas order by cod_rotina ";
     foreach ($db->query($sql) as $row){     
        // Verifica se usuario ja tem permissao registrada para a rotina
        $sql_upd = "REPLACE INTO permissoes (cod_rotina,cod_usuario,permissao)" ;
        $sql_upd.= " VALUES ('" . $row['cod_rotina'] . "',$id,'" . $_POST[$row['cod_rotina']]."')"  ;
        $db->exec($sql_upd) ;
     } // Fim do Foreach  da tabela de rotinas
     $db->commit();
  } catch (Exception $e) {
     display_error($LANG['error'].$e->getMessage(),true) ;
  }
  echo "<meta http-equiv='refresh' content='0;url=../src/rel_ramais.php'>\n" ;
}
?>